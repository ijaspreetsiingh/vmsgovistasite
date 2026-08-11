<?php
$pageTitle = 'Contact Messages';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
ensureContactsTable();

// ── Handle actions ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($id) {
        if ($action === 'mark_read') {
            $db->prepare("UPDATE contacts SET status='read' WHERE id=?")->execute([$id]);
            setFlash('success', 'Message marked as read.');
        } elseif ($action === 'mark_new') {
            $db->prepare("UPDATE contacts SET status='new' WHERE id=?")->execute([$id]);
            setFlash('success', 'Message marked as new.');
        } elseif ($action === 'delete') {
            $db->prepare('DELETE FROM contacts WHERE id=?')->execute([$id]);
            setFlash('success', 'Message deleted.');
        }
    }
    redirect(SITE_URL . '/admin/contacts.php');
}

// ── Filters ───────────────────────────────────────────
$statusFilter = $_GET['status'] ?? '';
$searchQuery  = trim($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 25;

$where  = [];
$params = [];
if (in_array($statusFilter, ['new', 'read'], true)) {
    $where[]  = 'status = ?';
    $params[] = $statusFilter;
}
if ($searchQuery) {
    $where[]  = '(name LIKE ? OR email LIKE ? OR company LIKE ? OR message LIKE ?)';
    $s = "%$searchQuery%";
    $params = array_merge($params, [$s, $s, $s, $s]);
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = (int)$db->query("SELECT COUNT(*) FROM contacts $whereSQL")->fetchColumn();
$newTotal = (int)$db->query("SELECT COUNT(*) FROM contacts WHERE status='new'")->fetchColumn();
$lastPage = max(1, (int)ceil($total / $perPage));
$offset = ($page - 1) * $perPage;
$contacts = fetchAll("SELECT * FROM contacts $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);
?>

<!-- ===== PIPELINE ===== -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px;">
  <a href="<?= SITE_URL ?>/admin/contacts.php" class="pipe-card" style="padding:14px;border-radius:10px;text-align:center;border:2px solid <?= !$statusFilter?'#2563eb':'var(--adm-border)' ?>;background:var(--adm-surface);text-decoration:none;display:block;">
    <div style="font-size:22px;font-weight:800;color:var(--adm-text);"><?= $total ?></div>
    <div style="font-size:10px;font-weight:600;color:var(--adm-text-muted);text-transform:uppercase;letter-spacing:.04em;">All Messages</div>
  </a>
  <a href="<?= SITE_URL ?>/admin/contacts.php?status=new" class="pipe-card" style="padding:14px;border-radius:10px;text-align:center;border:2px solid <?= $statusFilter==='new'?'#f79009':'var(--adm-border)' ?>;background:var(--adm-surface);text-decoration:none;display:block;">
    <div style="font-size:18px;margin-bottom:2px;"><i class="fa-solid fa-envelope" style="color:#f79009;"></i></div>
    <div style="font-size:22px;font-weight:800;color:var(--adm-text);"><?= $newTotal ?></div>
    <div style="font-size:10px;font-weight:600;color:var(--adm-text-muted);text-transform:uppercase;">New</div>
  </a>
  <a href="<?= SITE_URL ?>/admin/contacts.php?status=read" class="pipe-card" style="padding:14px;border-radius:10px;text-align:center;border:2px solid <?= $statusFilter==='read'?'#344054':'var(--adm-border)' ?>;background:var(--adm-surface);text-decoration:none;display:block;">
    <div style="font-size:18px;margin-bottom:2px;"><i class="fa-solid fa-envelope-open" style="color:#344054;"></i></div>
    <div style="font-size:22px;font-weight:800;color:var(--adm-text);"><?= $total - $newTotal ?></div>
    <div style="font-size:10px;font-weight:600;color:var(--adm-text-muted);text-transform:uppercase;">Read</div>
  </a>
</div>

<!-- ===== TOOLBAR ===== -->
<div class="liquid-toolbar">
  <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
    <input type="text" name="search" class="form-control-admin" style="width:220px;font-size:13px;" placeholder="Search name, email, company, message..." value="<?= e($searchQuery) ?>">
    <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= e($statusFilter) ?>"><?php endif; ?>
    <button type="submit" class="btn-secondary-admin" style="font-size:12px;padding:7px 14px;"><i class="fa-solid fa-search"></i> Search</button>
    <?php if ($searchQuery || $statusFilter): ?>
    <a href="<?= SITE_URL ?>/admin/contacts.php" class="btn-ghost-admin" style="font-size:12px;">Clear</a>
    <?php endif; ?>
  </form>
  <span class="liquid-pill"><i class="fa-solid fa-inbox me-1"></i> <?= $total ?> messages</span>
</div>

<div class="card-box p-0">
<table class="admin-table">
  <thead>
    <tr>
      <th>Name / Email</th>
      <th>Company</th>
      <th>Message</th>
      <th>Date</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($contacts as $c): ?>
    <tr>
      <td class="cell-title">
        <?= e($c['name']) ?>
        <div style="font-size:11px;font-weight:400;color:var(--adm-text-muted);"><?= e($c['email']) ?></div>
      </td>
      <td style="font-size:12px;"><?= e($c['company'] ?: '—') ?></td>
      <td style="max-width:300px;font-size:12px;color:var(--adm-text-secondary);">
        <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($c['message']) ?></div>
      </td>
      <td style="font-size:12px;white-space:nowrap;"><?= date('d M Y H:i', strtotime($c['created_at'])) ?></td>
      <td>
        <?php if ($c['status'] === 'new'): ?>
        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:#fffaeb;color:#f79009;border:1px solid #f7900933;">
          <i class="fa-solid fa-envelope" style="font-size:10px;"></i> New
        </span>
        <?php else: ?>
        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:#f2f4f7;color:#344054;border:1px solid #34405433;">
          <i class="fa-solid fa-envelope-open" style="font-size:10px;"></i> Read
        </span>
        <?php endif; ?>
      </td>
      <td>
        <div class="actions-cell">
          <button class="btn-edit-admin" style="font-size:11px;padding:5px 10px;border:none;cursor:pointer;" onclick="openContact(<?= $c['id'] ?>)">View</button>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="id" value="<?= $c['id'] ?>">
            <?php if ($c['status'] === 'new'): ?>
            <input type="hidden" name="action" value="mark_read">
            <button type="submit" class="btn-secondary-admin" style="font-size:11px;padding:5px 10px;" title="Mark as read"><i class="fa-solid fa-check"></i></button>
            <?php else: ?>
            <input type="hidden" name="action" value="mark_new">
            <button type="submit" class="btn-secondary-admin" style="font-size:11px;padding:5px 10px;" title="Mark as new"><i class="fa-solid fa-envelope"></i></button>
            <?php endif; ?>
          </form>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this message permanently?')">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="id" value="<?= $c['id'] ?>">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn-edit-admin" style="font-size:11px;padding:5px 10px;color:#b42318;border:none;cursor:pointer;" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
          </form>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (empty($contacts)): ?>
    <tr><td colspan="6" style="text-align:center;padding:48px;color:var(--adm-text-muted);font-size:14px;">
      <div style="font-size:40px;margin-bottom:12px;opacity:0.3;"><i class="fa-solid fa-envelope-open-text"></i></div>
      No contact messages yet. Submissions from the website <strong>/contact</strong> form will appear here.
    </td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<?php if ($lastPage > 1): ?>
<div class="admin-pagination">
  <?php
  $qparams = $_GET;
  unset($qparams['page']);
  $qs = http_build_query($qparams);
  for ($i=1; $i<=$lastPage; $i++):
    $url = '?' . ($qs ? $qs . '&' : '') . 'page=' . $i;
  ?>
  <a href="<?= $url ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<!-- ===== VIEW MODAL ===== -->
<div id="contactModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:999;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this)closeContact()">
  <div style="background:#fff;border:1px solid #e4e7ec;border-radius:14px;padding:24px;max-width:560px;width:100%;max-height:85vh;overflow-y:auto;box-shadow:0 12px 40px rgba(16,24,40,.2);">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
      <h3 style="margin:0;font-size:16px;color:#101828;">Message Details</h3>
      <button onclick="closeContact()" style="background:none;border:none;font-size:18px;color:#667085;cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div id="contactModalBody"></div>
  </div>
</div>

<script>
const contactData = <?= json_encode($contacts) ?>;
function openContact(id) {
  const c = contactData.find(x => parseInt(x.id) === id);
  if (!c) return;
  const esc = s => (s === null || s === undefined) ? '—' : String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  document.getElementById('contactModalBody').innerHTML = `
    <dl style="margin:0;font-size:13px;">
      <dt style="color:#667085;font-weight:600;margin-top:12px;">Name</dt><dd style="margin:4px 0 0;">${esc(c.name)}</dd>
      <dt style="color:#667085;font-weight:600;margin-top:12px;">Email</dt><dd style="margin:4px 0 0;"><a href="mailto:${esc(c.email)}">${esc(c.email)}</a></dd>
      <dt style="color:#667085;font-weight:600;margin-top:12px;">Company</dt><dd style="margin:4px 0 0;">${esc(c.company)}</dd>
      <dt style="color:#667085;font-weight:600;margin-top:12px;">Website</dt><dd style="margin:4px 0 0;">${esc(c.website)}</dd>
      <dt style="color:#667085;font-weight:600;margin-top:12px;">Received</dt><dd style="margin:4px 0 0;">${esc(c.created_at)}</dd>
      <dt style="color:#667085;font-weight:600;margin-top:16px;">Message</dt>
      <dd style="margin:8px 0 0;padding:14px;background:#f9fafb;border:1px solid #e4e7ec;border-radius:8px;white-space:pre-wrap;line-height:1.6;color:#344054;">${esc(c.message)}</dd>
    </dl>
    <div style="display:flex;gap:8px;margin-top:20px;justify-content:flex-end;">
      <a href="mailto:${esc(c.email)}" class="btn-primary-admin" style="padding:8px 16px;text-decoration:none;"><i class="fa-solid fa-reply"></i> Reply</a>
      <button onclick="closeContact()" class="btn-secondary-admin" style="padding:8px 16px;">Close</button>
    </div>`;
  document.getElementById('contactModal').style.display = 'flex';
}
function closeContact() {
  document.getElementById('contactModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
