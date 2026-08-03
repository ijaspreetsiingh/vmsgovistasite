<?php
$pageTitle = 'CRM – Lead Details';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { setFlash('error', 'Invalid lead.'); redirect(SITE_URL . '/admin/leads.php'); }
$db = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $ns = $_POST['new_status'] ?? '';
        $valid = ['new','contacted','qualified','proposal','negotiation','won','lost'];
        if (in_array($ns, $valid)) {
            $old = $db->query("SELECT status FROM leads WHERE id=$id")->fetchColumn() ?: 'new';
            $db->prepare("UPDATE leads SET status=? WHERE id=?")->execute([$ns, $id]);
            $db->prepare("INSERT INTO lead_status_history (lead_id, old_status, new_status, changed_by, notes) VALUES (?,?,?,?,?)")
               ->execute([$id, $old, $ns, $adminUser['name'] ?? 'Admin', $_POST['status_note'] ?? '']);
            if ($note = trim($_POST['status_note'] ?? '')) {
                $db->prepare("INSERT INTO lead_notes (lead_id, note, note_type, created_by) VALUES (?,?,'status_change',?)")
                   ->execute([$id, "Status changed to {$ns}: {$note}", $adminUser['name'] ?? 'Admin']);
            }
            setFlash('success', "Status updated.");
        }
        redirect(SITE_URL . '/admin/lead-view.php?id=' . $id);
    }

    if ($action === 'add_note') {
        $note = trim($_POST['note'] ?? '');
        $type = $_POST['note_type'] ?? 'note';
        if ($note) {
            $db->prepare("INSERT INTO lead_notes (lead_id, note, note_type, created_by) VALUES (?,?,?,?)")
               ->execute([$id, $note, $type, $adminUser['name'] ?? 'Admin']);
            setFlash('success', 'Note added.');
        }
        redirect(SITE_URL . '/admin/lead-view.php?id=' . $id);
    }

    if ($action === 'update_lead') {
        $db->prepare("UPDATE leads SET company_name=?, phone=?, country=?, package_interest=?, budget_min=?, budget_max=?, travel_date=?, pax_adults=?, pax_children=?, assigned_to=?, notes=? WHERE id=?")
           ->execute([
               $_POST['company_name'] ?? null, $_POST['phone'] ?? null, $_POST['country'] ?? null,
               $_POST['package_interest'] ?? null,
               $_POST['budget_min'] !== '' ? (float)$_POST['budget_min'] : null,
               $_POST['budget_max'] !== '' ? (float)$_POST['budget_max'] : null,
               $_POST['travel_date'] ?: null,
               $_POST['pax_adults'] !== '' ? (int)$_POST['pax_adults'] : null,
               $_POST['pax_children'] !== '' ? (int)$_POST['pax_children'] : null,
               $_POST['assigned_to'] ?? null, $_POST['notes'] ?? null
           ]);
        setFlash('success', 'Lead updated.');
        redirect(SITE_URL . '/admin/lead-view.php?id=' . $id);
    }

    if ($action === 'mark_won') {
        $val = (float)($_POST['converted_value'] ?? 0);
        $now = date('Y-m-d H:i:s');
        $old = $db->query("SELECT status FROM leads WHERE id=$id")->fetchColumn() ?: 'new';
        $db->prepare("UPDATE leads SET status='won', converted_value=?, converted_at=? WHERE id=?")->execute([$val > 0 ? $val : null, $now, $id]);
        $db->prepare("INSERT INTO lead_status_history (lead_id, old_status, new_status, changed_by, notes) VALUES (?,?,?,?,?)")
           ->execute([$id, $old, 'won', $adminUser['name'] ?? 'Admin', 'Lead converted. Value: $' . number_format($val)]);
        $db->prepare("INSERT INTO lead_notes (lead_id, note, note_type, created_by) VALUES (?,?,'status_change',?)")
           ->execute([$id, "Lead WON! Value: \$" . number_format($val), $adminUser['name'] ?? 'Admin']);
        setFlash('success', 'Lead marked as <strong>Won</strong>!');
        redirect(SITE_URL . '/admin/lead-view.php?id=' . $id);
    }

    if ($action === 'mark_lost') {
        $reason = trim($_POST['lost_reason'] ?? '');
        $old = $db->query("SELECT status FROM leads WHERE id=$id")->fetchColumn() ?: 'new';
        $db->prepare("UPDATE leads SET status='lost' WHERE id=?")->execute([$id]);
        $db->prepare("INSERT INTO lead_status_history (lead_id, old_status, new_status, changed_by, notes) VALUES (?,?,?,?,?)")
           ->execute([$id, $old, 'lost', $adminUser['name'] ?? 'Admin', $reason]);
        $db->prepare("INSERT INTO lead_notes (lead_id, note, note_type, created_by) VALUES (?,?,'status_change',?)")
           ->execute([$id, "Lead lost. Reason: {$reason}", $adminUser['name'] ?? 'Admin']);
        setFlash('success', 'Lead marked as Lost.');
        redirect(SITE_URL . '/admin/lead-view.php?id=' . $id);
    }
}

