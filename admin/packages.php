<?php
$pageTitle = 'All Packages';
require_once __DIR__ . '/includes/header.php';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verifyCsrf();
    $delId = (int)$_POST['delete_id'];
    $db = getDB();
    $db->prepare('DELETE FROM packages WHERE id = ?')->execute([$delId]);
    setFlash('success', 'Package deleted successfully.');
    redirect(SITE_URL . '/admin/packages.php');
}

// Handle quick status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    verifyCsrf();
    $tId  = (int)$_POST['toggle_id'];
    $tSt  = $_POST['toggle_status'];
    $nSt  = ($tSt === 'published') ? 'draft' : 'published';
    $db   = getDB();
    $db->prepare('UPDATE packages SET status=? WHERE id=?')->execute([$nSt, $tId]);
    setFlash('success', 'Status updated.');
    redirect(SITE_URL . '/admin/packages.php');
}

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$search  = trim($_GET['search'] ?? '');
$db      = getDB();

$where  = '1=1';
$params = [];
if ($search) {
    $where  = '(title LIKE ? OR destination LIKE ?)';
    $params = ["%$search%", "%$search%"];
}
$total   = $db->prepare("SELECT COUNT(*) FROM packages WHERE $where");
$total->execute($params);
$total   = (int)$total->fetchColumn();
$lastPage = max(1, (int)ceil($total / $perPage));
$offset  = ($page - 1) * $perPage;
$params2 = array_merge($params, [$perPage, $offset]);
$packages = fetchAll("SELECT * FROM packages WHERE $where ORDER BY id DESC LIMIT ? OFFSET ?", $params2);
?>

<div class="liquid-toolbar">
  <form method="GET" style="display:flex;gap:10px;">
    <input type="text" name="search" placeholder="Search packages..." class="form-control-admin" style="width:260px;" value="<?= e($search) ?>">
    <button type="submit" class="btn-secondary-admin"><i class="fa-solid fa-magnifying-glass"></i></button>
    <?php if ($search): ?><a href="<?= SITE_URL ?>/admin/packages.php" class="btn-secondary-admin">Clear</a><?php endif; ?>
  </form>
  <a href="<?= SITE_URL ?>/admin/package-create.php" class="btn-primary-admin" style="color:#fff;">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg> Add Package
  </a>
</div>

<div class="card-box p-0">
  <table class="admin-table">
    <thead>
      <tr>
        <th width="60"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>Image</th>
        <th><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>Title</th>
        <th><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>Destination</th>
        <th><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Duration</th>
        <th><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>Price</th>
        <th><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>Flags</th>
        <th><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>Status</th>
        <th><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($packages as $pkg): ?>
      <tr>
        <td>
          <img src="<?= e(packageImageUrl($pkg['main_image'])) ?>" alt="" class="thumb">
        </td>
        <td class="cell-title" style="max-width:200px;">
          <?= e($pkg['title']) ?>
          <div style="font-size:11px;color:#6e7681;margin-top:2px;">/<?= e($pkg['slug']) ?></div>
        </td>
        <td><?= e($pkg['destination'] ?? '—') ?></td>
        <td><?= (int)$pkg['days'] ?>D / <?= (int)$pkg['nights'] ?>N</td>
        <td>
          <?php if ($pkg['price_discounted']): ?>
            <span style="color:#3fb950;font-weight:600;"><?= formatPrice((float)$pkg['price_discounted'], $pkg['currency'] ?? 'INR') ?></span>
            <span style="color:#6e7681;text-decoration:line-through;font-size:11px;margin-left:4px;"><?= formatPrice((float)$pkg['price_original'], $pkg['currency'] ?? 'INR') ?></span>
          <?php else: ?>
            <span style="color:#c9d1d9;"><?= formatPrice((float)$pkg['price_original'], $pkg['currency'] ?? 'INR') ?></span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($pkg['is_featured']): ?><span class="badge-flag">F</span><?php endif; ?>
          <?php if ($pkg['is_popular']): ?><span class="badge-flag">P</span><?php endif; ?>
          <?php if ($pkg['show_on_homepage']): ?><span class="badge-flag">H</span><?php endif; ?>
        </td>
        <td>
          <?php if ($pkg['status'] === 'published'): ?><span class="badge-pub">Published</span>
          <?php elseif ($pkg['status'] === 'draft'): ?><span class="badge-draft">Draft</span>
          <?php else: ?><span class="badge-arc">Archived</span><?php endif; ?>
        </td>
        <td>
          <a href="<?= SITE_URL ?>/admin/package-edit.php?id=<?= $pkg['id'] ?>" class="btn-edit-admin"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg></a>

          <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this package?')">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="delete_id" value="<?= $pkg['id'] ?>">
            <button type="submit" class="btn-danger-admin"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg></button>
          </form>

          <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="toggle_id" value="<?= $pkg['id'] ?>">
            <input type="hidden" name="toggle_status" value="<?= $pkg['status'] ?>">
            <button type="submit" class="btn-secondary-admin" style="font-size:11px;padding:6px 10px;">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-1px;margin-right:2px;"><?= $pkg['status']==='published' ? '<path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" x2="12" y1="2" y2="12"/>' : '<path d="M5 12l5 5 5-5"/><path d="M12 2v10"/>' ?></svg><?= $pkg['status']==='published' ? 'Unpublish' : 'Publish' ?>
            </button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($packages)): ?>
      <tr><td colspan="8" style="text-align:center;color:#6e7681;padding:32px;">No packages found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($lastPage > 1): ?>
<div class="admin-pagination">
  <?php for ($i=1; $i<=$lastPage; $i++): ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
