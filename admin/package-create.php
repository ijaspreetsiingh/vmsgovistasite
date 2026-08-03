<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/package-save.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (empty(trim($_POST['title'] ?? ''))) {
        setFlash('error', 'Package title is required.');
    } else {
        try {
            $pkgId = savePackage($_POST, $_FILES, false);
            setFlash('success', 'Package created successfully!');
            redirect(SITE_URL . '/admin/package-edit.php?id=' . $pkgId);
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Add New Package';
require_once __DIR__ . '/includes/header.php';

$pkg = [];
require_once __DIR__ . '/includes/package-form.php';
require_once __DIR__ . '/includes/footer.php';
