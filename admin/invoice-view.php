<?php
$id = (int)($_GET['id'] ?? 0);
$isPrint = isset($_GET['print']);

// ── Load dependencies (header isn't included yet) ──
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// ── Load data (shared between print and normal) ──
$db = getDB();
$invRows = fetchAll("SELECT i.*, c.name as company_name, c.address as company_address, c.phone as company_phone, c.email as company_email, c.website as company_website, c.tax_id as company_tax_id, c.currency as company_currency
    FROM invoices i LEFT JOIN companies c ON i.company_id=c.id WHERE i.id=? LIMIT 1", [$id]);
$inv = $invRows[0] ?? null;
$items = $inv ? fetchAll("SELECT * FROM invoice_items WHERE invoice_id=? ORDER BY sort_order", [$id]) : [];

// ── Handle POST (only for normal view) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isPrint) {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'update_status') {
        $ns = $_POST['new_status'] ?? '';
        if (in_array($ns, ['draft','sent','paid','overdue','cancelled'])) {
            $db->prepare("UPDATE invoices SET status=? WHERE id=?")->execute([$ns, $id]);
            setFlash('success', "Invoice status updated.");
        }
        redirect(SITE_URL . '/admin/invoice-view.php?id=' . $id);
    }
    if ($action === 'delete') {
        $db->prepare("DELETE FROM invoices WHERE id=?")->execute([$id]);
        setFlash('success', 'Invoice deleted.');
        redirect(SITE_URL . '/admin/invoices.php');
    }
}

// ── Render ──
if ($isPrint):
    if (!$id || !$inv) { echo '<h2 style="text-align:center;margin-top:80px;color:#b42318;">Invoice not found.</h2>'; exit; }
    $pageTitle = 'Invoice';
    require_once __DIR__ . '/includes/header.php';
