<?php
/**
 * VMS Go Vista — Email / SMTP Mailer
 * ---------------------------------------------------------------
 * Pure-PHP SMTP client (zero Composer dependencies).
 * Works with Gmail / Google Workspace (App Passwords), any SMTP host.
 *
 * Settings (stored in the `settings` key-value table, managed from
 * Admin → Settings → "Email & SMTP Settings"):
 *   mail_enabled       1|0           master switch for all emails
 *   smtp_host          e.g. smtp.gmail.com, smtp.office365.com, mail.yourdomain.com
 *   smtp_port          587 (TLS) / 465 (SSL) / 25 (none)
 *   smtp_user          login username (or full Gmail/Workspace address)
 *   smtp_pass          password or App Password (Gmail: enable 2FA → App Password)
 *   smtp_encryption    tls | ssl | none
 *   smtp_from_email    sender address
 *   smtp_from_name     sender display name
 *   admin_notify_email admin address(es) for notifications (comma-separated allowed)
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

// ── Auto-ensure helper tables (safe on existing installs) ────
function ensureSettingsTable(): void {
    static $done = false;
    if ($done) return;
    getDB()->exec("CREATE TABLE IF NOT EXISTS `settings` (
        `setting_key`   VARCHAR(100) NOT NULL,
        `setting_value` TEXT DEFAULT NULL,
        `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done = true;
}

function ensureContactsTable(): void {
    static $done = false;
    if ($done) return;
    getDB()->exec("CREATE TABLE IF NOT EXISTS `contacts` (
        `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `name`       VARCHAR(120) NOT NULL,
        `email`      VARCHAR(180) NOT NULL,
        `company`    VARCHAR(180) DEFAULT NULL,
        `website`    VARCHAR(180) DEFAULT NULL,
        `message`    TEXT NOT NULL,
        `status`     ENUM('new','read') NOT NULL DEFAULT 'new',
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_contacts_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done = true;
}

// ── Settings get/set ─────────────────────────────────────────
function getSetting(string $key, $default = ''): string {
    ensureSettingsTable();
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (fetchAll('SELECT setting_key, setting_value FROM settings') as $r) {
            $cache[$r['setting_key']] = $r['setting_value'];
        }
    }
    $v = $cache[$key] ?? null;
    if ($v === null || $v === '') return (string)$default;
    // Decrypt sensitive settings
    if (in_array($key, ['smtp_pass'], true)) {
        return decryptSetting($v) ?: (string)$default;
    }
    return (string)$v;
}

function setSetting(string $key, string $value): void {
    ensureSettingsTable();
    // Encrypt sensitive settings
    if (in_array($key, ['smtp_pass'], true) && $value !== '') {
        $value = encryptSetting($value);
    }
    getDB()->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute([$key, $value]);
}

// Encryption helpers for sensitive settings (SMTP password)
function encryptSetting(string $value): string {
    $key = getEncryptionKey();
    $iv = random_bytes(16);
    $ciphertext = openssl_encrypt($value, 'AES-256-GCM', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return base64_encode($iv . $tag . $ciphertext);
}

function decryptSetting(string $encrypted): ?string {
    $key = getEncryptionKey();
    $data = base64_decode($encrypted, true);
    if ($data === false || strlen($data) < 33) return null;
    $iv = substr($data, 0, 16);
    $tag = substr($data, 16, 16);
    $ciphertext = substr($data, 32);
    return openssl_decrypt($ciphertext, 'AES-256-GCM', $key, OPENSSL_RAW_DATA, $iv, $tag);
}

function getEncryptionKey(): string {
    $envKey = $_ENV['SETTINGS_ENCRYPTION_KEY'] ?? null;
    if ($envKey) return base64_decode($envKey);
    // Fallback: derive from site-specific constant (less secure but works without env)
    static $derived = null;
    if ($derived === null) {
        $derived = hash_pbkdf2('sha256', SITE_NAME . DB_NAME, 'vms-salt-' . DB_HOST, 100000, 32, true);
    }
    return $derived;
}

function mailIsEnabled(): bool {
    return (int)getSetting('mail_enabled', 1) === 1;
}

// ── Instant-response helpers ──────────────────────────────────
function respondBodyThen(string $body, callable $background, int $status = 200): void {
    http_response_code($status);
    ignore_user_abort(true);
    set_time_limit(120);
    if (!headers_sent()) {
        header('Connection: close');
        header('Content-Length: ' . (string)strlen($body));
    }
    echo $body;
    while (ob_get_level() > 0) { ob_end_flush(); }
    flush();
    try {
        $background();
    } catch (Throwable $e) {
        error_log('VMS background task error: ' . $e->getMessage());
    }
}

function respondRedirectThen(string $url, callable $background): void {
    ignore_user_abort(true);
    set_time_limit(120);
    if (!headers_sent()) {
        header('Connection: close');
        header('Location: ' . $url);
    }
    while (ob_get_level() > 0) { ob_end_flush(); }
    flush();
    try {
        $background();
    } catch (Throwable $e) {
        error_log('VMS background task error: ' . $e->getMessage());
    }
    exit;
}

// ── Public entry point ────────────────────────────────────────
function sendMail(string $to, string $subject, string $htmlBody, string $plainText = ''): array {
    if (!mailIsEnabled()) {
        error_log('VMS sendMail: Email notifications are disabled in settings (to: ' . $to . ')');
        return ['success' => false, 'message' => 'Email notifications are disabled in settings.'];
    }

    $fromEmail = getSetting('smtp_from_email', '');
    if ($fromEmail === '') {
        $fromEmail = getSetting('smtp_user', '');
    }
    $fromName  = getSetting('smtp_from_name', SITE_NAME);
    $host      = trim(getSetting('smtp_host', ''));

    error_log('VMS sendMail: to=' . $to . ', host=' . $host . ', from=' . $fromEmail . ', mail_enabled=' . (mailIsEnabled() ? '1' : '0'));

    if ($host === '') {
        error_log('VMS sendMail: No SMTP host configured, falling back to PHP mail()');
        return sendMailNative($to, $subject, $htmlBody, $plainText, $fromEmail, $fromName);
    }

    $mailer = new VmsSmtpMailer([
        'host'       => $host,
        'port'       => (int)getSetting('smtp_port', 587),
        'user'       => getSetting('smtp_user', ''),
        'pass'       => getSetting('smtp_pass', ''),
        'encryption' => getSetting('smtp_encryption', 'tls'),
        'fromEmail'  => $fromEmail,
        'fromName'   => $fromName,
    ]);

    $result = $mailer->send($to, $subject, $htmlBody, $plainText);
    error_log('VMS sendMail result: ' . json_encode($result));
    return $result;
}

// ── High-level notification helpers ──────────────────────────
function sendAdminNotification(string $subject, string $htmlContent): array {
    $admin = trim(getSetting('admin_notify_email', ''));
    if ($admin === '') {
        return ['success' => false, 'message' => 'Admin notification email is not set in settings.'];
    }

    $results = [];
    foreach (array_map('trim', explode(',', $admin)) as $addr) {
        if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
            $results[] = sendMail($addr, $subject, $htmlContent);
        }
    }

    return [
        'success' => count($results) > 0,
        'message' => count($results) . ' admin address(es) notified.'
    ];
}

/** Package booking / enquiry — admin notification + client thank-you. */
function sendEnquiryEmails(array $enq): array {
    $fullName = trim(($enq['first_name'] ?? '') . ' ' . ($enq['last_name'] ?? ''));
    $pkgTitle = trim($enq['package_title'] ?? 'General enquiry');
    $guestName = $fullName !== '' ? $fullName : 'Valued Traveler';

    // 1) Admin notification
    $adminHtml = emailTemplate(
        'New Package Enquiry',
        '<p style="margin:0 0 16px;font-size:14px;color:#475467;line-height:1.7;">A new <strong>package booking enquiry</strong> has been received from the website.</p>'
        . buildEnquiryTableHtml($enq)
        . buildMessageBlock($enq['message'] ?? '')
    );

    $admin = sendAdminNotification('New Package Enquiry: ' . $pkgTitle, $adminHtml);

    // 2) Client thank-you
    $clientHtml = emailTemplate(
        'Thank you for your enquiry',
        '<p style="margin:0 0 16px;font-size:14px;color:#475467;line-height:1.8;">Dear ' . e($guestName) . ',</p>'
        . '<p style="margin:0 0 16px;font-size:14px;color:#475467;line-height:1.8;">Thank you for your interest in <strong style="color:#0a2540;">' . e($pkgTitle) . '</strong>. Our travel experts are currently reviewing your request and will get back to you within <strong style="color:#f26522;">24 hours</strong> with the best available options and pricing.</p>'
        . '<p style="margin:0 0 16px;font-size:14px;color:#475467;line-height:1.8;">We truly appreciate the opportunity to help you plan a memorable journey with comfort, care, and the best possible experience.</p>'
        . '<p style="margin:0;font-size:14px;color:#475467;line-height:1.8;">If your travel plan is urgent, you can contact our team directly using the support details below.</p>'
    );

    $client = sendMail($enq['email'] ?? '', 'Your enquiry — ' . SITE_NAME, $clientHtml);

    return ['admin' => $admin, 'client' => $client];
}

