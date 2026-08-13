<?php
// This file is included at the top of every admin page
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/mailer.php';
requireLogin();
$adminUser = currentUser();
$flash     = getFlash();

// Which page are we on? (for active nav highlight)
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>VMS Go Vista Admin</title>
<link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/fav.png">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/vendor/bootstrap.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/plugins/fontawesome.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin-liquid.css">
</head>
<body class="admin-liquid">

<!-- Sidebar -->
<aside class="admin-sidebar">
  <div class="sb-brand">
    <h2><span class="brand-mark"><i class="fa-solid fa-mountain-sun"></i></span> VMS Go Vista</h2>
    <p>Admin Panel</p>
  </div>
  <nav>
    <span class="sb-section">Main</span>
    <a href="<?= SITE_URL ?>/admin/index.php" class="<?= $currentPage==='index'?'active':'' ?>">
      <i class="fa-solid fa-gauge-high"></i> Dashboard
    </a>
    <span class="sb-section">Packages</span>
    <a href="<?= SITE_URL ?>/admin/packages.php" class="<?= in_array($currentPage,['packages','package-create','package-edit'])?'active':'' ?>">
      <i class="fa-solid fa-suitcase"></i> All Packages
    </a>
    <a href="<?= SITE_URL ?>/admin/package-create.php" class="<?= $currentPage==='package-create'?'active':'' ?>">
      <i class="fa-solid fa-plus"></i> Add Package
    </a>
    <span class="sb-section">CRM</span>
    <a href="<?= SITE_URL ?>/admin/leads.php" class="<?= in_array($currentPage,['leads','lead-view'])?'active':'' ?>">
      <i class="fa-solid fa-users"></i> Leads Pipeline
      <?php
      try {
          $navNewLeads = (int)getDB()->query("SELECT COUNT(*) FROM leads WHERE status='new'")->fetchColumn();
          if ($navNewLeads > 0) {
              echo '<span style="margin-left:auto;background:#f79009;color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;">' . $navNewLeads . '</span>';
          }
      } catch (Throwable $e) { /* ignore */ }
      ?>
    </a>
    <a href="<?= SITE_URL ?>/admin/enquiries.php" class="<?= in_array($currentPage,['enquiries','enquiry-view'])?'active':'' ?>">
      <i class="fa-solid fa-envelope"></i> Enquiries
      <?php
      try {
          $navNew = (int)getDB()->query("SELECT COUNT(*) FROM enquiries WHERE status='new'")->fetchColumn();
          if ($navNew > 0) {
              echo '<span style="margin-left:auto;background:#f79009;color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;">' . $navNew . '</span>';
          }
      } catch (Throwable $e) { /* ignore */ }
      ?>
    </a>
    <a href="<?= SITE_URL ?>/admin/contacts.php" class="<?= $currentPage==='contacts'?'active':'' ?>">
      <i class="fa-solid fa-envelope-open-text"></i> Contact Messages
      <?php
      try {
          ensureContactsTable();
          $navContacts = (int)getDB()->query("SELECT COUNT(*) FROM contacts WHERE status='new'")->fetchColumn();
          if ($navContacts > 0) {
              echo '<span style="margin-left:auto;background:#f79009;color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;">' . $navContacts . '</span>';
          }
      } catch (Throwable $e) { /* ignore */ }
      ?>
    </a>
    <a href="<?= SITE_URL ?>/admin/invoices.php" class="<?= in_array($currentPage,['invoices','invoice-create','invoice-view'])?'active':'' ?>">
      <i class="fa-solid fa-file-invoice"></i> Billing &amp; Invoices
    </a>
    <span class="sb-section">Settings</span>
    <a href="<?= SITE_URL ?>/admin/settings.php" class="<?= $currentPage==='settings'?'active':'' ?>">
      <i class="fa-solid fa-gear"></i> Package Settings
    </a>
    <span class="sb-section">Website</span>
    <a href="<?= SITE_URL ?>/index-three.php" target="_blank">
      <i class="fa-solid fa-arrow-up-right-from-square"></i> View Website
    </a>
  </nav>
  <div class="sb-user">
    <div class="user-row">
      <div class="avatar"><?= e(strtoupper(substr($adminUser['name'], 0, 1))) ?></div>
      <div>
        <div class="name"><?= e($adminUser['name']) ?></div>
        <div class="role"><?= e(ucfirst($adminUser['role'])) ?></div>
      </div>
    </div>
    <a href="<?= SITE_URL ?>/admin/logout.php" class="logout-link">
      <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
  </div>
</aside>

<!-- Main -->
<div class="admin-main">
  <div class="admin-topbar">
    <div class="topbar-left">
      <h1 class="page-title"><?= isset($pageTitle) ? e($pageTitle) : 'Dashboard' ?></h1>
      <?php if (!empty($pageSubtitle)): ?>
      <p class="page-subtitle"><?= e($pageSubtitle) ?></p>
      <?php endif; ?>
    </div>
    <div class="actions">
      <a href="<?= SITE_URL ?>/package.php" target="_blank" class="btn-topbar"><i class="fa-solid fa-eye"></i> View Tours</a>
      <?php if ($currentPage !== 'package-create'): ?>
      <a href="<?= SITE_URL ?>/admin/package-create.php" class="btn-topbar btn-primary-admin" style="color:#fff;border:none;"><i class="fa-solid fa-plus"></i> New package</a>
      <?php endif; ?>
      <?php if (in_array($currentPage, ['index', 'packages', 'package-edit'], true)): ?>
      <a href="<?= SITE_URL ?>/admin/enquiries.php" class="btn-topbar"><i class="fa-solid fa-envelope"></i> View Leads</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="admin-body">

  <?php if ($flash): ?>
    <div class="alert-<?= $flash['type']==='success'?'success':'error' ?>">
      <i class="fa-solid fa-<?= $flash['type']==='success'?'check-circle':'circle-exclamation' ?> me-2"></i>
      <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>
