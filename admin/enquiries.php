<?php
$pageTitle = 'CRM – Leads Pipeline';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// ── Handle actions ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    // Mark with custom status
    if (isset($_POST['quick_status'])) {
        $qId = (int)($_POST['enquiry_id'] ?? 0);
        $qSt = $_POST['quick_status'] ?? '';
        $valid = ['new','read','contacted','qualified','proposal_sent','negotiation','converted','lost'];
        if ($qId && in_array($qSt, $valid)) {
            $old = $db->query("SELECT status FROM enquiries WHERE id=$qId")->fetchColumn() ?: 'new';
            $db->prepare("UPDATE enquiries SET status=? WHERE id=?")->execute([$qSt, $qId]);
            $db->prepare("INSERT INTO enquiry_status_history (enquiry_id, old_status, new_status, changed_by) VALUES (?,?,?,?)")
               ->execute([$qId, $old, $qSt, $adminUser['name'] ?? 'Admin']);
            setFlash('success', "Status updated.");
        }
        redirect(SITE_URL . '/admin/enquiries.php');
    }
    // Bulk action
    if (isset($_POST['bulk_action']) && isset($_POST['selected'])) {
        $ids = array_map('intval', $_POST['selected']);
        $ba = $_POST['bulk_action'];
        if ($ba === 'delete') {
            $phs = implode(',', array_fill(0, count($ids), '?'));
            $db->prepare("DELETE FROM enquiries WHERE id IN ($phs)")->execute($ids);
            setFlash('success', count($ids) . ' enquiries deleted.');
        } elseif (in_array($ba, ['new','read','contacted','qualified','proposal_sent','negotiation','converted','lost'])) {
            $phs = implode(',', array_fill(0, count($ids), '?'));
            $db->prepare("UPDATE enquiries SET status=? WHERE id IN ($phs)")->execute(array_merge([$ba], $ids));
            foreach ($ids as $bid) {
                $old = $db->query("SELECT status FROM enquiries WHERE id=$bid")->fetchColumn() ?: 'new';
                $db->prepare("INSERT INTO enquiry_status_history (enquiry_id, old_status, new_status, changed_by) VALUES (?,?,?,?)")
                   ->execute([$bid, $old, $ba, $adminUser['name'] ?? 'Admin']);
            }
            setFlash('success', count($ids) . ' enquiries updated to ' . $ba . '.');
        }
        redirect(SITE_URL . '/admin/enquiries.php');
    }
}

