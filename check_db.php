<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

// Check if packages table exists and has data
$stmt = $db->query("SELECT COUNT(*) as total FROM packages");
$count = $stmt->fetch();
echo "Total packages in DB: " . $count['total'] . "\n";

// Check published packages
$stmt = $db->query("SELECT COUNT(*) as total FROM packages WHERE status='published'");
$count = $stmt->fetch();
echo "Published packages: " . $count['total'] . "\n";

// Check packages with show_on_homepage=1
$stmt = $db->query("SELECT COUNT(*) as total FROM packages WHERE status='published' AND show_on_homepage=1");
$count = $stmt->fetch();
echo "Homepage packages (show_on_homepage=1): " . $count['total'] . "\n";

// Check packages with is_popular=1
$stmt = $db->query("SELECT COUNT(*) as total FROM packages WHERE status='published' AND is_popular=1");
$count = $stmt->fetch();
echo "Popular packages (is_popular=1): " . $count['total'] . "\n";

// Show sample data
$stmt = $db->query("SELECT id, title, status, show_on_homepage, is_popular FROM packages LIMIT 5");
$rows = $stmt->fetchAll();
echo "\nSample packages:\n";
print_r($rows);

// Test getHomepagePackages function
echo "\n--- Testing getHomepagePackages() ---\n";
$pkgs = getHomepagePackages(4);
echo "Returned " . count($pkgs) . " packages\n";
print_r($pkgs);
