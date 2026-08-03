<?php
$pageTitle = 'Billing – Create Invoice';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$company = fetchAll("SELECT * FROM companies WHERE id=1 LIMIT 1");
$company = $company[0] ?? ['name'=>'VMS Go Vista','address'=>'','phone'=>'','email'=>'','website'=>'','tax_id'=>'','currency'=>'USD'];

$leadId = (int)($_GET['lead_id'] ?? 0);
$prefill = ['first_name'=>'','last_name'=>'','email'=>'','phone'=>'','country'=>'','address'=>''];
if ($leadId) {
    $ld = fetchAll("SELECT * FROM leads WHERE id=? LIMIT 1", [$leadId]);
    if (!empty($ld[0])) {
        $prefill = [
            'first_name' => $ld[0]['first_name'],
            'last_name'  => $ld[0]['last_name'],
            'email'      => $ld[0]['email'],
            'phone'      => $ld[0]['phone'] ?? '',
            'country'    => $ld[0]['country'] ?? '',
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    try {
        // Generate invoice number
        $year = date('Y');
        $count = (int)$db->query("SELECT COUNT(*)+1 FROM invoices WHERE invoice_number LIKE 'INV-$year-%'")->fetchColumn();
        $invNum = "INV-$year-" . str_pad($count, 4, '0', STR_PAD_LEFT);

        $customerName = trim($_POST['customer_name'] ?? '');
        $items_desc   = $_POST['item_desc']   ?? [];
        $items_qty    = $_POST['item_qty']    ?? [];
        $items_price  = $_POST['item_price']  ?? [];
        $taxRate      = (float)($_POST['tax_rate'] ?? 0);
        $discount     = (float)($_POST['discount'] ?? 0);

        if (!$customerName) throw new Exception('Customer name is required.');

        $subtotal = 0;
        $itemRows = [];
        foreach ($items_desc as $i => $desc) {
            if (!trim($desc)) continue;
            $qty  = max(1, (float)($items_qty[$i] ?? 1));
            $price = max(0, (float)($items_price[$i] ?? 0));
            $total = round($qty * $price, 2);
            $subtotal += $total;
            $itemRows[] = ['desc' => trim($desc), 'qty' => $qty, 'price' => $price, 'total' => $total];
        }
        if (empty($itemRows)) throw new Exception('At least one item is required.');

        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $grandTotal = round($subtotal + $taxAmount - $discount, 2);
        $dueDate = $_POST['due_date'] ?: date('Y-m-d', strtotime('+30 days'));

        $db->beginTransaction();
        $db->prepare("INSERT INTO invoices (invoice_number, lead_id, company_id, customer_name, customer_email, customer_phone, customer_address, invoice_date, due_date, subtotal, tax_rate, tax_amount, discount, total, currency, status, notes, created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
           ->execute([
               $invNum, $leadId ?: null, 1, $customerName,
               $_POST['customer_email'] ?? null, $_POST['customer_phone'] ?? null, $_POST['customer_address'] ?? null,
               $_POST['invoice_date'] ?: date('Y-m-d'), $dueDate,
               $subtotal, $taxRate, $taxAmount, $discount, $grandTotal,
               $company['currency'] ?? 'USD', 'draft', $_POST['notes'] ?? null, $adminUser['name'] ?? 'Admin'
           ]);
        $invId = (int)$db->lastInsertId();

        $stmt = $db->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total, sort_order) VALUES (?,?,?,?,?,?)");
        foreach ($itemRows as $idx => $ir) {
            $stmt->execute([$invId, $ir['desc'], $ir['qty'], $ir['price'], $ir['total'], $idx]);
        }
        $db->commit();

        setFlash('success', "Invoice <strong>$invNum</strong> created! <a href='" . SITE_URL . "/admin/invoice-view.php?id=$invId' style='color:#fff;text-decoration:underline;'>View Invoice →</a>");
        redirect(SITE_URL . '/admin/invoices.php');
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        setFlash('error', $e->getMessage());
    }
}
?>

<a href="<?= SITE_URL ?>/admin/invoices.php" class="detail-back"><i class="fa-solid fa-arrow-left"></i> Back to Invoices</a>

<div class="card-box" style="max-width:900px;">
  <h3 style="margin:0 0 20px;font-size:18px;">Create New Invoice</h3>

  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <!-- Company Header -->
    <div class="form-section" style="background:#f0f4ff;border-color:#c7d7fe;">
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <div>
          <h4 style="margin:0;font-size:16px;font-weight:800;color:var(--adm-text);"><?= e($company['name']) ?></h4>
          <p style="margin:6px 0 0;font-size:12px;color:var(--adm-text-muted);max-width:400px;line-height:1.5;">
            <?= e($company['address'] ?? '') ?><br>
            <?= e($company['phone'] ?? '') ?> · <?= e($company['email'] ?? '') ?>
          </p>
          <p style="margin:4px 0 0;font-size:11px;color:var(--adm-text-muted);">Tax ID: <?= e($company['tax_id'] ?? '') ?></p>
        </div>
        <div style="text-align:right;">
          <div style="font-size:24px;font-weight:200;color:var(--adm-accent);letter-spacing:-1px;">INVOICE</div>
        </div>
      </div>
    </div>

    <!-- Customer -->
    <div class="form-section">
      <div class="form-section-title"><i class="fa-solid fa-user me-2"></i>Bill To</div>
      <div class="form-row" style="margin-bottom:8px;">
        <div class="form-group" style="margin-bottom:0;">
          <label>Customer Name <span class="req">*</span></label>
          <input type="text" name="customer_name" class="form-control-admin" value="<?= e($prefill['first_name'] . ' ' . $prefill['last_name']) ?>" required>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label>Email</label>
          <input type="email" name="customer_email" class="form-control-admin" value="<?= e($prefill['email']) ?>">
        </div>
      </div>
      <div class="form-row" style="margin-bottom:8px;">
        <div class="form-group" style="margin-bottom:0;">
          <label>Phone</label>
          <input type="text" name="customer_phone" class="form-control-admin" value="<?= e($prefill['phone']) ?>">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label>Customer Address</label>
          <input type="text" name="customer_address" class="form-control-admin" value="<?= e($prefill['country']) ?>">
        </div>
      </div>
    </div>

    <!-- Invoice Dates -->
    <div class="form-section">
      <div class="form-section-title"><i class="fa-solid fa-calendar me-2"></i>Invoice Dates</div>
      <div class="form-row" style="margin-bottom:0;">
        <div class="form-group" style="margin-bottom:0;">
          <label>Invoice Date</label>
          <input type="date" name="invoice_date" class="form-control-admin" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label>Due Date</label>
          <input type="date" name="due_date" class="form-control-admin" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
        </div>
      </div>
    </div>

    <!-- Line Items -->
    <div class="form-section">
      <div class="form-section-title"><i class="fa-solid fa-list me-2"></i>Invoice Items</div>
      <div id="items-wrapper">
        <div class="repeater-item" style="padding:14px;">
          <div class="form-row" style="margin-bottom:8px;">
            <div class="form-group" style="margin-bottom:0;grid-column:span 2;">
              <label>Description</label>
              <input type="text" name="item_desc[]" class="form-control-admin" placeholder="e.g. Bali Luxury Package - 5 Days" style="font-size:13px;">
            </div>
          </div>
          <div class="form-row" style="margin-bottom:0;">
            <div class="form-group" style="margin-bottom:0;">
              <label>Qty</label>
              <input type="number" name="item_qty[]" class="form-control-admin" value="1" min="1" step="1" style="font-size:13px;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label>Unit Price ($)</label>
              <input type="number" name="item_price[]" class="form-control-admin" value="0" step="0.01" min="0" style="font-size:13px;">
            </div>
          </div>
        </div>
      </div>
      <template id="items-wrapper-template">
        <div class="repeater-item" style="padding:14px;">
          <button type="button" class="remove-btn" style="top:6px;right:6px;padding:3px 8px;font-size:10px;">Remove</button>
          <div class="form-row" style="margin-bottom:8px;">
            <div class="form-group" style="margin-bottom:0;grid-column:span 2;">
              <label>Description</label>
              <input type="text" name="item_desc[]" class="form-control-admin" placeholder="e.g. Hotel Accommodation" style="font-size:13px;">
            </div>
          </div>
          <div class="form-row" style="margin-bottom:0;">
            <div class="form-group" style="margin-bottom:0;">
              <label>Qty</label>
              <input type="number" name="item_qty[]" class="form-control-admin" value="1" min="1" step="1" style="font-size:13px;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label>Unit Price ($)</label>
              <input type="number" name="item_price[]" class="form-control-admin" value="0" step="0.01" min="0" style="font-size:13px;">
            </div>
          </div>
        </div>
      </template>
      <button type="button" class="add-repeater-btn" data-target="items-wrapper" style="margin-top:8px;font-size:12px;">
        <i class="fa-solid fa-plus"></i> Add Item
      </button>
    </div>

    <!-- Totals -->
    <div class="form-section">
      <div class="form-section-title"><i class="fa-solid fa-calculator me-2"></i>Totals</div>
      <div class="form-row" style="margin-bottom:12px;">
        <div class="form-group" style="margin-bottom:0;">
          <label>Tax Rate (%)</label>
          <input type="number" name="tax_rate" class="form-control-admin" value="0" step="0.01" min="0" max="100" style="font-size:13px;">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label>Discount ($)</label>
          <input type="number" name="discount" class="form-control-admin" value="0" step="0.01" min="0" style="font-size:13px;">
        </div>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label>Notes / Terms</label>
        <textarea name="notes" class="form-control-admin" rows="3" placeholder="Payment terms, bank details, thank you note..." style="font-size:13px;"></textarea>
      </div>
    </div>

    <div class="pkg-form-submit" style="position:static;margin-top:16px;">
      <button type="submit" class="btn-primary-admin" style="padding:12px 28px;"><i class="fa-solid fa-cloud-arrow-up"></i> Create Invoice</button>
      <a href="<?= SITE_URL ?>/admin/invoices.php" class="btn-secondary-admin" style="padding:11px 20px;">Cancel</a>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.add-repeater-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var target = btn.dataset.target;
            var wrapper = document.getElementById(target);
            var tpl = document.getElementById(target + '-template');
            if (!wrapper || !tpl) return;
            var div = document.createElement('div');
            div.className = 'repeater-item';
            div.innerHTML = tpl.innerHTML;
            wrapper.appendChild(div);
        });
    });
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-btn')) {
            if (confirm('Remove this item?')) e.target.closest('.repeater-item').remove();
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
