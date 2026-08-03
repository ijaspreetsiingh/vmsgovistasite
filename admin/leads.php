<?php
$pageTitle = 'CRM – Leads Pipeline';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// ── Handle actions ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    // Move enquiry to lead
    if ($action === 'move_to_lead' && isset($_POST['enquiry_id'])) {
        $eid = (int)$_POST['enquiry_id'];
        $enq = fetchAll("SELECT * FROM enquiries WHERE id=? LIMIT 1", [$eid]);
        if (!empty($enq[0])) {
            $e = $enq[0];
            $db->prepare("INSERT INTO leads (enquiry_id, first_name, last_name, email, phone, country, source, package_interest, pax_adults, pax_children, status, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,'new',NOW())")
               ->execute([$eid, $e['first_name'], $e['last_name'], $e['email'], $e['phone'], $e['country'], 'website', $e['package_title'], $e['adults'], $e['children']]);
            $leadId = (int)$db->lastInsertId();
            // Auto-note
            $db->prepare("INSERT INTO lead_notes (lead_id, note, note_type, created_by) VALUES (?,?,'status_change',?)")
               ->execute([$leadId, 'Lead created from website enquiry #' . $eid, $adminUser['name'] ?? 'Admin']);
            // Mark enquiry as converted
            $db->prepare("UPDATE enquiries SET status='converted' WHERE id=?")->execute([$eid]);
            setFlash('success', 'Enquiry moved to <strong>Leads</strong>! <a href="' . SITE_URL . '/admin/lead-view.php?id=' . $leadId . '" style="color:#fff;text-decoration:underline;">View Lead →</a>');
        }
        redirect(SITE_URL . '/admin/leads.php');
    }

    // Quick status update
    if ($action === 'quick_status' && isset($_POST['lead_id'])) {
        $lid = (int)$_POST['lead_id'];
        $ns  = $_POST['new_status'] ?? '';
        $valid = ['new','contacted','qualified','proposal','negotiation','won','lost'];
        if ($lid && in_array($ns, $valid)) {
            $old = $db->query("SELECT status FROM leads WHERE id=$lid")->fetchColumn() ?: 'new';
            $db->prepare("UPDATE leads SET status=? WHERE id=?")->execute([$ns, $lid]);
            $db->prepare("INSERT INTO lead_status_history (lead_id, old_status, new_status, changed_by) VALUES (?,?,?,?)")
               ->execute([$lid, $old, $ns, $adminUser['name'] ?? 'Admin']);
            setFlash('success', 'Lead status updated.');
        }
        redirect(SITE_URL . '/admin/leads.php');
    }

    // Bulk action
    if ($action === 'bulk' && isset($_POST['selected'])) {
        $ids = array_map('intval', $_POST['selected']);
        $ba  = $_POST['bulk_action'] ?? '';
        if ($ba === 'delete') {
            $phs = implode(',', array_fill(0, count($ids), '?'));
            $db->prepare("DELETE FROM leads WHERE id IN ($phs)")->execute($ids);
            setFlash('success', count($ids) . ' leads deleted.');
        } elseif (in_array($ba, ['new','contacted','qualified','proposal','negotiation','won','lost'])) {
            $phs = implode(',', array_fill(0, count($ids), '?'));
            foreach ($ids as $bid) {
                $old = $db->query("SELECT status FROM leads WHERE id=$bid")->fetchColumn() ?: 'new';
                $db->prepare("UPDATE leads SET status=? WHERE id=?")->execute([$ba, $bid]);
                $db->prepare("INSERT INTO lead_status_history (lead_id, old_status, new_status, changed_by) VALUES (?,?,?,?)")
                   ->execute([$bid, $old, $ba, $adminUser['name'] ?? 'Admin']);
            }
            setFlash('success', count($ids) . ' leads updated.');
        }
        redirect(SITE_URL . '/admin/leads.php');
    }
}

