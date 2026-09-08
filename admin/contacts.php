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

    // Delete ALL contacts
    if ($action === 'delete_all') {
        $db->exec("DELETE FROM contacts");
        setFlash('success', 'All contact messages have been permanently deleted.');
        redirect(SITE_URL . '/admin/contacts.php');
    }

    // Bulk delete
    if ($action === 'bulk_delete' && isset($_POST['selected'])) {
        $ids = array_map('intval', $_POST['selected']);
        if ($ids) {
            $phs = implode(',', array_fill(0, count($ids), '?'));
            $db->prepare("DELETE FROM contacts WHERE id IN ($phs)")->execute($ids);
            setFlash('success', count($ids) . ' message(s) deleted.');
        }
        redirect(SITE_URL . '/admin/contacts.php');
    }

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
  <div style="display:flex;align-items:center;gap:8px;">
    <span class="liquid-pill"><i class="fa-solid fa-inbox me-1"></i> <?= $total ?> messages</span>
    <?php if ($total > 0): ?>
    <button type="button"
      onclick="confirmDeleteAll()"
      style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;font-size:12px;font-weight:600;background:#b42318;color:#fff;border:1px solid #b42318;border-radius:8px;cursor:pointer;white-space:nowrap;">
      <i class="fa-solid fa-trash-can"></i> Delete All
    </button>
    <?php endif; ?>
  </div>
</div>

<!-- Hidden Delete All form -->
<form id="deleteAllForm" method="POST" style="display:none;">
  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
  <input type="hidden" name="action" value="delete_all">
</form>

<!-- Bulk actions form -->
<form method="POST" id="bulkForm">
<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
<input type="hidden" name="action" value="bulk_delete">

<div class="card-box p-0">

<!-- ===== BULK DELETE BAR ===== -->
<div id="bulkBar" style="display:none;padding:10px 16px;background:#fef3f2;border-bottom:1px solid #fecdca;display:flex;align-items:center;justify-content:space-between;">
  <span style="font-size:13px;font-weight:600;color:#b42318;"><i class="fa-solid fa-trash-can"></i> <span id="bulkCount">0</span> message(s) selected</span>
  <form method="POST" id="bulkForm" onsubmit="return confirm('Delete selected messages permanently?')">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="bulk_delete">
    <div id="bulkIds"></div>
    <button type="submit" style="background:#b42318;color:#fff;border:none;padding:7px 16px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;"><i class="fa-solid fa-trash-can"></i> Delete Selected</button>
  </form>
</div>

<table class="admin-table">
  <thead>
    <tr>
      <th width="30"><input type="checkbox" id="selectAll" style="accent-color:#2563eb;"></th>
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
      <td><input type="checkbox" name="selected[]" value="<?= $c['id'] ?>" class="row-check" style="accent-color:#2563eb;"></td>
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
    <tr><td colspan="7" style="text-align:center;padding:48px;color:var(--adm-text-muted);font-size:14px;">
      <div style="font-size:40px;margin-bottom:12px;opacity:0.3;"><i class="fa-solid fa-envelope-open-text"></i></div>
      No contact messages yet. Submissions from the website <strong>/contact</strong> form will appear here.
    </td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<?php if (!empty($contacts)): ?>
<div style="display:flex;align-items:center;gap:12px;margin-top:16px;padding:12px 16px;background:#f9fafb;border:1px solid var(--adm-border);border-radius:10px;">
  <span style="font-size:13px;font-weight:600;color:var(--adm-text);" id="selCount">0 selected</span>
  <button type="submit" class="btn-secondary-admin" style="font-size:12px;padding:6px 14px;background:#b42318;color:#fff;border-color:#b42318;" onclick="return confirm('Delete selected messages permanently?')"><i class="fa-solid fa-trash-can"></i> Delete Selected</button>
