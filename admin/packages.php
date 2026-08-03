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
  <a href="<?= SITE_URL ?>/admin/package-create.php" class="btn-primary-admin">
    <i class="fa-solid fa-plus"></i> Add Package
  </a>
</div>

<div class="card-box p-0">
  <table class="admin-table">
    <thead>
      <tr>
        <th width="60">Image</th>
        <th>Title</th>
        <th>Destination</th>
        <th>Duration</th>
        <th>Price</th>
        <th>Flags</th>
        <th>Status</th>
        <th>Actions</th>
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
          <a href="<?= SITE_URL ?>/admin/package-edit.php?id=<?= $pkg['id'] ?>" class="btn-edit-admin">Edit</a>

          <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this package?')">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="delete_id" value="<?= $pkg['id'] ?>">
            <button type="submit" class="btn-danger-admin">Del</button>
          </form>

          <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="toggle_id" value="<?= $pkg['id'] ?>">
            <input type="hidden" name="toggle_status" value="<?= $pkg['status'] ?>">
            <button type="submit" class="btn-secondary-admin" style="font-size:11px;padding:6px 10px;">
              <?= $pkg['status']==='published' ? 'Unpublish' : 'Publish' ?>
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
