<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/mailer.php';

echo "=== Testing Client Thank-You Email ===\n\n";

// Test 1: Direct sendMail to a real email
echo "Test 1: Direct sendMail to your email...\n";
$result = sendMail('your-real-email@gmail.com', 'Test Client Email', '<h1>Test</h1><p>If you get this, client emails work!</p>');
echo "Result: " . json_encode($result) . "\n\n";

// Test 2: sendEnquiryEmails
echo "Test 2: sendEnquiryEmails...\n";
$result = sendEnquiryEmails([
    'first_name' => 'Test',
    'last_name' => 'User',
    'email' => 'your-real-email@gmail.com',
    'phone' => '1234567890',
    'country' => 'India',
    'adults' => 2,
    'children' => 0,
    'travel_date' => '2026-09-01',
    'package_title' => 'Test Package',
    'message' => 'Test message',
]);
echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

// Test 3: sendContactEmails
echo "Test 3: sendContactEmails...\n";
$result = sendContactEmails([
    'name' => 'Test User',
    'email' => 'your-real-email@gmail.com',
    'company' => 'Test Co',
    'website' => 'https://test.com',
    'message' => 'Test contact message',
]);
echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

// Test 4: sendBookingEmails
echo "Test 4: sendBookingEmails...\n";
$result = sendBookingEmails([
    'name' => 'Test User',
    'email' => 'your-real-email@gmail.com',
    'phone' => '1234567890',
    'package_title' => 'Test Package',
    'travel_date' => '2026-09-01',
    'adults' => 2,
    'children' => 0,
    'total_price' => '$500',
    'booking_id' => 123,
    'message' => 'Special request',
]);
echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

echo "=== Check your inbox AND php-error.log ===\n";