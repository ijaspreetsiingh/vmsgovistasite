<?php
// Database configuration - edit these values to match your server
// Supports environment variables for production security
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '1234');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'vmsgovista');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
// ── Dynamic SITE_URL: works on localhost, LAN IP (192.168.x.x), or a real domain ──
// The base path is derived from the project folder relative to the web root, so it
// stays correct on EVERY page (front, admin, api) — the old dirname(SCRIPT_NAME)
// approach appended /admin or /api to the URL on those pages.
$__https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$__scheme = $__https ? 'https' : 'http';
$__host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Validate host against allowlist to prevent Host header injection
$__allowedHosts = ['localhost', '127.0.0.1', 'vmsgovista.com', 'www.vmsgovista.com'];
if (!in_array($__host, $__allowedHosts, true) && !preg_match('/^192\.168\.\d+\.\d+$/', $__host)) {
    $__host = 'localhost';
}

// Base path = project folder relative to the web root (e.g. /vms/touriza-htm)
$__docRoot = rtrim(str_replace('\\', '/', (string)($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$__projDir = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
$__base    = '';
// stripos: Windows drive-letter casing can differ between DOCUMENT_ROOT and __DIR__
if ($__docRoot !== '' && stripos($__projDir, $__docRoot) === 0) {
    $__base = substr($__projDir, strlen($__docRoot));
}
// Fallback: if DOCUMENT_ROOT was empty/not a prefix (CLI, unusual setups),
// strip known subfolders from the script path to reach the project root
if ($__base === '' && isset($_SERVER['SCRIPT_NAME'])) {
    $__scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $__base = preg_replace('#/(config|admin|api|includes)$#', '', $__scriptDir) ?? '';
}
define('SITE_URL', $__scheme . '://' . $__host . $__base);  // no trailing slash
unset($__https, $__scheme, $__host, $__docRoot, $__projDir, $__base, $__scriptDir, $__allowedHosts);
define('SITE_NAME', 'VMS Go Vista');
define('UPLOAD_DIR', __DIR__ . '/../uploads/packages/');
define('UPLOAD_URL', SITE_URL . '/uploads/packages/');

// Session configuration
define('SESSION_TIMEOUT', 3600); // 1 hour

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        // Only add SSL CA if explicitly set (for production)
        $sslCa = $_ENV['DB_SSL_CA'] ?? null;
        if ($sslCa && is_file($sslCa)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
        }
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            // Don't expose internal error details
            if (php_sapi_name() !== 'cli') {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Database connection failed']);
            }
            exit;
        }
    }
    return $pdo;
}
