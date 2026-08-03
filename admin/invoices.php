<?php
$pageTitle = 'Billing – Invoices';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$statusFilter = $_GET['status'] ?? '';
$searchQuery  = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = []; $params = [];
if ($statusFilter) { $where[] = 'i.status = ?'; $params[] = $statusFilter; }
if ($searchQuery) {
    $where[] = '(i.invoice_number LIKE ? OR i.customer_name LIKE ?)';
    $s = "%$searchQuery%"; $params = array_merge($params, [$s, $s]);
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = (int)$db->query("SELECT COUNT(*) FROM invoices i $whereSQL")->fetchColumn();
$lastPage = max(1, (int)ceil($total / $perPage));
$offset = ($page - 1) * $perPage;
$invoices = fetchAll("SELECT i.*, c.name as company_name FROM invoices i LEFT JOIN companies c ON i.company_id=c.id $whereSQL ORDER BY i.created_at DESC LIMIT $perPage OFFSET $offset", $params);
$stats = [
    'total' => (int)$db->query("SELECT COUNT(*) FROM invoices")->fetchColumn(),
    'paid'  => (int)$db->query("SELECT COUNT(*) FROM invoices WHERE status='paid'")->fetchColumn(),
    'draft' => (int)$db->query("SELECT COUNT(*) FROM invoices WHERE status='draft'")->fetchColumn(),
    'total_revenue' => (float)$db->query("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status='paid'")->fetchColumn(),
];
?>
<div class="liquid-toolbar">
  <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
    <input type="text" name="search" class="form-control-admin" style="width:200px;font-size:13px;" placeholder="Search invoices..." value="<?= e($searchQuery) ?>">
    <select name="status" class="form-control-admin" style="width:auto;font-size:13px;">
      <option value="">All Status</option>
      <option value="draft" <?= $statusFilter==='draft'?'selected':'' ?>>Draft</option>
      <option value="sent" <?= $statusFilter==='sent'?'selected':'' ?>>Sent</option>
      <option value="paid" <?= $statusFilter==='paid'?'selected':'' ?>>Paid</option>
      <option value="overdue" <?= $statusFilter==='overdue'?'selected':'' ?>>Overdue</option>
      <option value="cancelled" <?= $statusFilter==='cancelled'?'selected':'' ?>>Cancelled</option>
    </select>
    <button type="submit" class="btn-secondary-admin" style="font-size:12px;"><i class="fa-solid fa-filter"></i></button>
    <?php if ($searchQuery || $statusFilter): ?><a href="<?= SITE_URL ?>/admin/invoices.php" class="btn-ghost-admin" style="font-size:12px;">Clear</a><?php endif; ?>
  </form>
  <a href="<?= SITE_URL ?>/admin/invoice-create.php" class="btn-primary-admin" style="font-size:12px;"><i class="fa-solid fa-plus"></i> New Invoice</a>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
  <div style="padding:16px;background:var(--adm-surface);border:1px solid var(--adm-border);border-radius:10px;">
    <div style="font-size:22px;font-weight:800;"><?= $stats['total'] ?></div>
    <div style="font-size:12px;color:var(--adm-text-muted);">Total Invoices</div>
  </div>
  <div style="padding:16px;background:var(--adm-surface);border:1px solid var(--adm-border);border-radius:10px;">
    <div style="font-size:22px;font-weight:800;"><?= $stats['paid'] ?></div>
    <div style="font-size:12px;color:var(--adm-text-muted);">Paid</div>
  </div>
  <div style="padding:16px;background:var(--adm-surface);border:1px solid var(--adm-border);border-radius:10px;">
    <div style="font-size:22px;font-weight:800;">$<?= number_format($stats['total_revenue'], 0) ?></div>
    <div style="font-size:12px;color:var(--adm-text-muted);">Total Revenue</div>
  </div>
  <div style="padding:16px;background:var(--adm-surface);border:1px solid var(--adm-border);border-radius:10px;">
    <div style="font-size:22px;font-weight:800;"><?= $stats['draft'] ?></div>
    <div style="font-size:12px;color:var(--adm-text-muted);">Awaiting Send</div>
  </div>
</div>

<div class="card-box p-0">
<table class="admin-table">
  <thead>
    <tr><th>Invoice #</th><th>Customer</th><th>Company</th><th>Date</th><th>Due</th><th>Total</th><th>Status</th><th>Actions</th></tr>
  </thead>
  <tbody>
  <?php foreach ($invoices as $inv): ?>
    <tr>
      <td class="cell-title"><?= e($inv['invoice_number']) ?></td>
      <td><?= e($inv['customer_name']) ?></td>
      <td style="font-size:12px;"><?= e($inv['company_name'] ?? '—') ?></td>
      <td style="font-size:12px;"><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
      <td style="font-size:12px;"><?= $inv['due_date'] ? date('d M Y', strtotime($inv['due_date'])) : '—' ?></td>
      <td style="font-weight:700;">$<?= number_format((float)$inv['total'], 2) ?></td>
      <td><span class="<?= $inv['status']==='paid'?'badge-pub':($inv['status']==='overdue'?'badge-arc':'badge-draft') ?>"><?= ucfirst($inv['status']) ?></span></td>
      <td>
        <a href="<?= SITE_URL ?>/admin/invoice-view.php?id=<?= $inv['id'] ?>" class="btn-edit-admin" style="font-size:11px;padding:4px 8px;">View</a>
        <a href="<?= SITE_URL ?>/admin/invoice-view.php?id=<?= $inv['id'] ?>&print=1" target="_blank" class="btn-secondary-admin" style="font-size:11px;padding:4px 8px;"><i class="fa-solid fa-print"></i></a>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (empty($invoices)): ?>
    <tr><td colspan="8" style="text-align:center;padding:48px;color:var(--adm-text-muted);font-size:14px;">
      <div style="font-size:40px;margin-bottom:12px;opacity:0.3;"><i class="fa-solid fa-file-invoice"></i></div>
      No invoices yet. <a href="<?= SITE_URL ?>/admin/invoice-create.php">Create your first invoice →</a>
    </td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<?php if ($lastPage > 1): ?>
<div class="admin-pagination">
  <?php for ($i=1; $i<=$lastPage; $i++): ?>
    <a href="?page=<?= $i ?><?= $statusFilter ? '&status='.$statusFilter : '' ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
