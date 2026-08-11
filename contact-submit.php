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
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    exit('Please enter a valid email address.');
}
if (strlen($message) > 10000) {
    http_response_code(422);
    exit('Your message is too long. Please shorten it and try again.');
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

    // ── Respond INSTANTLY to the user, then fire emails in the background ──
    // (SMTP can take 5-15s — the user must see success within ~1s.)
    $contact = [
        'name'    => $name,
        'email'   => $email,
        'company' => $company,
        'website' => $website,
        'message' => $message,
    ];
    respondBodyThen(
        'Thank you! Your message has been sent. Our team will get back to you within 24 hours.',
        fn() => sendContactEmails($contact)
    );
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    exit('Sorry, something went wrong on our side. Please try again or email us directly.');
}