</div>
<?php endif; ?>
</form>

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
// ── Bulk Select / Delete ──
(function() {
  const selectAll = document.getElementById('selectAll');
  const rowCbs = document.querySelectorAll('.row-cb');
  const bulkBar = document.getElementById('bulkBar');
  const bulkCount = document.getElementById('bulkCount');
  const bulkIds = document.getElementById('bulkIds');

  function updateBulk() {
    const checked = document.querySelectorAll('.row-cb:checked');
    const count = checked.length;
    bulkBar.style.display = count > 0 ? 'flex' : 'none';
    bulkCount.textContent = count;
    bulkIds.innerHTML = '';
    checked.forEach(cb => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = cb.value;
      bulkIds.appendChild(input);
    });
    selectAll.checked = count === rowCbs.length && count > 0;
    selectAll.indeterminate = count > 0 && count < rowCbs.length;
  }

  selectAll.addEventListener('change', function() {
    rowCbs.forEach(cb => { cb.checked = selectAll.checked; });
    updateBulk();
  });

  rowCbs.forEach(cb => { cb.addEventListener('change', updateBulk); });
})();

const contactData = <?= json_encode($contacts) ?>;
function openContact(id) {
  const c = contactData.find(x => parseInt(x.id) === id);
  if (!c) return;
  const esc = s => (s === null || s === undefined) ? '—' : String(s).replace(/[&<>"']/g, m => ({'&':'&','<':'<','>':'>','"':'"',"'":'''}[m]));
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
      <button onclick="deleteFromModal(${parseInt(c.id)})" class="btn-secondary-admin" style="padding:8px 16px;background:#b42318;color:#fff;border-color:#b42318;"><i class="fa-solid fa-trash-can"></i> Delete</button>
      <button onclick="closeContact()" class="btn-secondary-admin" style="padding:8px 16px;">Close</button>
    </div>`;
  document.getElementById('contactModal').style.display = 'flex';
}
function closeContact() {
  document.getElementById('contactModal').style.display = 'none';
}

function deleteFromModal(id) {
  if (!confirm('Delete this message permanently?')) return;
  const form = document.createElement('form');
  form.method = 'POST';
  form.innerHTML = `
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" value="${id}">`;
  document.body.appendChild(form);
  form.submit();
}

// Bulk select all
document.getElementById('selectAll')?.addEventListener('change', function() {
  document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked); updateCount();
});
document.querySelectorAll('.row-check').forEach(c => c.addEventListener('change', updateCount));
function updateCount() {
  document.getElementById('selCount').textContent = document.querySelectorAll('.row-check:checked').length + ' selected';
}

// Delete All confirmation
function confirmDeleteAll() {
  // Custom confirm modal
  const overlay = document.createElement('div');
  overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;';
  overlay.innerHTML = `
    <div style="background:#fff;border-radius:14px;padding:28px 28px 24px;max-width:420px;width:100%;box-shadow:0 16px 48px rgba(16,24,40,.25);text-align:center;">
      <div style="width:56px;height:56px;background:#fef3f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
        <i class="fa-solid fa-triangle-exclamation" style="font-size:24px;color:#b42318;"></i>
      </div>
      <h3 style="margin:0 0 8px;font-size:17px;font-weight:700;color:#101828;">Delete All Messages?</h3>
      <p style="margin:0 0 24px;font-size:13px;color:#667085;line-height:1.6;">
        This will permanently delete <strong>all <?= $total ?> contact message(s)</strong> from the database.<br>
        <span style="color:#b42318;font-weight:600;">This action cannot be undone.</span>
      </p>
      <div style="display:flex;gap:10px;justify-content:center;">
        <button onclick="this.closest('div[style*=fixed]').remove()"
          style="flex:1;max-width:140px;padding:10px 18px;font-size:13px;font-weight:600;background:#fff;color:#344054;border:1px solid #d0d5dd;border-radius:8px;cursor:pointer;">
          Cancel
        </button>
        <button onclick="document.getElementById('deleteAllForm').submit()"
          style="flex:1;max-width:140px;padding:10px 18px;font-size:13px;font-weight:600;background:#b42318;color:#fff;border:1px solid #b42318;border-radius:8px;cursor:pointer;">
          <i class="fa-solid fa-trash-can"></i> Yes, Delete All
        </button>
      </div>
    </div>`;
  document.body.appendChild(overlay);
  overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