/** Contact form — admin notification + client thank-you. */
function sendContactEmails(array $contact): array {
    $name = trim($contact['name'] ?? '');
    $guestName = $name !== '' ? $name : 'Valued Guest';

    // 1) Admin notification
    $adminHtml = emailTemplate(
        'New Contact Message',
        '<p style="margin:0 0 16px;font-size:14px;color:#475467;line-height:1.7;">A new message was submitted through the website <strong>Contact</strong> form.</p>'
        . buildContactTableHtml($contact)
        . buildMessageBlock($contact['message'] ?? '')
    );

    $admin = sendAdminNotification('New Contact Message: ' . ($name !== '' ? $name : 'Website visitor'), $adminHtml);

    // 2) Client thank-you
    $clientHtml = emailTemplate(
        'Thank You for Contacting ' . SITE_NAME . '!',
        '<p style="margin:0 0 16px;font-size:14px;color:#475467;line-height:1.8;">Dear ' . e($guestName) . ',</p>'
        . '<p style="margin:0 0 16px;font-size:14px;color:#475467;line-height:1.8;">We have received your message and sincerely appreciate you reaching out to us. Our team is reviewing your request and will get back to you within <strong style="color:#f26522;">24 hours</strong>.</p>'
        . '<p style="margin:0 0 16px;font-size:14px;color:#475467;line-height:1.8;">Whether it is a package enquiry, custom holiday plan, or general travel support, we are here to assist you with the best possible guidance.</p>'
        . '<p style="margin:0;font-size:14px;color:#475467;line-height:1.8;">If your request is urgent, please feel free to connect with us directly.</p>'
    );

    $client = sendMail($contact['email'] ?? '', 'We received your message — ' . SITE_NAME, $clientHtml);

    return ['admin' => $admin, 'client' => $client];
}

