<?php
$pageTitle = 'Edit Package';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/package-save.php';

$editId = (int)($_GET['id'] ?? 0);
if (!$editId) { setFlash('error', 'Invalid package ID.'); redirect(SITE_URL . '/admin/packages.php'); }

$pkg = getPackageById($editId);
if (!$pkg) { setFlash('error', 'Package not found.'); redirect(SITE_URL . '/admin/packages.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (empty(trim($_POST['title'] ?? ''))) {
        setFlash('error', 'Package title is required.');
    } else {
        try {
            savePackage($_POST, $_FILES, true, $editId);
            setFlash('success', 'Package updated successfully!');
            // Reload the package with fresh data
            $pkg = getPackageById($editId);
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Edit: ' . ($pkg['title'] ?? 'Package');
?>
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
  <a href="<?= SITE_URL ?>/package-details.php?slug=<?= e($pkg['slug']) ?>" target="_blank" class="btn-secondary-admin" style="font-size:13px;">
    <i class="fa-solid fa-eye"></i> View Live
  </a>
  <span style="color:#6b7280;font-size:14px;font-weight:500;">Package ID: <?= $editId ?> · Slug: /<?= e($pkg['slug']) ?></span>
</div>
<?php
require_once __DIR__ . '/includes/package-form.php';
require_once __DIR__ . '/includes/footer.php';