?>
<style>
body.admin-liquid { background:#fff; }
.admin-sidebar, .admin-topbar, .detail-back, .no-print { display:none !important; }
.admin-main { margin-left:0; }
.admin-body { padding:0; max-width:100%; }
</style>
<div style="max-width:800px;margin:40px auto;padding:40px 50px;background:#fff;font-family:'Inter',sans-serif;">
<?php else:
    if (!$id || !$inv) { setFlash('error', 'Invoice not found.'); redirect(SITE_URL . '/admin/invoices.php'); }
    $pageTitle = 'Invoice';
    require_once __DIR__ . '/includes/header.php';
?>
<a href="<?= SITE_URL ?>/admin/invoices.php" class="detail-back"><i class="fa-solid fa-arrow-left"></i> Back to Invoices</a>
<div class="card-box p-0"><div style="padding:30px 35px;">
<?php endif; ?>

<!-- Invoice Header -->
<table width="100%" cellpadding="0" cellspacing="0" style="border-bottom:2px solid #2563eb;padding-bottom:20px;margin-bottom:24px;">
  <tr>
    <td>
      <h1 style="margin:0;font-size:26px;font-weight:800;color:#1a1a1a;"><?= e($inv['company_name'] ?? 'VMS Go Vista') ?></h1>
      <p style="margin:8px 0 0;font-size:12px;color:#666;line-height:1.6;">
        <?= nl2br(e($inv['company_address'] ?? '')) ?><br>
        <?= e($inv['company_phone'] ?? '') ?> · <?= e($inv['company_email'] ?? '') ?><br>
        Tax ID: <?= e($inv['company_tax_id'] ?? '—') ?>
      </p>
    </td>
    <td style="text-align:right;vertical-align:top;">
      <h2 style="margin:0;font-size:32px;font-weight:200;color:#2563eb;letter-spacing:-2px;">INVOICE</h2>
      <p style="margin:8px 0 0;font-size:14px;font-weight:700;color:#1a1a1a;"><?= e($inv['invoice_number']) ?></p>
    </td>
  </tr>
</table>

<!-- Customer & Dates -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
  <tr>
    <td style="vertical-align:top;">
      <h4 style="margin:0 0 8px;font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:1px;">Bill To</h4>
      <p style="margin:0;font-size:14px;font-weight:600;color:#1a1a1a;"><?= e($inv['customer_name']) ?></p>
      <p style="margin:4px 0 0;font-size:12px;color:#666;">
        <?= e($inv['customer_email'] ?? '') ?><br>
        <?= e($inv['customer_phone'] ?? '') ?><br>
        <?= e($inv['customer_address'] ?? '') ?>
      </p>
    </td>
    <td style="text-align:right;vertical-align:top;">
      <table cellpadding="0" cellspacing="0" style="font-size:13px;">
        <tr><td style="color:#888;padding-right:16px;">Invoice Date:</td><td style="font-weight:600;"><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td></tr>
        <tr><td style="color:#888;padding-right:16px;">Due Date:</td><td style="font-weight:600;"><?= $inv['due_date'] ? date('d M Y', strtotime($inv['due_date'])) : '—' ?></td></tr>
        <tr><td style="color:#888;padding-right:16px;">Status:</td><td><span style="display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:600;background:<?= $inv['status']==='paid'?'#ecfdf3':($inv['status']==='overdue'?'#fef3f2':'#f2f4f7') ?>;color:<?= $inv['status']==='paid'?'#027a48':($inv['status']==='overdue'?'#b42318':'#344054') ?>;"><?= ucfirst($inv['status']) ?></span></td></tr>
      </table>
    </td>
  </tr>
</table>

<!-- Items Table -->
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:24px;">
  <thead>
    <tr style="background:#f8fafc;border-top:1px solid #e2e8f0;border-bottom:2px solid #e2e8f0;">
      <th style="padding:12px 14px;text-align:left;font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:0.5px;">#</th>
      <th style="padding:12px 14px;text-align:left;font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Description</th>
      <th style="padding:12px 14px;text-align:center;font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Qty</th>
      <th style="padding:12px 14px;text-align:right;font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Unit Price</th>
      <th style="padding:12px 14px;text-align:right;font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Total</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($items as $idx => $item): ?>
    <tr style="border-bottom:1px solid #f1f5f9;">
      <td style="padding:14px;font-size:13px;color:#94a3b8;width:30px;"><?= $idx + 1 ?></td>
      <td style="padding:14px;font-size:13px;font-weight:500;color:#1a1a1a;"><?= e($item['description']) ?></td>
      <td style="padding:14px;text-align:center;font-size:13px;color:#64748b;"><?= (float)$item['quantity'] ?></td>
      <td style="padding:14px;text-align:right;font-size:13px;color:#64748b;">$<?= number_format((float)$item['unit_price'], 2) ?></td>
      <td style="padding:14px;text-align:right;font-size:13px;font-weight:600;color:#1a1a1a;">$<?= number_format((float)$item['total'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Totals -->
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td style="width:60%;vertical-align:bottom;">
      <?php if ($inv['notes']): ?>
      <div style="padding:14px 16px;background:#f8fafc;border-radius:8px;font-size:12px;color:#64748b;line-height:1.6;">
        <strong style="color:#1a1a1a;">Notes:</strong><br><?= nl2br(e($inv['notes'])) ?>
      </div>
      <?php endif; ?>
    </td>
    <td style="width:40%;padding-left:24px;vertical-align:bottom;">
      <table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;">
        <tr><td style="color:#64748b;padding:6px 0;">Subtotal:</td><td style="text-align:right;font-weight:500;">$<?= number_format((float)$inv['subtotal'], 2) ?></td></tr>
        <?php if ((float)$inv['tax_rate'] > 0): ?>
        <tr><td style="color:#64748b;padding:6px 0;">Tax (<?= (float)$inv['tax_rate'] ?>%):</td><td style="text-align:right;font-weight:500;">$<?= number_format((float)$inv['tax_amount'], 2) ?></td></tr>
        <?php endif; ?>
        <?php if ((float)$inv['discount'] > 0): ?>
        <tr><td style="color:#64748b;padding:6px 0;">Discount:</td><td style="text-align:right;font-weight:500;color:#b42318;">-$<?= number_format((float)$inv['discount'], 2) ?></td></tr>
        <?php endif; ?>
        <tr style="border-top:2px solid #1a1a1a;">
          <td style="padding:10px 0 0;font-size:15px;font-weight:700;color:#1a1a1a;">Total:</td>
          <td style="text-align:right;padding:10px 0 0;font-size:18px;font-weight:800;color:#1a1a1a;">$<?= number_format((float)$inv['total'], 2) ?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<div style="margin-top:40px;padding-top:16px;border-top:1px solid #e2e8f0;font-size:11px;color:#94a3b8;text-align:center;">
  <?= e($inv['company_website'] ?? '') ?> · Thank you for your business!
</div>

<?php if ($isPrint): ?>
<script>window.onload = function() { window.print(); };</script>
<?php endif; ?>

<?php if (!$isPrint): ?>
</div></div>

<div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap;" class="no-print">
  <a href="<?= SITE_URL ?>/admin/invoice-view.php?id=<?= $id ?>&print=1" target="_blank" class="btn-primary-admin"><i class="fa-solid fa-print"></i> Print / PDF</a>

  <form method="POST" style="display:inline-flex;gap:6px;align-items:center;padding:10px 16px;background:#f9fafb;border:1px solid var(--adm-border);border-radius:10px;">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="update_status">
    <select name="new_status" class="form-control-admin" style="width:auto;font-size:12px;padding:6px 10px;">
      <option value="draft" <?= $inv['status']==='draft'?'selected':'' ?>>Draft</option>
      <option value="sent" <?= $inv['status']==='sent'?'selected':'' ?>>Sent</option>
      <option value="paid" <?= $inv['status']==='paid'?'selected':'' ?>>Paid</option>
      <option value="overdue" <?= $inv['status']==='overdue'?'selected':'' ?>>Overdue</option>
      <option value="cancelled" <?= $inv['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
    </select>
    <button type="submit" class="btn-secondary-admin" style="font-size:12px;padding:6px 12px;">Update Status</button>
  </form>

  <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this invoice?')">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="delete">
    <button type="submit" class="btn-danger-admin" style="font-size:12px;padding:6px 12px;"><i class="fa-solid fa-trash"></i> Delete</button>
  </form>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