/** Booking form — admin notification + client confirmation. */
function sendBookingEmails(array $bk): array {
    $guestName = trim($bk['name'] ?? '');
    $guestName = $guestName !== '' ? $guestName : 'Valued Traveler';
    $pkgTitle  = trim($bk['package_title'] ?? 'Travel Package');

    // 1) Admin notification
    $adminHtml = emailTemplate(
        'New Booking Request',
        '<p style="margin:0 0 16px;font-size:14px;color:#475467;line-height:1.7;">A new <strong>booking request</strong> has been received from the website.</p>'
        . buildBookingTableHtml($bk)
        . buildMessageBlock($bk['message'] ?? '')
    );

    $admin = sendAdminNotification('New Booking Request: ' . $pkgTitle, $adminHtml);

    // 2) Client thank-you
    $clientHtml = emailTemplate(
        'Booking Request Received',
        '<p style="margin:0 0 16px;font-size:14px;color:#475467;line-height:1.8;">Dear ' . e($guestName) . ',</p>'
        . '<p style="margin:0 0 16px;font-size:14px;color:#475467;line-height:1.8;">Thank you for choosing <strong style="color:#0a2540;">' . e($pkgTitle) . '</strong>. Your booking request has been received and our travel experts are reviewing it right now. We will get back to you within <strong style="color:#f26522;">24 hours</strong> with confirmation and the best available pricing.</p>'
        . buildBookingTableHtml($bk)
        . '<p style="margin:16px 0 0;font-size:14px;color:#475467;line-height:1.8;">We truly appreciate the opportunity to plan a memorable journey for you. If your travel plan is urgent, please reach our team directly using the support details below.</p>'
    );

    $client = sendMail($bk['email'] ?? '', 'Booking Request Received — ' . SITE_NAME, $clientHtml);

    return ['admin' => $admin, 'client' => $client];
}

function buildBookingTableHtml(array $bk): string {
    $fields = [
        'Booking ID'  => '#' . ($bk['booking_id'] ?? '—'),
        'Name'        => $bk['name'] ?? '',
        'Email'       => $bk['email'] ?? '',
        'Phone'       => $bk['phone'] ?? '',
        'Package'     => $bk['package_title'] ?? '',
        'Travelling Date' => $bk['travel_date'] ?? '',
        'Adults'      => $bk['adults'] ?? '',
        'Children'    => $bk['children'] ?? '',
        'Total Price' => $bk['total_price'] ?? '',
    ];
    return buildFieldsTable($fields);
}

