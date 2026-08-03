<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

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

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $fail('email');
}

if (strlen($message) > 10000) {
    $fail('message');
}

try {
    $db = getDB();
    $stmt = $db->prepare(
        'INSERT INTO enquiries
        (package_id, package_title, first_name, last_name, email, country, phone, adults, children, message, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
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
        $message,
        'new',
    ]);
    redirect(enquiryReturnUrl($packageSlug, 'sent'));
} catch (Throwable $e) {
    $fail('server');
}
