<?php
/**
 * Contact form handler — saves the submission and sends emails.
 * Called via AJAX from /contact (contact-form.js posts to this file).
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request method.');
}

// ── Honeypot check (bot trap) ──
if (!empty($_POST['hp_confirm'] ?? '')) {
    error_log('VMS contact: Honeypot triggered from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    http_response_code(200);
    exit('Thank you! Your message has been sent. Our team will get back to you within 24 hours.');
}

// ── Rate limiting: 3 submissions per 15 minutes per IP ──
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateKey = 'contact_ratelimit_' . md5($ip);
session_start();
$now = time();
$window = 15 * 60;
$maxSubmissions = 3;

if (!isset($_SESSION[$rateKey])) {
    $_SESSION[$rateKey] = ['count' => 0, 'window_start' => $now];
} elseif ($now - $_SESSION[$rateKey]['window_start'] > $window) {
    $_SESSION[$rateKey] = ['count' => 0, 'window_start' => $now];
}

if ($_SESSION[$rateKey]['count'] >= $maxSubmissions) {
    error_log('VMS contact: Rate limit exceeded for ' . $ip);
    http_response_code(429);
    exit('Too many submissions. Please wait a few minutes before trying again.');
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$company = trim($_POST['company'] ?? '');
$website = trim($_POST['website'] ?? '');
$message = trim($_POST['message'] ?? '');

// ── Validation ──────────────────────────────────────────
if ($name === '' || $email === '' || $message === '') {
    http_response_code(422);
    exit('Please fill in your name, email and message.');
}

$emailValidation = validateEmailStrict($email);
if (!$emailValidation['valid']) {
    http_response_code(422);
    exit($emailValidation['message']);
}

if (strlen($message) > 10000) {
    http_response_code(422);
    exit('Your message is too long. Please shorten it and try again.');
}

// ── Basic spam pattern detection ──
$spamPatterns = [
    '/\b(casino|viagra|cialis|porn|sex|loan|debt|credit|insurance|mortgage)\b/i',
    '/\b(buy now|click here|limited time|act now|free money|make money)\b/i',
    '/(https?:\/\/){3,}/',
    '/\b(seo|backlink|guest post|link building)\b/i',
];
foreach ($spamPatterns as $pattern) {
    if (preg_match($pattern, $message) || preg_match($pattern, $name) || preg_match($pattern, $company)) {
        error_log('VMS contact: Spam pattern detected from ' . $ip . ' - ' . $pattern);
        http_response_code(200);
        exit('Thank you! Your message has been sent. Our team will get back to you within 24 hours.');
    }
}

try {
    ensureContactsTable();

    $db = getDB();
    $stmt = $db->prepare(
        'INSERT INTO contacts (name, email, company, website, message, status) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $name,
        $email,
        $company !== '' ? $company : null,
        $website !== '' ? $website : null,
        $message,
        'new',
    ]);

    $_SESSION[$rateKey]['count']++;

    // ── Respond INSTANTLY to the user, then fire emails in the background ──
    $contact = [
        'name'    => $name,
        'email'   => $email,
        'company' => $company,
        'website' => $website,
        'message' => $message,
    ];
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
    http_response_code(500);
    exit('Sorry, something went wrong on our side. Please try again or email us directly.');
}