// ── Email template (premium, safer for email clients) ─────────
function emailTemplate(string $title, string $bodyHtml): string {
    $siteName      = e(getSetting('smtp_from_name', SITE_NAME));
    $safeTitle     = e($title);

    $baseUrl       = rtrim(SITE_URL, '/');
    $bannerUrl     = $baseUrl . '/assets/mail.png';
    $iconUrl       = $baseUrl . '/assets/mail-icons/';

    $homeUrl       = $baseUrl . '/';
    $packagesUrl   = $baseUrl . '/package';
    $aboutUrl      = $baseUrl . '/about';
    $contactUrl    = $baseUrl . '/contact';

    $phoneDisp     = '+91 98701 82425';
    $phoneTel      = '+919870182425';
    $emailAddress  = 'info@vmsgovista.com';
    $emailHref     = 'mailto:info@vmsgovista.com';
    $websiteDisp   = 'www.vmsgovista.com';
    $websiteHref   = $homeUrl;
    $waUrl         = 'https://wa.me/919870182425';

    $facebookUrl   = 'https://www.facebook.com/vmsgovista';
    $instagramUrl  = 'https://www.instagram.com/vmsgovista';

    $iconLuggage   = $iconUrl . 'luggage.svg';
    $iconHeadset   = $iconUrl . 'headset.svg';
    $iconShield    = $iconUrl . 'shield-check.svg';
    $iconPhone     = $iconUrl . 'phone.svg';
    $iconGlobe     = $iconUrl . 'globe.svg';
    $iconTag       = $iconUrl . 'tag.svg';
    $iconCalendar  = $iconUrl . 'calendar.svg';
    $iconUsers     = $iconUrl . 'users.svg';
    $iconFacebook  = $iconUrl . 'facebook.svg';
    $iconInstagram = $iconUrl . 'instagram.svg';
    $iconWhatsapp  = $iconUrl . 'whatsapp.svg';
    $preheader = 'Thank you for connecting with VMS Go Vista. Our travel team will respond within 24 hours.';

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$safeTitle}</title>
</head>
<body style="margin:0;padding:0;background-color:#edf2f7;font-family:Arial,Helvetica,sans-serif;color:#101828;">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;mso-hide:all;visibility:hidden;font-size:1px;line-height:1px;color:#edf2f7;">
    {$preheader}
  </div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#edf2f7;margin:0;padding:0;width:100%;">
    <tr>
      <td align="center" style="padding:28px 10px;">
        <table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:640px;background-color:#ffffff;border:1px solid #dbe4ea;border-radius:14px;overflow:hidden;">
          
          <!-- Banner -->
          <tr>
            <td style="padding:0;line-height:0;font-size:0;">
              <img src="{$bannerUrl}" alt="{$siteName}" width="640" style="display:block;width:100%;max-width:640px;height:auto;border:0;outline:none;text-decoration:none;">
            </td>
          </tr>

          <!-- Top feature bar -->
          <tr>
            <td style="background-color:#0a2540;padding:16px 10px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td width="33.33%" align="center" valign="top" style="padding:4px 6px;border-right:1px solid rgba(255,255,255,0.14);">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                      <tr>
                        <td align="center" valign="middle" style="width:40px;height:40px;background-color:#163554;border:1px solid rgba(255,255,255,0.25);border-radius:10px;">
                          <img src="{$iconLuggage}" width="18" height="18" alt="" style="width:18px;height:18px;border:0;vertical-align:middle;">
                        </td>
                      </tr>
                    </table>
                    <div style="font-size:10.5px;line-height:1.4;color:#ffffff;font-weight:700;letter-spacing:.08em;margin-top:7px;">CUSTOM TRAVEL PACKAGES</div>
                    <div style="font-size:10px;line-height:1.5;color:#b8c7d9;margin-top:1px;">Tailored for you</div>
                  </td>

                  <td width="33.33%" align="center" valign="top" style="padding:4px 6px;border-right:1px solid rgba(255,255,255,0.14);">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                      <tr>
                        <td align="center" valign="middle" style="width:40px;height:40px;background-color:#163554;border:1px solid rgba(255,255,255,0.25);border-radius:10px;">
                          <img src="{$iconHeadset}" width="18" height="18" alt="" style="width:18px;height:18px;border:0;vertical-align:middle;">
                        </td>
                      </tr>
                    </table>
                    <div style="font-size:10.5px;line-height:1.4;color:#ffffff;font-weight:700;letter-spacing:.08em;margin-top:7px;">24/7 SUPPORT</div>
                    <div style="font-size:10px;line-height:1.5;color:#b8c7d9;margin-top:1px;">We're here to help</div>
                  </td>

                  <td width="33.33%" align="center" valign="top" style="padding:4px 6px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                      <tr>
                        <td align="center" valign="middle" style="width:40px;height:40px;background-color:#163554;border:1px solid rgba(255,255,255,0.25);border-radius:10px;">
                          <img src="{$iconShield}" width="18" height="18" alt="" style="width:18px;height:18px;border:0;vertical-align:middle;">
                        </td>
                      </tr>
                    </table>
                    <div style="font-size:10.5px;line-height:1.4;color:#ffffff;font-weight:700;letter-spacing:.08em;margin-top:7px;">SAFE &amp; RELIABLE</div>
                    <div style="font-size:10px;line-height:1.5;color:#b8c7d9;margin-top:1px;">Your journey, our priority</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Main content -->
          <tr>
            <td style="padding:34px 40px 12px 40px;">
              <h1 style="margin:0;text-align:center;font-family:Georgia,'Times New Roman',serif;font-size:30px;line-height:1.25;color:#0a2540;font-weight:700;letter-spacing:-0.3px;">{$safeTitle}</h1>

              <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:14px auto 24px;">
                <tr>
                  <td style="width:58px;height:3px;background-color:#f26522;border-radius:2px;font-size:0;line-height:0;">&nbsp;</td>
                </tr>
              </table>

              <div style="font-size:14px;line-height:1.8;color:#475467;">
                {$bodyHtml}
              </div>
            </td>
          </tr>

          <!-- Immediate help block -->
          <tr>
            <td style="padding:0 40px 6px 40px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f3f6f9;border:1px solid #dbe4ea;border-radius:14px;">
                <tr>
                  <td valign="middle" style="padding:18px 20px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td valign="middle" style="width:46px;">
                          <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                              <td align="center" valign="middle" style="width:44px;height:44px;background-color:#0a2540;border-radius:50%;">
                                <img src="{$iconPhone}" width="18" height="18" alt="" style="width:18px;height:18px;border:0;vertical-align:middle;">
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td valign="middle" style="padding-left:14px;">
                          <div style="font-family:Georgia,'Times New Roman',serif;font-size:18px;line-height:1.3;color:#0a2540;font-weight:700;">Need Immediate Help?</div>
                          <div style="font-size:12px;line-height:1.7;color:#667085;">Call us now — our team will be happy to assist you.</div>
                        </td>
                      </tr>
                    </table>
                  </td>

                  <td align="right" valign="middle" style="padding:18px 20px;">
                    <div style="font-family:Georgia,'Times New Roman',serif;font-size:22px;line-height:1.2;color:#f26522;font-weight:800;">
                      <a href="tel:{$phoneTel}" style="color:#f26522;text-decoration:none;">{$phoneDisp}</a>
                    </div>
                    <div style="font-size:11px;line-height:1.6;color:#667085;margin-top:4px;">Mon - Sun | 9AM - 7PM</div>
                    <div style="font-size:11px;line-height:1.6;margin-top:7px;">
                      <a href="{$waUrl}" style="color:#0a2540;text-decoration:none;font-weight:700;">WhatsApp Support</a>
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Sign off -->
          <tr>
            <td align="center" style="padding:24px 40px 8px 40px;">
              <p style="margin:0;text-align:center;font-size:14px;line-height:1.8;color:#475467;">
                Warm Regards,<br>
                <strong style="color:#0a2540;">Team VMS Go Vista</strong>
              </p>
              <p style="margin:18px 0 0 0;text-align:center;font-family:Georgia,'Times New Roman',serif;font-size:14px;line-height:1.8;color:#0a2540;font-style:italic;">
                We look forward to helping you plan your next unforgettable journey!
              </p>
            </td>
          </tr>

          <!-- Bottom highlights -->
          <tr>
            <td style="padding:14px 40px 32px 40px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f8fafc;border:1px solid #e6edf3;border-radius:12px;">
                <tr>
                  <td width="25%" align="center" valign="top" style="padding:16px 6px;border-right:1px dotted #cfd8e3;">
                    <div style="width:34px;height:34px;background-color:#eaf0f6;border-radius:50%;margin:0 auto;text-align:center;line-height:34px;font-size:0;">
                      <img src="{$iconGlobe}" width="16" height="16" alt="" style="width:16px;height:16px;border:0;vertical-align:middle;">
                    </div>
                    <div style="font-size:11.5px;line-height:1.5;color:#0a2540;font-weight:700;margin-top:7px;">Wide Destinations</div>
                    <div style="font-size:10px;line-height:1.5;color:#7b8a99;">Across the Globe</div>
                  </td>

                  <td width="25%" align="center" valign="top" style="padding:16px 6px;border-right:1px dotted #cfd8e3;">
                    <div style="width:34px;height:34px;background-color:#eaf0f6;border-radius:50%;margin:0 auto;text-align:center;line-height:34px;font-size:0;">
                      <img src="{$iconTag}" width="16" height="16" alt="" style="width:16px;height:16px;border:0;vertical-align:middle;">
                    </div>
                    <div style="font-size:11.5px;line-height:1.5;color:#0a2540;font-weight:700;margin-top:7px;">Best Price</div>
                    <div style="font-size:10px;line-height:1.5;color:#7b8a99;">Guaranteed</div>
                  </td>

                  <td width="25%" align="center" valign="top" style="padding:16px 6px;border-right:1px dotted #cfd8e3;">
                    <div style="width:34px;height:34px;background-color:#eaf0f6;border-radius:50%;margin:0 auto;text-align:center;line-height:34px;font-size:0;">
                      <img src="{$iconCalendar}" width="16" height="16" alt="" style="width:16px;height:16px;border:0;vertical-align:middle;">
                    </div>
                    <div style="font-size:11.5px;line-height:1.5;color:#0a2540;font-weight:700;margin-top:7px;">Easy Booking</div>
                    <div style="font-size:10px;line-height:1.5;color:#7b8a99;">Hassle-Free</div>
                  </td>

                  <td width="25%" align="center" valign="top" style="padding:16px 6px;">
                    <div style="width:34px;height:34px;background-color:#eaf0f6;border-radius:50%;margin:0 auto;text-align:center;line-height:34px;font-size:0;">
                      <img src="{$iconUsers}" width="16" height="16" alt="" style="width:16px;height:16px;border:0;vertical-align:middle;">
                    </div>
                    <div style="font-size:11.5px;line-height:1.5;color:#0a2540;font-weight:700;margin-top:7px;">Trusted by</div>
                    <div style="font-size:10px;line-height:1.5;color:#7b8a99;">Thousands</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#0a2540;padding:30px 40px 14px 40px;border-top:4px solid #f26522;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td width="34%" valign="top" style="padding:0 18px 18px 0;">
                    <div style="font-size:14px;line-height:1.4;color:#ffffff;font-weight:800;letter-spacing:.03em;">VMS GO VISTA PVT. LTD.</div>
                    <div style="font-size:12px;line-height:1.9;color:#c8d5e3;margin-top:8px;">Custom Tours &amp; Travel Packages</div>
                  
                    <div style="font-size:12px;line-height:1.9;color:#c8d5e3;">Reliable support for memorable travel experiences.</div>
                  </td>

                  <td width="28%" valign="top" style="padding:0 18px 18px 18px;border-left:1px solid rgba(255,255,255,0.12);">
                    <div style="font-size:14px;line-height:1.4;color:#ffffff;font-weight:800;">Quick Links</div>
                    <div style="margin-top:10px;font-size:12px;line-height:2;">
                      <div><a href="{$homeUrl}" style="color:#c8d5e3;text-decoration:none;">Home</a></div>
                      <div><a href="{$packagesUrl}" style="color:#c8d5e3;text-decoration:none;">Packages</a></div>
                      <div><a href="{$aboutUrl}" style="color:#c8d5e3;text-decoration:none;">About Us</a></div>
                      <div><a href="{$contactUrl}" style="color:#c8d5e3;text-decoration:none;">Contact Us</a></div>
                    </div>
                  </td>

                  <td width="38%" valign="top" style="padding:0 0 18px 18px;border-left:1px solid rgba(255,255,255,0.12);">
                    <div style="font-size:14px;line-height:1.4;color:#ffffff;font-weight:800;">Connect With Us</div>

                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top:10px;">
                      <tr>
                        <td style="padding-right:6px;">
                          <a href="{$facebookUrl}" style="display:block;width:30px;height:30px;background-color:#1877F2;border-radius:50%;text-decoration:none;text-align:center;line-height:30px;">
                            <img src="{$iconFacebook}" width="15" height="15" alt="Facebook" style="width:15px;height:15px;border:0;vertical-align:middle;">
                          </a>
                        </td>
                        <td style="padding-right:6px;">
                          <a href="{$instagramUrl}" style="display:block;width:30px;height:30px;background-color:#E4405F;border-radius:50%;text-decoration:none;text-align:center;line-height:30px;">
                            <img src="{$iconInstagram}" width="15" height="15" alt="Instagram" style="width:15px;height:15px;border:0;vertical-align:middle;">
                          </a>
                        </td>
                        <td>
                          <a href="{$waUrl}" style="display:block;width:30px;height:30px;background-color:#25D366;border-radius:50%;text-decoration:none;text-align:center;line-height:30px;">
                            <img src="{$iconWhatsapp}" width="15" height="15" alt="WhatsApp" style="width:15px;height:15px;border:0;vertical-align:middle;">
                          </a>
                        </td>
                      </tr>
                    </table>

                    <div style="margin-top:12px;font-size:12px;line-height:1.9;color:#c8d5e3;">
                      <div><a href="{$emailHref}" style="color:#c8d5e3;text-decoration:none;">{$emailAddress}</a></div>
                      <div><a href="{$websiteHref}" style="color:#c8d5e3;text-decoration:none;">{$websiteDisp}</a></div>
                      <div><a href="tel:{$phoneTel}" style="color:#c8d5e3;text-decoration:none;">{$phoneDisp}</a></div>
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer note -->
          <tr>
            <td align="center" style="background-color:#081a2e;padding:14px 30px 22px 30px;">
              <div style="font-size:10.5px;line-height:1.7;color:#9bb0c6;">
                This is an automated message. Please do not reply to this email.
              </div>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}

