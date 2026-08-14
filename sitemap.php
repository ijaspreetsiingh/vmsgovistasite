<?php
// ── Dynamic XML Sitemap ─────────────────────────────────────────
// Served at /sitemap.xml via .htaccess rewrite. Auto-includes every
// published package from the database plus all static pages.
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/xml; charset=utf-8');

// Production canonical base — use the live domain.
$base = 'https://vmsgovista.com';

$today = date('Y-m-d');

// Static pages: [path, priority, changefreq]
$pages = [
    ['',              '1.0', 'daily'],
    ['package',       '0.9', 'daily'],
    ['about',         '0.8', 'monthly'],
    ['service',       '0.7', 'monthly'],
    ['contact',       '0.8', 'monthly'],
    ['booking',       '0.8', 'monthly'],
];

$urls = '';
foreach ($pages as [$path, $priority, $freq]) {
    $loc = $base . '/' . $path;
    $urls .= "  <url>\n"
          . "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n"
          . "    <lastmod>{$today}</lastmod>\n"
          . "    <changefreq>{$freq}</changefreq>\n"
          . "    <priority>{$priority}</priority>\n"
          . "  </url>\n";
}

// Package detail pages from DB
try {
    $db = getDB();
    $stmt = $db->query("SELECT slug FROM packages ORDER BY id");
    foreach ($stmt as $row) {
        $slug = trim((string)($row['slug'] ?? ''));
        if ($slug === '') continue;
        $loc = $base . '/package-details/' . rawurlencode($slug);
        $urls .= "  <url>\n"
              . "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n"
              . "    <lastmod>{$today}</lastmod>\n"
              . "    <changefreq>weekly</changefreq>\n"
              . "    <priority>0.8</priority>\n"
              . "  </url>\n";
    }
} catch (Throwable $e) {
    // If DB is unavailable, still serve the static portion.
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?= $urls ?>
</urlset>