// Load lead
$rows = fetchAll("SELECT * FROM leads WHERE id=? LIMIT 1", [$id]);
$lead = $rows[0] ?? null;
if (!$lead) { setFlash('error', 'Lead not found.'); redirect(SITE_URL . '/admin/leads.php'); }

$notes = fetchAll("SELECT * FROM lead_notes WHERE lead_id=? ORDER BY created_at DESC", [$id]);
$history = fetchAll("SELECT * FROM lead_status_history WHERE lead_id=? ORDER BY created_at DESC", [$id]);
$invoices = fetchAll("SELECT * FROM invoices WHERE lead_id=? ORDER BY created_at DESC", [$id]);

$initials = strtoupper(substr($lead['first_name'], 0, 1) . substr($lead['last_name'], 0, 1));
$convRate = 0;
$totalDealValue = 0;
$lastActivity = $lead['updated_at'];

$sc = [
    'new'         => ['label'=>'New','color'=>'#f79009','bg'=>'#fffaeb','icon'=>'fa-star'],
    'contacted'   => ['label'=>'Contacted','color'=>'#175cd3','bg'=>'#eff4ff','icon'=>'fa-phone'],
    'qualified'   => ['label'=>'Qualified','color'=>'#3538cd','bg'=>'#eef0ff','icon'=>'fa-check-circle'],
    'proposal'    => ['label'=>'Proposal','color'=>'#9b5de5','bg'=>'#f5efff','icon'=>'fa-file-lines'],
    'negotiation' => ['label'=>'Negotiation','color'=>'#e07c00','bg'=>'#fff3e0','icon'=>'fa-handshake'],
    'won'         => ['label'=>'Won','color'=>'#027a48','bg'=>'#ecfdf3','icon'=>'fa-trophy'],
    'lost'        => ['label'=>'Lost','color'=>'#b42318','bg'=>'#fef3f2','icon'=>'fa-ban'],
];
$s = $sc[$lead['status']] ?? $sc['new'];

$transitions = $sc;
unset($transitions[$lead['status']]);
?>

<a href="<?= SITE_URL ?>/admin/leads.php" class="detail-back"><i class="fa-solid fa-arrow-left"></i> Back to Leads</a>