function buildEnquiryTableHtml(array $enq): string {
    $fields = [
        'Name'        => trim(($enq['first_name'] ?? '') . ' ' . ($enq['last_name'] ?? '')),
        'Email'       => $enq['email'] ?? '',
        'Phone'       => $enq['phone'] ?? '',
        'Country'     => $enq['country'] ?? '',
        'Package'     => $enq['package_title'] ?? 'General enquiry',
        'Travelling Date' => ($enq['travel_date'] ?? '') !== '' ? $enq['travel_date'] : '—',
        'Adults'      => ($enq['adults'] ?? '') !== '' ? $enq['adults'] : '—',
        'Children'    => ($enq['children'] ?? '') !== '' ? $enq['children'] : '—',
    ];
    return buildFieldsTable($fields);
}

function buildContactTableHtml(array $contact): string {
    $fields = [
        'Name'    => $contact['name'] ?? '',
        'Email'   => $contact['email'] ?? '',
        'Company' => $contact['company'] ?? '',
        'Website' => $contact['website'] ?? '',
    ];
    return buildFieldsTable($fields);
}

function buildFieldsTable(array $fields): string {
    $rows = '';
    foreach ($fields as $label => $val) {
        $v = ($val === '' || $val === null) ? '—' : e($val);
        $rows .= '<tr>'
               . '<td style="padding:11px 14px;font-size:12.5px;color:#667085;width:145px;border-bottom:1px solid #edf2f7;background:#f9fbfc;">' . e($label) . '</td>'
               . '<td style="padding:11px 14px;font-size:13px;color:#101828;border-bottom:1px solid #edf2f7;background:#ffffff;"><strong>' . $v . '</strong></td>'
               . '</tr>';
    }

    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:separate;border-spacing:0;background:#ffffff;border:1px solid #e5edf3;border-radius:12px;overflow:hidden;">'
         . $rows
         . '</table>';
}

