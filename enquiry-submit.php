<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/package.php');
}

$firstName    = trim($_POST['first_name'] ?? '');
$lastName     = trim($_POST['last_name'] ?? '');
$email        = trim($_POST['email'] ?? '');
$country      = trim($_POST['country'] ?? '');
$phone        = trim($_POST['phone'] ?? '');
$message      = trim($_POST['message'] ?? '');
$packageId    = (int)($_POST['package_id'] ?? 0);
$packageTitle = trim($_POST['package_title'] ?? '');
$packageSlug  = trim($_POST['package_slug'] ?? '');

$adults = null;
if (isset($_POST['adults']) && $_POST['adults'] !== '') {
    $adults = max(0, min(99, (int)$_POST['adults']));
}
$children = null;
if (isset($_POST['children']) && $_POST['children'] !== '') {
    $children = max(0, min(99, (int)$_POST['children']));
}

// Travel date (optional field)
$travelDate = trim($_POST['travel_date'] ?? '');
if ($travelDate !== '' && !DateTime::createFromFormat('Y-m-d', $travelDate)) {
    $travelDate = '';
}

if (!$packageSlug && $packageId > 0) {
    $rows = fetchAll('SELECT slug, title FROM packages WHERE id = ? LIMIT 1', [$packageId]);
    if (!empty($rows)) {
        $packageSlug  = $rows[0]['slug'] ?? '';
        if (!$packageTitle) {
            $packageTitle = $rows[0]['title'] ?? '';
        }
    }
}

function enquiryReturnUrl(string $slug, string $result, string $reason = ''): string {
    if ($slug !== '') {
        $params = ['slug' => $slug, 'enquiry' => $result];
        if ($reason !== '') {
            $params['reason'] = $reason;
        }
        return SITE_URL . '/package-details.php?' . http_build_query($params) . '#contact-form';
    }
    $params = ['enquiry' => $result];
    if ($reason !== '') {
        $params['reason'] = $reason;
    }
    return SITE_URL . '/package.php?' . http_build_query($params);
}

$fail = function (string $reason) use ($packageSlug): void {
    redirect(enquiryReturnUrl($packageSlug, 'error', $reason));
};

if ($firstName === '' || $lastName === '' || $email === '' || $message === '') {
    $fail('missing');
}

$emailValidation = validateEmailStrict($email);
if (!$emailValidation['valid']) {
    $fail('email');
}

if (strlen($message) > 10000) {
    $fail('message');
}

try {
    $db = getDB();

    // Auto-ensure travel column (safe on existing installs)
    $db->exec("ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `travel_date` DATE DEFAULT NULL AFTER `children`");

    $stmt = $db->prepare(
        'INSERT INTO enquiries
        (package_id, package_title, first_name, last_name, email, country, phone, adults, children, travel_date, message, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $packageId > 0 ? $packageId : null,
        $packageTitle !== '' ? $packageTitle : null,
        $firstName,
        $lastName,
        $email,
        $country !== '' ? $country : null,
        $phone !== '' ? $phone : null,
        $adults,
        $children,
        $travelDate !== '' ? $travelDate : null,
        $message,
        'new',
    ]);

    // ── Redirect INSTANTLY, then fire emails in the background ──
    // (SMTP can take 5-15s — the user must land on the success page immediately.)
    respondRedirectThen(enquiryReturnUrl($packageSlug, 'sent'), function () use (
        $firstName, $lastName, $email, $phone, $country, $adults, $children, $packageTitle, $packageSlug, $message, $travelDate
    ) {
        try {
            $result = sendEnquiryEmails([
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'email'         => $email,
                'phone'         => $phone,
                'country'       => $country,
                'adults'        => $adults,
                'children'      => $children,
                'travel_date'   => $travelDate !== '' ? $travelDate : '',
                'package_title' => $packageTitle !== '' ? $packageTitle : ($packageSlug !== '' ? $packageSlug : 'General enquiry'),
                'message'       => $message,
            ]);
            error_log('VMS enquiry email result: ' . json_encode($result));
        } catch (Throwable $emailErr) {
            // Emails must never block a booking — log and continue
            error_log('VMS enquiry email error: ' . $emailErr->getMessage());
        }
    });
} catch (Throwable $e) {
    $fail('server');
}