// ── Filters ───────────────────────────────────────────
$statusFilter = $_GET['status'] ?? '';
$searchQuery  = trim($_GET['search'] ?? '');
$dateFrom     = trim($_GET['from'] ?? '');
$dateTo       = trim($_GET['to'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 25;

$where  = [];
$params = [];

if ($statusFilter) {
    $where[]  = 'status = ?';
    $params[] = $statusFilter;
}
if ($searchQuery) {
    $where[]  = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR package_title LIKE ? OR phone LIKE ?)';
    $s = "%$searchQuery%";
    $params = array_merge($params, [$s, $s, $s, $s, $s]);
}
if ($dateFrom) {
    $where[]  = 'created_at >= ?';
    $params[] = $dateFrom . ' 00:00:00';
}
if ($dateTo) {
    $where[]  = 'created_at <= ?';
    $params[] = $dateTo . ' 23:59:59';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ── Stats for pipeline ───────────────────────────────
$statuses = ['new','read','contacted','qualified','proposal_sent','negotiation','converted','lost'];
$stats = [];
$totalCount = 0;
foreach ($statuses as $st) {
    $c = (int)$db->query("SELECT COUNT(*) FROM enquiries WHERE status='$st'")->fetchColumn();
    $stats[$st] = $c;
    $totalCount += $c;
}

// ── Fetch enquiries ──────────────────────────────────
$total = (int)$db->query("SELECT COUNT(*) FROM enquiries $whereSQL")->fetchColumn();
$lastPage = max(1, (int)ceil($total / $perPage));
$offset = ($page - 1) * $perPage;
$enquiries = fetchAll("SELECT * FROM enquiries $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);

// Status config for display
$sc = [
    'new'           => ['label'=>'New','color'=>'#f79009','bg'=>'#fffaeb','icon'=>'fa-star'],
    'read'          => ['label'=>'Read','color'=>'#344054','bg'=>'#f2f4f7','icon'=>'fa-envelope-open'],
    'contacted'     => ['label'=>'Contacted','color'=>'#175cd3','bg'=>'#eff4ff','icon'=>'fa-phone'],
    'qualified'     => ['label'=>'Qualified','color'=>'#3538cd','bg'=>'#eef0ff','icon'=>'fa-check-circle'],
    'proposal_sent' => ['label'=>'Proposal Sent','color'=>'#9b5de5','bg'=>'#f5efff','icon'=>'fa-file-lines'],
    'negotiation'   => ['label'=>'Negotiation','color'=>'#e07c00','bg'=>'#fff3e0','icon'=>'fa-handshake'],
    'converted'     => ['label'=>'Converted','color'=>'#027a48','bg'=>'#ecfdf3','icon'=>'fa-trophy'],
    'lost'          => ['label'=>'Lost','color'=>'#b42318','bg'=>'#fef3f2','icon'=>'fa-ban'],
];
?>

<style>
/* Pipeline cards */
.crm-pipeline { display: grid; grid-template-columns: repeat(8, 1fr); gap: 10px; margin-bottom: 24px; }
@media (max-width:1200px) { .crm-pipeline { grid-template-columns: repeat(4, 1fr); } }
@media (max-width:640px) { .crm-pipeline { grid-template-columns: repeat(2, 1fr); } }
.pipe-card {
  padding: 14px 12px; border-radius: 10px; text-align: center;
  border: 1px solid var(--adm-border); background: var(--adm-surface);
  cursor: pointer; transition: all 0.15s; text-decoration: none; display: block;
}
.pipe-card:hover { box-shadow: var(--adm-shadow); transform: translateY(-1px); }
.pipe-card.active { border-width: 2px; }
.pipe-card .pc-icon { font-size: 18px; margin-bottom: 4px; }
.pipe-card .pc-count { font-size: 22px; font-weight: 800; color: var(--adm-text); line-height: 1.2; }
.pipe-card .pc-label { font-size: 10px; font-weight: 600; color: var(--adm-text-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-top: 2px; }
</style>

<!-- ===== PIPELINE ===== -->
<div class="crm-pipeline">
  <a href="<?= SITE_URL ?>/admin/enquiries.php" class="pipe-card <?= !$statusFilter?'active':'' ?>" style="border-color:<?= !$statusFilter?'#2563eb':'var(--adm-border)' ?>;">
    <div class="pc-count"><?= $totalCount ?></div>
    <div class="pc-label">All Leads</div>
  </a>
  <?php foreach ($statuses as $st): $c = $sc[$st]; ?>
  <a href="<?= SITE_URL ?>/admin/enquiries.php?status=<?= $st ?>" class="pipe-card <?= $statusFilter===$st?'active':'' ?>" style="border-color:<?= $statusFilter===$st?$c['color'].'66':'var(--adm-border)' ?>;">
    <div class="pc-icon"><i class="fa-solid <?= $c['icon'] ?>" style="color:<?= $c['color'] ?>;"></i></div>
    <div class="pc-count"><?= $stats[$st] ?? 0 ?></div>
    <div class="pc-label"><?= $c['label'] ?></div>
  </a>
  <?php endforeach; ?>
</div>

<!-- ===== TOOLBAR ===== -->
<div class="liquid-toolbar">
  <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
    <input type="text" name="search" class="form-control-admin" style="width:200px;font-size:13px;" placeholder="Search name, email, phone..." value="<?= e($searchQuery) ?>">
    <input type="date" name="from" class="form-control-admin" style="width:150px;font-size:13px;" value="<?= e($dateFrom) ?>" title="From date">
    <input type="date" name="to" class="form-control-admin" style="width:150px;font-size:13px;" value="<?= e($dateTo) ?>" title="To date">
    <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= e($statusFilter) ?>"><?php endif; ?>
    <button type="submit" class="btn-secondary-admin" style="font-size:12px;padding:7px 14px;"><i class="fa-solid fa-filter"></i> Filter</button>
    <?php if ($searchQuery || $dateFrom || $dateTo || $statusFilter): ?>
    <a href="<?= SITE_URL ?>/admin/enquiries.php" class="btn-ghost-admin" style="font-size:12px;">Clear</a>
    <?php endif; ?>
  </form>
  <span class="liquid-pill"><?= $total ?> leads</span>
</div>

<!-- Bulk actions form -->
<form method="POST" id="bulkForm">
<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

<div class="card-box p-0">
<table class="admin-table">
  <thead>
    <tr>
      <th width="30"><input type="checkbox" id="selectAll" style="accent-color:#2563eb;"></th>
      <th>Name / Email</th>
      <th>Phone</th>
      <th>Package</th>
      <th>People</th>
      <th>Travelling Date</th>
      <th>Received</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($enquiries as $e): $st = $sc[$e['status']] ?? $sc['new']; ?>
    <tr>
      <td><input type="checkbox" name="selected[]" value="<?= $e['id'] ?>" class="row-check" style="accent-color:#2563eb;"></td>
      <td class="cell-title">
        <?= e($e['first_name'].' '.$e['last_name']) ?>
        <div style="font-size:11px;font-weight:400;color:var(--adm-text-muted);"><?= e($e['email']) ?></div>
      </td>
      <td><?= e($e['phone'] ?: '—') ?></td>
      <td style="max-width:140px;font-size:12px;"><?= e($e['package_title'] ?: 'General') ?></td>
      <td style="font-size:12px;"><?= (int)($e['adults']??0) ?>A / <?= (int)($e['children']??0) ?>C</td>
      <?php
        $tDate = !empty($e['travel_date']) ? date('d M Y', strtotime($e['travel_date'])) : '';
      ?>
      <td style="font-size:12px;white-space:nowrap;">
        <?php if ($tDate): ?>
          <span style="color:var(--adm-text-strong,#1a2433);font-weight:600;"><i class="fa-solid fa-calendar-days" style="color:#0f6a94;margin-right:5px;"></i><?= $tDate ?></span>
        <?php else: ?>
          <span style="color:var(--adm-text-muted);">—</span>
        <?php endif; ?>
      </td>
      <td style="font-size:12px;white-space:nowrap;color:var(--adm-text-muted);">
        <i class="fa-solid fa-inbox" style="color:#8a94a6;margin-right:5px;"></i><?= date('d M Y', strtotime($e['created_at'])) ?>
      </td>
      <td>
        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:<?= $st['bg'] ?>;color:<?= $st['color'] ?>;border:1px solid <?= $st['color'] ?>33;">
          <i class="fa-solid <?= $st['icon'] ?>" style="font-size:10px;"></i> <?= $st['label'] ?>
        </span>
      </td>
      <td>
        <div class="actions-cell">
          <a href="<?= SITE_URL ?>/admin/enquiry-view.php?id=<?= (int)$e['id'] ?>" class="btn-edit-admin" style="font-size:11px;padding:5px 10px;">Open</a>
          <!-- Move to Lead -->
          <form method="POST" action="<?= SITE_URL ?>/admin/leads.php" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="move_to_lead">
            <input type="hidden" name="enquiry_id" value="<?= $e['id'] ?>">
            <button type="submit" class="btn-secondary-admin" style="font-size:10px;padding:4px 8px;background:var(--adm-success);color:#fff;border-color:var(--adm-success);white-space:nowrap;">
              <i class="fa-solid fa-arrow-right-to-bracket"></i> Lead
            </button>
          </form>
          <select onchange="if(this.value){var f=document.createElement('form');f.method='POST';f.innerHTML='<?= addslashes('<input type=hidden name=csrf_token value='.csrfToken().'><input type=hidden name=enquiry_id value='.(int)$e['id'].'><input type=hidden name=quick_status value=') ?>'+this.value+'<?= addslashes('>') ?>';document.body.appendChild(f);f.submit();}" style="font-size:11px;padding:4px 6px;border-radius:6px;border:1px solid var(--adm-border);background:#fff;cursor:pointer;">
            <option value="">More...</option>
            <option value="contacted">Contacted</option>
            <option value="qualified">Qualified</option>
            <option value="proposal_sent">Proposal</option>
            <option value="negotiation">Negotiation</option>
            <option value="converted">✅ Converted</option>
            <option value="lost">❌ Lost</option>
          </select>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (empty($enquiries)): ?>
    <tr><td colspan="9" style="text-align:center;padding:48px;color:var(--adm-text-muted);font-size:14px;">
      <div style="font-size:40px;margin-bottom:12px;opacity:0.3;"><i class="fa-solid fa-inbox"></i></div>
      No leads found matching your criteria.
    </td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<!-- Bulk actions bar -->
<?php if (!empty($enquiries)): ?>
<div style="display:flex;align-items:center;gap:12px;margin-top:16px;padding:12px 16px;background:#f9fafb;border:1px solid var(--adm-border);border-radius:10px;">
  <span style="font-size:13px;font-weight:600;color:var(--adm-text);" id="selCount">0 selected</span>
  <select name="bulk_action" class="form-control-admin" style="width:auto;font-size:12px;padding:6px 10px;">
    <option value="">Bulk action...</option>
    <option value="read">Mark as Read</option>
    <option value="contacted">Mark Contacted</option>
    <option value="qualified">Mark Qualified</option>
    <option value="proposal_sent">Mark Proposal Sent</option>
    <option value="negotiation">Mark Negotiation</option>
    <option value="converted">Mark Converted</option>
    <option value="lost">Mark Lost</option>
    <option value="delete" style="color:#b42318;">Delete Selected</option>
  </select>
  <button type="submit" class="btn-secondary-admin" style="font-size:12px;padding:6px 14px;">Apply</button>
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

<script>
// Select all checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
  document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
  updateCount();
});
document.querySelectorAll('.row-check').forEach(c => c.addEventListener('change', updateCount));
function updateCount() {
  var n = document.querySelectorAll('.row-check:checked').length;
  document.getElementById('selCount').textContent = n + ' selected';
}
document.getElementById('bulkForm')?.addEventListener('submit', function(e) {
  if (this.querySelector('[name=bulk_action]').value === 'delete') {
    if (!confirm('Delete selected enquiries permanently?')) e.preventDefault();
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