function buildMessageBlock(string $message): string {
    $msg = trim($message);
    if ($msg === '') return '';

    return '<div style="margin-top:16px;padding:16px 18px;background:#f9fbfc;border:1px solid #e5edf3;border-radius:12px;">'
         . '<strong style="display:block;font-size:12px;color:#344054;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;">Message</strong>'
         . '<p style="margin:0;font-size:13px;color:#475467;line-height:1.8;white-space:pre-wrap;">' . e($msg) . '</p>'
         . '</div>';
}

// ── Native mail() fallback ────────────────────────────────────
function sendMailNative(string $to, string $subject, string $htmlBody, string $plainText = '', string $fromEmail = '', string $fromName = ''): array {
    if ($fromEmail === '') {
        $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $fromEmail = 'noreply@' . $host;
    }

    $from = $fromName !== ''
        ? encodeMailHeader($fromName) . ' <' . $fromEmail . '>'
        : $fromEmail;

    $headers = "From: {$from}\r\n"
             . "Reply-To: {$fromEmail}\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Content-Type: text/html; charset=UTF-8\r\n"
             . "X-Mailer: VMS Go Vista";

    $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $htmlBody, $headers);

    return [
        'success' => (bool)$ok,
        'message' => $ok ? 'Sent via PHP mail().' : 'PHP mail() failed.'
    ];
}