<div class="card-box p-0">
  <div class="detail-header-row">
    <div style="display:flex;align-items:center;gap:16px;">
      <div style="width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;font-size:18px;font-weight:700;flex-shrink:0;"><?= e($initials) ?></div>
      <div>
        <h2><?= e($lead['first_name'] . ' ' . $lead['last_name']) ?></h2>
        <p class="meta">
          Lead #<?= $lead['id'] ?> · <?= e($lead['email']) ?> · Created <?= date('d M Y H:i', strtotime($lead['created_at'])) ?>
          <?php if ($lead['enquiry_id']): ?> · From Enquiry #<?= $lead['enquiry_id'] ?><?php endif; ?>
        </p>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:12px;">
      <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 16px;border-radius:999px;font-size:13px;font-weight:600;background:<?= $s['bg'] ?>;color:<?= $s['color'] ?>;border:1px solid <?= $s['color'] ?>33;">
        <i class="fa-solid <?= $s['icon'] ?>"></i> <?= $s['label'] ?>
      </span>
      <?php if ($lead['status'] === 'won' && $lead['converted_value'] > 0): ?>
      <span style="font-size:16px;font-weight:800;color:var(--adm-success);">$<?= number_format((float)$lead['converted_value'], 2) ?></span>
      <?php endif; ?>
    </div>
  </div>

  <div class="detail-grid" style="padding:24px;">
    <!-- LEFT -->
    <div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
        <div style="padding:14px;background:#f9fafb;border:1px solid var(--adm-border);border-radius:10px;text-align:center;">
          <div style="font-size:20px;font-weight:800;color:var(--adm-text);"><?= ceil((time()-strtotime($lead['created_at']))/86400) ?>d</div>
          <div style="font-size:11px;color:var(--adm-text-muted);">Age</div>
        </div>
        <div style="padding:14px;background:#f9fafb;border:1px solid var(--adm-border);border-radius:10px;text-align:center;">
          <div style="font-size:20px;font-weight:800;color:var(--adm-text);"><?= count($notes) ?></div>
          <div style="font-size:11px;color:var(--adm-text-muted);">Notes</div>
        </div>
        <div style="padding:14px;background:#f9fafb;border:1px solid var(--adm-border);border-radius:10px;text-align:center;">
          <div style="font-size:20px;font-weight:800;color:var(--adm-text);"><?= count($invoices) ?></div>
          <div style="font-size:11px;color:var(--adm-text-muted);">Invoices</div>
        </div>
      </div>

      <!-- Lead Details -->
      <div class="card-box" style="box-shadow:none;margin-bottom:20px;">
        <h4 style="margin:0 0 16px;font-size:14px;font-weight:700;">Lead Details</h4>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="action" value="update_lead">
          <div class="form-row" style="margin-bottom:12px;">
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Company</label>
              <input type="text" name="company_name" class="form-control-admin" value="<?= e($lead['company_name'] ?? '') ?>" style="font-size:13px;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Phone</label>
              <input type="text" name="phone" class="form-control-admin" value="<?= e($lead['phone'] ?? '') ?>" style="font-size:13px;">
            </div>
          </div>
          <div class="form-row" style="margin-bottom:12px;">
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Country</label>
              <input type="text" name="country" class="form-control-admin" value="<?= e($lead['country'] ?? '') ?>" style="font-size:13px;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Package Interest</label>
              <input type="text" name="package_interest" class="form-control-admin" value="<?= e($lead['package_interest'] ?? '') ?>" style="font-size:13px;">
            </div>
          </div>
          <div class="form-row" style="margin-bottom:12px;">
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Budget Min ($)</label>
              <input type="number" name="budget_min" class="form-control-admin" value="<?= e($lead['budget_min'] ?? '') ?>" step="0.01" style="font-size:13px;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Budget Max ($)</label>
              <input type="number" name="budget_max" class="form-control-admin" value="<?= e($lead['budget_max'] ?? '') ?>" step="0.01" style="font-size:13px;">
            </div>
          </div>
          <div class="form-row" style="margin-bottom:12px;">
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Travel Date</label>
              <input type="date" name="travel_date" class="form-control-admin" value="<?= e($lead['travel_date'] ?? '') ?>" style="font-size:13px;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Assigned To</label>
              <input type="text" name="assigned_to" class="form-control-admin" value="<?= e($lead['assigned_to'] ?? '') ?>" style="font-size:13px;" placeholder="e.g. Sales Team">
            </div>
          </div>
          <div class="form-row" style="margin-bottom:12px;">
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Adults</label>
              <input type="number" name="pax_adults" class="form-control-admin" value="<?= (int)($lead['pax_adults'] ?? 0) ?>" min="0" style="font-size:13px;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label style="font-size:12px;">Children</label>
              <input type="number" name="pax_children" class="form-control-admin" value="<?= (int)($lead['pax_children'] ?? 0) ?>" min="0" style="font-size:13px;">
            </div>
          </div>
          <div class="form-group" style="margin-bottom:12px;">
            <label style="font-size:12px;">Internal Notes</label>
            <textarea name="notes" class="form-control-admin" rows="3" style="font-size:13px;"><?= e($lead['notes'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn-secondary-admin" style="font-size:12px;"><i class="fa-solid fa-save"></i> Save Changes</button>
        </form>
      </div>

      <!-- Invoices -->
      <div class="card-box" style="box-shadow:none;margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
          <h4 style="margin:0;font-size:14px;font-weight:700;">Invoices</h4>
          <a href="<?= SITE_URL ?>/admin/invoice-create.php?lead_id=<?= $id ?>" class="btn-primary-admin" style="font-size:11px;padding:5px 12px;">
            <i class="fa-solid fa-plus"></i> New Invoice
          </a>
        </div>
        <?php if (!empty($invoices)): ?>
        <table class="admin-table" style="font-size:12px;">
          <thead><tr><th>#</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($invoices as $inv): ?>
            <tr>
              <td><?= e($inv['invoice_number']) ?></td>
              <td><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
              <td style="font-weight:700;">$<?= number_format((float)$inv['total'], 2) ?></td>
              <td><span class="<?= $inv['status']==='paid'?'badge-pub':($inv['status']==='overdue'?'badge-arc':'badge-draft') ?>"><?= ucfirst($inv['status']) ?></span></td>
              <td><a href="<?= SITE_URL ?>/admin/invoice-view.php?id=<?= $inv['id'] ?>" class="btn-edit-admin" style="font-size:11px;padding:4px 8px;">View</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <p style="color:var(--adm-text-muted);font-size:13px;">No invoices yet.</p>
        <?php endif; ?>
      </div>

      <!-- Timeline -->
      <div class="card-box" style="box-shadow:none;">
        <h4 style="margin:0 0 16px;font-size:14px;font-weight:700;">Activity Timeline</h4>
        <?php
        $timeline = [];
        foreach ($notes as $n) { $timeline[] = ['type'=>'note','data'=>$n,'date'=>$n['created_at']]; }
        foreach ($history as $h) { $timeline[] = ['type'=>'history','data'=>$h,'date'=>$h['created_at']]; }
        usort($timeline, fn($a,$b) => strtotime($b['date']) - strtotime($a['date']));
        ?>
        <?php if (empty($timeline)): ?><p style="color:var(--adm-text-muted);font-size:13px;">No activity yet.</p><?php endif; ?>
        <ul style="list-style:none;padding:0;margin:0;">
        <?php foreach ($timeline as $tl):
          if ($tl['type'] === 'note'): $n = $tl['data'];
            $iconMap = ['note'=>'fa-note-sticky','email'=>'fa-envelope','call'=>'fa-phone','meeting'=>'fa-handshake','status_change'=>'fa-arrow-right'];
        ?>
          <li style="position:relative;padding:0 0 20px 40px;border-left:2px solid var(--adm-border);margin-left:12px;">
            <div style="position:absolute;left:-9px;top:2px;width:16px;height:16px;border-radius:50%;background:#fff;border:3px solid #98a2b3;"></div>
            <div style="font-size:12px;font-weight:600;color:var(--adm-text);">
              <span style="display:inline-flex;align-items:center;gap:4px;padding:1px 8px;border-radius:999px;font-size:10px;font-weight:600;background:#f2f4f7;color:#344054;">
                <i class="fa-solid <?= $iconMap[$n['note_type']] ?? 'fa-circle' ?>"></i> <?= ucfirst($n['note_type']) ?>
              </span>
              <span style="color:var(--adm-text-muted);font-weight:400;margin-left:6px;">by <?= e($n['created_by'] ?? 'System') ?> · <?= date('d M H:i', strtotime($n['created_at'])) ?></span>
            </div>
            <div style="margin-top:4px;padding:10px 12px;background:#f9fafb;border:1px solid var(--adm-border);border-radius:8px;font-size:12px;color:var(--adm-text-secondary);white-space:pre-wrap;"><?= e($n['note']) ?></div>
          </li>
        <?php else: $h = $tl['data']; ?>
          <li style="position:relative;padding:0 0 20px 40px;border-left:2px solid var(--adm-border);margin-left:12px;">
            <div style="position:absolute;left:-9px;top:2px;width:16px;height:16px;border-radius:50%;background:#fff;border:3px solid #f79009;"></div>
            <div style="font-size:12px;font-weight:600;color:var(--adm-text);">
              Status changed
              <span style="color:var(--adm-text-muted);font-weight:400;margin-left:6px;">by <?= e($h['changed_by'] ?? 'System') ?> · <?= date('d M H:i', strtotime($h['created_at'])) ?></span>
            </div>
            <div style="margin-top:4px;font-size:12px;">
              <span style="display:inline-flex;align-items:center;gap:4px;padding:1px 8px;border-radius:999px;font-size:10px;font-weight:600;background:#f2f4f7;color:#344054;"><?= ucfirst($h['old_status'] ?? '—') ?></span>
              <i class="fa-solid fa-arrow-right" style="margin:0 6px;color:var(--adm-text-muted);font-size:10px;"></i>
              <span style="display:inline-flex;align-items:center;gap:4px;padding:1px 8px;border-radius:999px;font-size:10px;font-weight:600;background:#eef0ff;color:#3538cd;"><?= ucfirst($h['new_status']) ?></span>
              <?php if ($h['notes']): ?><div style="margin-top:4px;color:var(--adm-text-muted);font-size:11px;"><?= e($h['notes']) ?></div><?php endif; ?>
            </div>
          </li>
        <?php endif; endforeach; ?>
        </ul>
      </div>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="detail-sidebar">
      <!-- Quick Actions -->
      <div class="card-box" style="margin-bottom:16px;">
        <h4 style="margin:0 0 14px;font-size:13px;font-weight:700;">Quick Actions</h4>
        <div class="action-stack">
          <a href="mailto:<?= e($lead['email']) ?>" class="btn-primary-admin" style="justify-content:center;"><i class="fa-solid fa-reply"></i> Send Email</a>
          <?php if ($lead['phone']): ?>
          <a href="tel:<?= e($lead['phone']) ?>" class="btn-secondary-admin" style="justify-content:center;"><i class="fa-solid fa-phone"></i> Call</a>
          <a href="https://wa.me/<?= preg_replace('/\D/','',$lead['phone']) ?>?text=Hi%20<?= urlencode($lead['first_name']) ?>" target="_blank" class="btn-secondary-admin" style="justify-content:center;color:#25D366;border-color:#25D36644;"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
          <?php endif; ?>
          <a href="<?= SITE_URL ?>/admin/invoice-create.php?lead_id=<?= $id ?>" class="btn-secondary-admin" style="justify-content:center;"><i class="fa-solid fa-file-invoice"></i> Create Invoice</a>
        </div>
      </div>

      <!-- Status Update -->
      <div class="card-box" style="margin-bottom:16px;">
        <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;">Update Status</h4>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="action" value="update_status">
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:6px;">
          <?php foreach ($transitions as $val => $c): ?>
            <label style="padding:8px;border-radius:8px;border:2px solid var(--adm-border);cursor:pointer;text-align:center;background:#fff;">
              <input type="radio" name="new_status" value="<?= $val ?>" style="position:absolute;opacity:0;">
              <div style="font-size:16px;"><i class="fa-solid <?= $c['icon'] ?>" style="color:<?= $c['color'] ?>;"></i></div>
              <div style="font-size:10px;font-weight:600;color:var(--adm-text);margin-top:2px;"><?= $c['label'] ?></div>
            </label>
          <?php endforeach; ?>
          </div>
          <input type="text" name="status_note" class="form-control-admin" style="margin-top:10px;font-size:12px;" placeholder="Note (optional)">
          <button type="submit" class="btn-primary-admin" style="width:100%;justify-content:center;margin-top:8px;font-size:12px;padding:8px;"><i class="fa-solid fa-arrow-right"></i> Update</button>
        </form>
      </div>

      <!-- Mark Won -->
      <?php if ($lead['status'] !== 'won' && $lead['status'] !== 'lost'): ?>
      <div class="card-box" style="margin-bottom:16px;border-color:#abefc6;background:var(--adm-success-bg);">
        <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;color:#027a48;"><i class="fa-solid fa-trophy me-1"></i> Close Won</h4>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="action" value="mark_won">
          <input type="number" name="converted_value" class="form-control-admin" step="0.01" min="0" placeholder="Deal value ($)" style="font-size:12px;margin-bottom:8px;">
          <button type="submit" class="btn-primary-admin" style="width:100%;justify-content:center;background:var(--adm-success);font-size:12px;" onclick="return confirm('Confirm this deal as WON?')"><i class="fa-solid fa-check-circle"></i> Mark Won</button>
        </form>
      </div>

      <!-- Mark Lost -->
      <div class="card-box" style="margin-bottom:16px;border-color:#fecdca;background:var(--adm-danger-bg);">
        <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;color:#b42318;"><i class="fa-solid fa-ban me-1"></i> Close Lost</h4>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="action" value="mark_lost">
          <input type="text" name="lost_reason" class="form-control-admin" placeholder="Reason (e.g. Budget, Competitor)" style="font-size:12px;margin-bottom:8px;">
          <button type="submit" class="btn-primary-admin" style="width:100%;justify-content:center;background:var(--adm-danger);font-size:12px;" onclick="return confirm('Mark this lead as Lost?')"><i class="fa-solid fa-ban"></i> Mark Lost</button>
        </form>
      </div>
      <?php endif; ?>

      <!-- Add Note -->
      <div class="card-box">
        <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;">Add Note</h4>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="action" value="add_note">
          <div style="display:flex;gap:4px;margin-bottom:8px;flex-wrap:wrap;">
            <?php foreach (['note'=>'📝','email'=>'📧','call'=>'📞','meeting'=>'🤝'] as $k => $v): ?>
            <button type="button" onclick="this.closest('form').querySelector('[name=note_type]').value='<?= $k ?>';this.closest('form').querySelectorAll('button').forEach(b=>b.style.background='#fff');this.style.background='#2563eb';this.style.color='#fff';" style="padding:4px 10px;border-radius:6px;font-size:11px;border:1px solid var(--adm-border);background:#fff;cursor:pointer;"><?= $v ?> <?= ucfirst($k) ?></button>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="note_type" value="note">
          <textarea name="note" class="form-control-admin" rows="3" placeholder="Add note..." style="font-size:12px;"></textarea>
          <button type="submit" class="btn-secondary-admin" style="width:100%;justify-content:center;margin-top:8px;font-size:12px;padding:8px;"><i class="fa-solid fa-plus"></i> Add Note</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
