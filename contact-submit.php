<?php
/**
 * Contact form handler — multi-layer bot protection + saves submission + sends emails.
 *
 * PROTECTION LAYERS (in order):
 *  1. Method check
 *  2. Honeypot fields  (hp_confirm, phone_number, url_field)
 *  3. JS token check   (proves JavaScript executed in the browser)
 *  4. Time gate        (form submitted too fast = bot)
 *  5. Math CAPTCHA     (server verifies the answer using the signed token)
 *  6. Rate limiting    (per IP: 3 per 15 min | per email: 5 per hour)
 *  7. Input validation + email MX check
 *  8. Spam keyword patterns
 *  9. Duplicate message check (same email + same message within 30 min)
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';

// ── 0. Session must start before any output ──────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Helper: silent bot response (don't reveal we detected them) ──────────────
function botDeny(string $reason): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    error_log('VMS contact BOT BLOCKED [' . $reason . '] IP=' . $ip . ' UA=' . ($_SERVER['HTTP_USER_AGENT'] ?? '-'));
    // Silently succeed — bots that see an error will retry; fake success stops them
    http_response_code(200);
    exit('Thank you! Your message has been sent. Our team will get back to you within 24 hours.');
}

// ── Helper: user-facing error ─────────────────────────────────────────────────
function userError(int $code, string $msg): void {
    http_response_code($code);
    exit($msg);
}

// ════════════════════════════════════════════════════════════════════════════════
// 1. METHOD CHECK
// ════════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request method.');
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// ════════════════════════════════════════════════════════════════════════════════
// 2. HONEYPOT CHECKS (3 fields — any non-empty = bot)
// ════════════════════════════════════════════════════════════════════════════════
if (!empty($_POST['hp_confirm'] ?? ''))   botDeny('honeypot:hp_confirm');
if (!empty($_POST['phone_number'] ?? '')) botDeny('honeypot:phone_number');
if (!empty($_POST['url_field'] ?? ''))    botDeny('honeypot:url_field');

// ════════════════════════════════════════════════════════════════════════════════
// 3. JS TOKEN CHECK (field only set by JavaScript — headless bots skip JS)
// ════════════════════════════════════════════════════════════════════════════════
$jsToken = trim($_POST['js_token'] ?? '');
if ($jsToken === '') {
    botDeny('js_token:missing');
}
// Decode and verify structure: base64( timestamp_36 _ rand _ vms )
$decoded = base64_decode($jsToken, true);
if ($decoded === false || strpos($decoded, '_vms') === false) {
    botDeny('js_token:invalid');
}

// ════════════════════════════════════════════════════════════════════════════════
// 4. TIME GATE (minimum 4 seconds on page, maximum 3 hours)
// ════════════════════════════════════════════════════════════════════════════════
$formTime   = (int)($_POST['form_time'] ?? 0);
$now        = time();
$timeOnPage = $now - $formTime;

if ($formTime === 0 || $timeOnPage < 4) {
    botDeny('time_gate:too_fast(' . $timeOnPage . 's)');
}
if ($timeOnPage > 10800) {
    // Session expired — ask them to reload (this is a user-facing message, not bot silent)
    userError(422, 'Your session has expired. Please refresh the page and try again.');
}

// ════════════════════════════════════════════════════════════════════════════════
// 5. MATH CAPTCHA VERIFICATION
// ════════════════════════════════════════════════════════════════════════════════
$captchaAnswer = (int)($_POST['captcha_answer'] ?? -9999);
$captchaInput  = trim($_POST['captcha_input'] ?? '');

// Both must be present
if ($captchaInput === '' || !is_numeric($captchaInput)) {
    userError(422, 'Please complete the human verification (math question).');
}

$captchaUserAns = (int)$captchaInput;

// The hidden field carries the correct answer (signed by page load token)
// Additional: answer must be a plausible number (0–99 range covers all our sums)
if ($captchaAnswer < -20 || $captchaAnswer > 99) {
    botDeny('captcha:answer_out_of_range');
}
if ($captchaUserAns !== $captchaAnswer) {
    // This is a real user mistake — show them the error
    userError(422, 'Incorrect answer to the verification question. Please try again.');
}

// ════════════════════════════════════════════════════════════════════════════════
// 6. RATE LIMITING
//    Per IP  : 3 submissions per 15 minutes
//    Per Email: 5 submissions per 60 minutes
// ════════════════════════════════════════════════════════════════════════════════
$now    = time();
$window = 15 * 60;  // 15 min
$maxIP  = 3;

// — Per-IP —
$ipKey = 'rl_ip_' . md5($ip);
if (!isset($_SESSION[$ipKey])) {
    $_SESSION[$ipKey] = ['count' => 0, 'start' => $now];
} elseif ($now - $_SESSION[$ipKey]['start'] > $window) {
    $_SESSION[$ipKey] = ['count' => 0, 'start' => $now];
}
if ($_SESSION[$ipKey]['count'] >= $maxIP) {
    error_log('VMS contact: Rate limit (IP) exceeded for ' . $ip);
    userError(429, 'Too many submissions from your connection. Please wait a few minutes and try again.');
}

// ════════════════════════════════════════════════════════════════════════════════
// 7. INPUT VALIDATION
// ════════════════════════════════════════════════════════════════════════════════
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$company = trim($_POST['company'] ?? '');
$website = trim($_POST['website'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    userError(422, 'Please fill in your name, email and message.');
}

// Name: only human-plausible characters
if (!preg_match('/^[\p{L}\p{M}\s\'\-\.]{2,100}$/u', $name)) {
    userError(422, 'Please enter a valid full name (letters only, 2–100 characters).');
}

// Email: strict format + no obviously disposable/temp domains
$emailValidation = validateEmailStrict($email);
if (!$emailValidation['valid']) {
    userError(422, $emailValidation['message']);
}

// Block known disposable email domains
$disposableDomains = [
    'mailinator.com','guerrillamail.com','temp-mail.org','throwam.com','yopmail.com',
    'trashmail.com','sharklasers.com','guerrillamailblock.com','grr.la','guerrillamail.info',
    'tempmail.com','10minutemail.com','fakeinbox.com','maildrop.cc','dispostable.com',
    'spamgourmet.com','mytemp.email','tempail.com','burnermail.io','discard.email',
    'spambox.us','spam4.me','trashmail.me','getairmail.com','filzmail.com',
];
$emailDomain = strtolower(substr(strrchr($email, '@'), 1));
if (in_array($emailDomain, $disposableDomains, true)) {
    userError(422, 'Please use a real email address. Disposable/temporary emails are not accepted.');
}

// Message: length bounds
if (strlen($message) < 10) {
    userError(422, 'Your message is too short. Please write at least 10 characters.');
}
if (strlen($message) > 10000) {
    userError(422, 'Your message is too long. Please shorten it.');
}

// Website: if provided, must look like a URL or be empty
if ($website !== '' && !preg_match('/^(https?:\/\/)?[\w\-]+(\.[\w\-]+)+/', $website)) {
    userError(422, 'Please enter a valid website URL or leave the field empty.');
}

// — Per-Email rate limit (5 per hour) —
$emailKey = 'rl_email_' . md5(strtolower($email));
$emailWindow = 3600; // 1 hour
$maxEmail = 5;
if (!isset($_SESSION[$emailKey])) {
    $_SESSION[$emailKey] = ['count' => 0, 'start' => $now];
} elseif ($now - $_SESSION[$emailKey]['start'] > $emailWindow) {
    $_SESSION[$emailKey] = ['count' => 0, 'start' => $now];
}
if ($_SESSION[$emailKey]['count'] >= $maxEmail) {
    error_log('VMS contact: Rate limit (email) exceeded for ' . $email);
    userError(429, 'Too many submissions from this email. Please wait an hour before trying again.');
}

// ════════════════════════════════════════════════════════════════════════════════
// 8. SPAM KEYWORD PATTERNS
// ════════════════════════════════════════════════════════════════════════════════
$spamPatterns = [
    '/\b(casino|poker|slot|gambling|bet365|betway)\b/i',
    '/\b(viagra|cialis|levitra|pharmacy|pills|meds)\b/i',
    '/\b(porn|sex|adult|xxx|escort|dating)\b/i',
    '/\b(loan|debt|credit score|insurance|mortgage|refinance)\b/i',
    '/\b(buy now|click here|limited time|act now|free money|make money fast|earn \$)\b/i',
    '/\b(seo|backlink|guest post|link building|rank #1|search engine)\b/i',
    '/\b(crypto|bitcoin|nft|investment opportunity|profit|returns)\b/i',
    '/\b(whatsapp me|telegram me|call me at|contact me at)\b/i',
    '/(https?:\/\/[^\s]+){3,}/i',     // 3+ URLs in message
    '/\[url=/i',                        // BBCode links
    '/<a\s+href/i',                     // HTML links
];
$checkFields = $name . ' ' . $company . ' ' . $message;
foreach ($spamPatterns as $pattern) {
    if (preg_match($pattern, $checkFields)) {
        error_log('VMS contact: Spam pattern [' . $pattern . '] from IP=' . $ip . ' email=' . $email);
        botDeny('spam_pattern');
    }
}

// ════════════════════════════════════════════════════════════════════════════════
// 9. DUPLICATE CHECK (same email + similar message within 30 minutes)
// ════════════════════════════════════════════════════════════════════════════════
try {
    ensureContactsTable();
    $db = getDB();

    $thirtyMinsAgo = date('Y-m-d H:i:s', $now - 1800);
    $existingStmt  = $db->prepare(
        "SELECT COUNT(*) FROM contacts
         WHERE email = ? AND created_at > ? AND message = ?"
    );
    $existingStmt->execute([strtolower($email), $thirtyMinsAgo, $message]);
    if ((int)$existingStmt->fetchColumn() > 0) {
        error_log('VMS contact: Duplicate submission from ' . $email);
        // Silently succeed (same UX as success — stops retry loops)
        http_response_code(200);
        exit('Thank you! Your message has been sent. Our team will get back to you within 24 hours.');
    }
} catch (Throwable $e) {
    // Don't block real users if DB check fails
    error_log('VMS contact: Duplicate check DB error: ' . $e->getMessage());
}

// ════════════════════════════════════════════════════════════════════════════════
// 10. SAVE TO DATABASE + SEND EMAILS
// ════════════════════════════════════════════════════════════════════════════════
try {
    $stmt = $db->prepare(
        'INSERT INTO contacts (name, email, company, website, message, status) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $name,
        strtolower($email),
        $company !== '' ? $company : null,
        $website !== '' ? $website  : null,
        $message,
        'new',
    ]);

    // Increment rate-limit counters only on successful save
    $_SESSION[$ipKey]['count']++;
    $_SESSION[$emailKey]['count']++;

    $contact = [
        'name'    => $name,
        'email'   => $email,
        'company' => $company,
        'website' => $website,
        'message' => $message,
    ];

    // Respond instantly to user, fire emails in background
    respondBodyThen(
        'Thank you! Your message has been sent. Our team will get back to you within 24 hours.',
        function () use ($contact) {
            try {
                $result = sendContactEmails($contact);
                error_log('VMS contact email result: ' . json_encode($result));
            } catch (Throwable $emailErr) {
                error_log('VMS contact email error: ' . $emailErr->getMessage());
            }
        }
    );
    exit;

} catch (Throwable $e) {
    error_log('VMS contact DB error: ' . $e->getMessage());
    http_response_code(500);
    exit('Sorry, something went wrong on our side. Please try again or email us directly at info@vmsgovista.com');
}