function encodeMailHeader(string $s): string {
    return preg_match('/[\x80-\xff]/', $s)
        ? '=?UTF-8?B?' . base64_encode($s) . '?='
        : $s;
}

// ── Pure PHP SMTP client ──────────────────────────────────────
class VmsSmtpMailer
{
    private string $host;
    private int $port;
    private string $user;
    private string $pass;
    private string $enc;
    private string $fromEmail;
    private string $fromName;
    private $sock = null;
    private int $timeout = 25;

    public function __construct(array $cfg)
    {
        $this->host      = (string)($cfg['host'] ?? '');
        $this->port      = (int)($cfg['port'] ?? 587);
        $this->user      = (string)($cfg['user'] ?? '');
        $this->pass      = (string)($cfg['pass'] ?? '');
        $this->enc       = (string)($cfg['encryption'] ?? 'tls');
        $this->fromEmail = (string)($cfg['fromEmail'] ?? '');
        $this->fromName  = (string)($cfg['fromName'] ?? '');
    }

    public function send(string $to, string $subject, string $html, string $plain = ''): array
    {
        try {
            if (!$this->connect()) {
                return ['success' => false, 'message' => 'Could not connect to SMTP server (' . $this->host . ':' . $this->port . ').'];
            }

            if (!$this->authenticate()) {
                $this->close();
                return ['success' => false, 'message' => 'SMTP authentication failed. Check username / password / App Password.'];
            }

            if (!$this->sendMessage($to, $subject, $html, $plain)) {
                $this->close();
                return ['success' => false, 'message' => 'SMTP server rejected the message (recipient / sender address).'];
            }

            $this->close();
            return ['success' => true, 'message' => 'Email sent successfully via SMTP.'];
        } catch (Throwable $e) {
            $this->close();
            return ['success' => false, 'message' => 'SMTP error: ' . $e->getMessage()];
        }
    }

