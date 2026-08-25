<?php
require_once 'config/db.php';
require_once 'includes/mailer.php';

// Quick test with Gmail SMTP (replace with real credentials)
setSetting('mail_enabled', '1');
setSetting('smtp_host', 'smtp.gmail.com');
setSetting('smtp_port', '587');
setSetting('smtp_encryption', 'tls');
setSetting('smtp_user', 'your@gmail.com');        // CHANGE THIS
setSetting('smtp_pass', 'your-app-password');     // CHANGE THIS (16-char App Password)
setSetting('smtp_from_email', 'your@gmail.com');  // CHANGE THIS
setSetting('smtp_from_name', 'VMS Test');
setSetting('admin_notify_email', 'your@gmail.com'); // CHANGE THIS

echo "Sending test email...\n";
$res = sendMail('your@gmail.com', 'VMS SMTP Test', '<h1>Test</h1><p>If you see this, SMTP works!</p>');
echo json_encode($res, JSON_PRETTY_PRINT);