// ── Filters ───────────────────────────────────────────
$statusFilter = $_GET['status'] ?? '';
$searchQuery  = trim($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 25;

$where  = [];
$params = [];
if ($statusFilter) { $where[] = 'status = ?'; $params[] = $statusFilter; }
if ($searchQuery) {
    $where[] = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ? OR company_name LIKE ?)';
    $s = "%$searchQuery%";
    $params = array_merge($params, [$s, $s, $s, $s, $s]);
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Pipeline stats
$statuses = ['new','contacted','qualified','proposal','negotiation','won','lost'];
$stats = []; $totalLeads = 0;
foreach ($statuses as $st) {
    $c = (int)$db->query("SELECT COUNT(*) FROM leads WHERE status='$st'")->fetchColumn();
    $stats[$st] = $c; $totalLeads += $c;
}

$total = (int)$db->query("SELECT COUNT(*) FROM leads $whereSQL")->fetchColumn();
$lastPage = max(1, (int)ceil($total / $perPage));
$offset = ($page - 1) * $perPage;
$leads = fetchAll("SELECT * FROM leads $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);

// Unmoved enquiries (for Move to Lead)
$unmovedEnquiries = fetchAll("SELECT e.id, e.first_name, e.last_name, e.email, e.package_title, e.created_at
    FROM enquiries e LEFT JOIN leads l ON e.id = l.enquiry_id
    WHERE l.id IS NULL AND e.status != 'converted'
    ORDER BY e.created_at DESC LIMIT 5");

$sc = [
    'new'         => ['label'=>'New','color'=>'#f79009','bg'=>'#fffaeb','icon'=>'fa-star'],
    'contacted'   => ['label'=>'Contacted','color'=>'#175cd3','bg'=>'#eff4ff','icon'=>'fa-phone'],
    'qualified'   => ['label'=>'Qualified','color'=>'#3538cd','bg'=>'#eef0ff','icon'=>'fa-check-circle'],
    'proposal'    => ['label'=>'Proposal','color'=>'#9b5de5','bg'=>'#f5efff','icon'=>'fa-file-lines'],
    'negotiation' => ['label'=>'Negotiation','color'=>'#e07c00','bg'=>'#fff3e0','icon'=>'fa-handshake'],
    'won'         => ['label'=>'Won','color'=>'#027a48','bg'=>'#ecfdf3','icon'=>'fa-trophy'],
    'lost'        => ['label'=>'Lost','color'=>'#b42318','bg'=>'#fef3f2','icon'=>'fa-ban'],
];
?>

<!-- Pipeline -->
<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:8px;margin-bottom:20px;">
  <a href="<?= SITE_URL ?>/admin/leads.php" class="pipe-card" style="padding:12px;border-radius:10px;text-align:center;border:2px solid <?= !$statusFilter?'#2563eb':'var(--adm-border)' ?>;background:var(--adm-surface);text-decoration:none;display:block;">
    <div style="font-size:20px;font-weight:800;color:var(--adm-text);"><?= $totalLeads ?></div>
    <div style="font-size:10px;font-weight:600;color:var(--adm-text-muted);text-transform:uppercase;letter-spacing:0.04em;">All</div>
  </a>
  <?php foreach ($statuses as $st): $c = $sc[$st]; ?>
  <a href="<?= SITE_URL ?>/admin/leads.php?status=<?= $st ?>" style="padding:12px;border-radius:10px;text-align:center;border:2px solid <?= $statusFilter===$st?$c['color'].'66':'var(--adm-border)' ?>;background:var(--adm-surface);text-decoration:none;display:block;">
    <div style="font-size:18px;"><i class="fa-solid <?= $c['icon'] ?>" style="color:<?= $c['color'] ?>;"></i></div>
    <div style="font-size:18px;font-weight:800;color:var(--adm-text);"><?= $stats[$st] ?? 0 ?></div>
    <div style="font-size:10px;font-weight:600;color:var(--adm-text-muted);text-transform:uppercase;"><?= $c['label'] ?></div>
  </a>
  <?php endforeach; ?>
</div>

<!-- Toolbar -->
<div class="liquid-toolbar">
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
    <form method="GET" style="display:flex;gap:8px;align-items:center;">
      <input type="text" name="search" class="form-control-admin" style="width:200px;font-size:13px;" placeholder="Search leads..." value="<?= e($searchQuery) ?>">
      <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= e($statusFilter) ?>"><?php endif; ?>
      <button type="submit" class="btn-secondary-admin" style="font-size:12px;padding:7px 14px;"><i class="fa-solid fa-search"></i></button>
      <?php if ($searchQuery || $statusFilter): ?><a href="<?= SITE_URL ?>/admin/leads.php" class="btn-ghost-admin" style="font-size:12px;">Clear</a><?php endif; ?>
    </form>
  </div>
  <div style="display:flex;gap:8px;align-items:center;">
    <span class="liquid-pill"><?= $totalLeads ?> leads · <?= ($stats['won']??0) ?> won</span>
  </div>
</div>

<!-- Unmoved enquiries notice -->
<?php if (!empty($unmovedEnquiries)): ?>
<div class="card-box" style="margin-bottom:20px;border-color:#f7900944;background:#fffaeb;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
    <h4 style="margin:0;font-size:13px;font-weight:700;color:#b54708;"><i class="fa-solid fa-inbox me-1"></i> Pending Enquiries (not moved to CRM)</h4>
    <a href="<?= SITE_URL ?>/admin/enquiries.php" style="font-size:12px;">View All <i class="fa-solid fa-arrow-right"></i></a>
  </div>
  <div style="display:flex;flex-wrap:wrap;gap:8px;">
    <?php foreach ($unmovedEnquiries as $ue): ?>
    <form method="POST" style="display:inline-flex;align-items:center;gap:8px;padding:8px 12px;background:#fff;border:1px solid var(--adm-border);border-radius:8px;">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="action" value="move_to_lead">
      <input type="hidden" name="enquiry_id" value="<?= $ue['id'] ?>">
      <span style="font-size:12px;font-weight:600;"><?= e($ue['first_name'] . ' ' . $ue['last_name']) ?></span>
      <span style="font-size:11px;color:var(--adm-text-muted);"><?= e($ue['package_title'] ?: 'General') ?></span>
      <span style="font-size:10px;color:var(--adm-text-muted);"><?= date('d M', strtotime($ue['created_at'])) ?></span>
      <button type="submit" class="btn-primary-admin" style="font-size:11px;padding:5px 12px;background:var(--adm-success);">
        <i class="fa-solid fa-arrow-right"></i> Move to Lead
      </button>
    </form>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Leads Table -->
<form method="POST" id="bulkForm">
<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
<input type="hidden" name="action" value="bulk">
<div class="card-box p-0">
<table class="admin-table">
  <thead>
    <tr>
      <th width="30"><input type="checkbox" id="selectAll" style="accent-color:#2563eb;"></th>
      <th>Lead Name</th>
      <th>Contact</th>
      <th>Interest</th>
      <th>Pax</th>
      <th>Created</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($leads as $ld): $st = $sc[$ld['status']] ?? $sc['new']; ?>
    <tr>
      <td><input type="checkbox" name="selected[]" value="<?= $ld['id'] ?>" class="row-check" style="accent-color:#2563eb;"></td>
      <td class="cell-title"><?= e($ld['first_name'].' '.$ld['last_name']) ?>
        <?php if ($ld['company_name']): ?><div style="font-size:11px;font-weight:400;color:var(--adm-text-muted);"><?= e($ld['company_name']) ?></div><?php endif; ?>
      </td>
      <td style="font-size:12px;">
        <div><?= e($ld['email']) ?></div>
        <?php if ($ld['phone']): ?><div style="color:var(--adm-text-muted);"><?= e($ld['phone']) ?></div><?php endif; ?>
      </td>
      <td style="font-size:12px;max-width:120px;"><?= e($ld['package_interest'] ?: '—') ?></td>
      <td style="font-size:12px;"><?= (int)$ld['pax_adults'] ?>A<?= $ld['pax_children'] ? '/'.(int)$ld['pax_children'].'C' : '' ?></td>
      <td style="font-size:12px;white-space:nowrap;"><?= date('d M Y', strtotime($ld['created_at'])) ?></td>
      <td>
        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:<?= $st['bg'] ?>;color:<?= $st['color'] ?>;border:1px solid <?= $st['color'] ?>33;">
          <i class="fa-solid <?= $st['icon'] ?>"></i> <?= $st['label'] ?>
        </span>
      </td>
      <td>
        <div class="actions-cell">
          <a href="<?= SITE_URL ?>/admin/lead-view.php?id=<?= $ld['id'] ?>" class="btn-edit-admin" style="font-size:11px;padding:5px 10px;">Open CRM</a>
          <?php if ($ld['status'] !== 'won' && $ld['status'] !== 'lost'): ?>
          <a href="<?= SITE_URL ?>/admin/invoice-create.php?lead_id=<?= $ld['id'] ?>" class="btn-secondary-admin" style="font-size:11px;padding:5px 10px;" title="Create Invoice"><i class="fa-solid fa-file-invoice"></i></a>
          <?php endif; ?>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (empty($leads)): ?>
    <tr><td colspan="8" style="text-align:center;padding:48px;color:var(--adm-text-muted);font-size:14px;">
      <div style="font-size:40px;margin-bottom:12px;opacity:0.3;"><i class="fa-solid fa-users"></i></div>
      No leads found. Move enquiries to CRM to get started.
    </td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<?php if (!empty($leads)): ?>
<div style="display:flex;align-items:center;gap:12px;margin-top:16px;padding:12px 16px;background:#f9fafb;border:1px solid var(--adm-border);border-radius:10px;">
  <span style="font-size:13px;font-weight:600;" id="selCount">0 selected</span>
  <select name="bulk_action" class="form-control-admin" style="width:auto;font-size:12px;padding:6px 10px;">
    <option value="">Bulk action...</option>
    <option value="contacted">Mark Contacted</option>
    <option value="qualified">Mark Qualified</option>
    <option value="proposal">Mark Proposal</option>
    <option value="negotiation">Mark Negotiation</option>
    <option value="won">Mark Won</option>
    <option value="lost">Mark Lost</option>
    <option value="delete" style="color:#b42318;">Delete</option>
  </select>
  <button type="submit" class="btn-secondary-admin" style="font-size:12px;padding:6px 14px;">Apply</button>
</div>
<?php endif; ?>
</form>

<?php if ($lastPage > 1): ?>
<div class="admin-pagination">
  <?php for ($i=1; $i<=$lastPage; $i++):
    $q = $_GET; $q['page'] = $i; unset($q['_']); ?>
  <a href="?<?= http_build_query($q) ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
  document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked); updateCount();
});
document.querySelectorAll('.row-check').forEach(c => c.addEventListener('change', updateCount));
function updateCount() {
  document.getElementById('selCount').textContent = document.querySelectorAll('.row-check:checked').length + ' selected';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