    private function connect(): bool
    {
        $transport = ($this->enc === 'ssl') ? 'ssl' : 'tcp';
        $remote    = $transport . '://' . $this->host . ':' . $this->port;

        // Resolve CA file cross-platform
        $caFile = $_ENV['SMTP_CA_FILE'] ?? null;
        if (!$caFile) {
            // Common locations
            $candidates = [
                '/etc/ssl/certs/ca-certificates.crt',           // Linux/Debian/Ubuntu
                '/etc/pki/tls/certs/ca-bundle.crt',             // RHEL/CentOS/Fedora
                '/etc/ssl/ca-bundle.pem',                       // Alpine
                'C:\xampp1\php\extras\ssl\cacert.pem',          // XAMPP Windows
                'C:\xampp\php\extras\ssl\cacert.pem',           // XAMPP alt
                'C:\Program Files\OpenSSL\certs\ca-bundle.crt', // OpenSSL Windows
            ];
            foreach ($candidates as $c) {
                if (is_file($c)) { $caFile = $c; break; }
            }
        }
        // Capath for Linux
        $capath = is_dir('/etc/ssl/certs') ? '/etc/ssl/certs' : null;

        $sslOptions = [
            'verify_peer'       => true,
            'verify_peer_name'  => true,
            'allow_self_signed' => false,
        ];
        if ($caFile) $sslOptions['cafile'] = $caFile;
        if ($capath) $sslOptions['capath'] = $capath;

        // Fallback: if no CA file found on Windows, use system store
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && !$caFile && !$capath) {
            $sslOptions['verify_peer'] = false; // Will use Windows cert store implicitly
        }

        $ctx = stream_context_create(['ssl' => $sslOptions]);

        $this->sock = @stream_socket_client($remote, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $ctx);
        if (!$this->sock) return false;

        stream_set_timeout($this->sock, $this->timeout);

        $this->readReply(); // 220
        $this->cmd('EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));

        if ($this->enc === 'tls') {
            if ($this->cmd('STARTTLS') !== 220) return false;
            $crypto = stream_socket_enable_crypto($this->sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$crypto) return false;
            $this->cmd('EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        }

        return true;
    }

    private function authenticate(): bool
    {
        if ($this->user === '') return true;

        if ($this->cmd('AUTH LOGIN') !== 334) return false;
        if ($this->raw(base64_encode($this->user)) !== 334) return false;
        if ($this->raw(base64_encode($this->pass)) !== 235) return false;

        return true;
    }

    private function sendMessage(string $to, string $subject, string $html, string $plain): bool
    {
        if ($this->fromEmail === '') return false;

        if ($this->cmd('MAIL FROM:<' . $this->fromEmail . '>') !== 250) return false;
        if ($this->cmd('RCPT TO:<' . $to . '>') !== 250) return false;
        if ($this->cmd('DATA') !== 354) return false;

        if ($plain === '') {
            $plain = strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $html));
        }

        $boundary = 'vms_' . md5(uniqid('', true));

        $headers = [];
        $headers[] = 'From: ' . ($this->fromName !== '' ? encodeMailHeader($this->fromName) . ' <' . $this->fromEmail . '>' : $this->fromEmail);
        $headers[] = 'To: <' . $to . '>';
        $headers[] = 'Subject: ' . encodeMailHeader($subject);
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'Message-ID: <' . uniqid('vms') . '@' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . '>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
        $headers[] = 'X-Mailer: VMS Go Vista';

        $body = "--{$boundary}\r\n"
              . "Content-Type: text/plain; charset=UTF-8\r\n"
              . "Content-Transfer-Encoding: base64\r\n\r\n"
              . chunk_split(base64_encode($plain)) . "\r\n"
              . "--{$boundary}\r\n"
              . "Content-Type: text/html; charset=UTF-8\r\n"
              . "Content-Transfer-Encoding: base64\r\n\r\n"
              . chunk_split(base64_encode($html)) . "\r\n"
              . "--{$boundary}--\r\n";

        fwrite($this->sock, implode("\r\n", $headers) . "\r\n\r\n" . $body);
        return $this->cmd('.') === 250;
    }

    private function cmd(string $cmd): int
    {
        fwrite($this->sock, $cmd . "\r\n");
        return $this->readReply();
    }

    private function raw(string $payload): int
    {
        fwrite($this->sock, $payload . "\r\n");
        return $this->readReply();
    }

    private function readReply(): int
    {
        $code = 0;
        while (($line = fgets($this->sock, 515)) !== false) {
            $code = (int)substr($line, 0, 3);
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $code;
    }

    private function close(): void
    {
        if ($this->sock) {
            @fwrite($this->sock, "QUIT\r\n");
            @fclose($this->sock);
            $this->sock = null;
        }
    }
}
