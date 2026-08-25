<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/booking.php');
}

// ── Auto-ensure bookings table (safe on existing installs) ────
function ensureBookingsTable(): void {
    static $done = false;
    if ($done) return;
    getDB()->exec("CREATE TABLE IF NOT EXISTS `bookings` (
        `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `package_id`       INT UNSIGNED DEFAULT NULL,
        `package_title`    VARCHAR(255) DEFAULT NULL,
        `user_name`        VARCHAR(160) NOT NULL,
        `email`            VARCHAR(180) NOT NULL,
        `phone`            VARCHAR(30) NOT NULL,
        `travel_date`      DATE NOT NULL,
        `travelers_adult`  TINYINT UNSIGNED NOT NULL DEFAULT 1,
        `travelers_child`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
        `total_price`      DECIMAL(12,2) NOT NULL DEFAULT 0,
        `special_requests` TEXT DEFAULT NULL,
        `status`           ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
        `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_bookings_pkg` (`package_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done = true;
}

// Validate required fields
$required = ['name', 'email', 'phone', 'package_id', 'travel_date', 'adults'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        setFlash('error', 'Please fill in all required fields.');
        redirect(SITE_URL . '/booking.php');
    }
}

// Sanitize inputs
$name     = trim($_POST['name']);
$email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$phone    = trim($_POST['phone']);
$packageId = (int)$_POST['package_id'];
$travelDate = $_POST['travel_date'];
$adults   = max(1, (int)$_POST['adults']);
$children = max(0, (int)($_POST['children'] ?? 0));
$specialRequests = trim($_POST['special_requests'] ?? '');

// Validate email (strict)
$emailValidation = validateEmailStrict($email);
if (!$emailValidation['valid']) {
    setFlash('error', $emailValidation['message']);
    redirect(SITE_URL . '/booking.php');
}

// Validate phone (basic)
if (!preg_match('/^[0-9+\s-]{10,20}$/', $phone)) {
    setFlash('error', 'Please enter a valid phone number.');
    redirect(SITE_URL . '/booking.php');
}

// Validate date
$travelDateObj = DateTime::createFromFormat('Y-m-d', $travelDate);
if (!$travelDateObj || $travelDateObj < new DateTime('+1 day')) {
    setFlash('error', 'Travel date must be at least tomorrow.');
    redirect(SITE_URL . '/booking.php');
}

// Get package details for pricing
$package = getPackageById($packageId);
if (!$package) {
    setFlash('error', 'Invalid package selected.');
    redirect(SITE_URL . '/booking.php');
}

// Calculate total price
$pricePerPerson = (float)($package['price_discounted'] ?? $package['price_original']);
$totalPrice = ($adults + $children) * $pricePerPerson;
$currency   = $package['currency'] ?? 'INR';

try {
    ensureBookingsTable();
    $db = getDB();

    // 1) Insert booking
    $stmt = $db->prepare("
        INSERT INTO bookings (
            package_id, package_title, user_name, email, phone, travel_date,
            travelers_adult, travelers_child, total_price, special_requests, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        $packageId,
        $package['title'],
        $name,
        $email,
        $phone,
        $travelDate,
        $adults,
        $children,
        $totalPrice,
        $specialRequests,
    ]);
    $bookingId = $db->lastInsertId();

    // 2) Insert into enquiries so the booking shows up in Admin → CRM
    $nameParts = preg_split('/\s+/', $name, 2);
    $firstName = $nameParts[0] ?? $name;
    $lastName  = $nameParts[1] ?? '';
    $enqMessage = 'Booking request — Travel date: ' . $travelDate
                . ' | Adults: ' . $adults . ' | Children: ' . $children
                . ' | Total: ' . formatPrice($totalPrice, $currency)
                . ($specialRequests !== '' ? " | Special requests: " . $specialRequests : '');

    // Ensure the travel_date column exists so admin enquiries table shows it
    $db->exec("ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `travel_date` DATE DEFAULT NULL AFTER `children`");

    $db->prepare(
        'INSERT INTO enquiries
        (package_id, package_title, first_name, last_name, email, country, phone, adults, children, travel_date, message, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $packageId,
        $package['title'],
        $firstName,
        $lastName,
        $email,
        'India',
        $phone,
        $adults,
        $children,
        $travelDate !== '' ? $travelDate : null,
        $enqMessage,
        'new',
    ]);

    // ── Redirect INSTANTLY, then fire emails in the background ──
    // (SMTP can take 5-15s — the user must land on the success page immediately.)
    respondRedirectThen(SITE_URL . '/booking.php?success=1', function () use (
        $name, $email, $phone, $package, $travelDate, $adults, $children, $totalPrice, $specialRequests, $bookingId, $currency
    ) {
        try {
            $result = sendBookingEmails([
                'name'          => $name,
                'email'         => $email,
                'phone'         => $phone,
                'package_title' => $package['title'],
                'travel_date'   => $travelDate,
                'adults'        => $adults,
                'children'      => $children,
                'total_price'   => formatPrice($totalPrice, $currency),
                'booking_id'    => $bookingId,
                'message'       => $specialRequests,
            ]);
            error_log('VMS booking email result: ' . json_encode($result));
        } catch (Throwable $emailErr) {
            // Emails must never block a booking — log and continue
            error_log('VMS booking email error: ' . $emailErr->getMessage());
        }
    });

} catch (Throwable $e) {
    error_log('VMS booking insert error: ' . $e->getMessage());
    setFlash('error', 'Failed to submit booking. Please try again.');
    redirect(SITE_URL . '/booking.php');
}
