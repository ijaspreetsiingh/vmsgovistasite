<?php

// ============================================================
// Database Configuration
// ============================================================

define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'vmsgovis1_admin');
define('DB_PASS', $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: 'Ijaspreet@121#..');
define('DB_NAME', $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'vmsgovis1_vms');
define('DB_CHARSET', 'utf8mb4');


// ============================================================
// Site Configuration
// ============================================================

// Production domain
$siteUrl = $_ENV['SITE_URL'] ?? getenv('SITE_URL') ?: 'https://vmsgovista.com';

define('SITE_URL', rtrim($siteUrl, '/'));

define('SITE_NAME', 'VMS Go Vista');


// ============================================================
// Upload Configuration
// ============================================================

define(
    'UPLOAD_DIR',
    __DIR__ . '/../uploads/packages/'
);

define(
    'UPLOAD_URL',
    SITE_URL . '/uploads/packages/'
);


// ============================================================
// Session Configuration
// ============================================================

define('SESSION_TIMEOUT', 3600); // 1 hour


// ============================================================
// Database Connection
// ============================================================

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {

        $dsn = 'mysql:host=' . DB_HOST
             . ';dbname=' . DB_NAME
             . ';charset=' . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Optional MySQL SSL CA
        $sslCa = $_ENV['DB_SSL_CA'] ?? getenv('DB_SSL_CA') ?: null;

        if ($sslCa && is_file($sslCa)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
        }

        try {

            $pdo = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                $options
            );

        } catch (PDOException $e) {

            error_log(
                'DB connection failed: ' . $e->getMessage()
            );

            http_response_code(500);

            if (php_sapi_name() !== 'cli') {

                header('Content-Type: application/json');

                echo json_encode([
                    'error' => 'Database connection failed'
                ]);
            }

            exit;
        }
    }

    return $pdo;
}