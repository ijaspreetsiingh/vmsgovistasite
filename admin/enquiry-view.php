<?php
$pageTitle = 'CRM – Enquiry Details';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { setFlash('error', 'Invalid enquiry.'); redirect(SITE_URL . '/admin/enquiries.php'); }

$db = getDB();

// ── Handle form actions ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    // Update status
    if ($action === 'update_status') {
        $newStatus = $_POST['new_status'] ?? '';
        $statusNote = trim($_POST['status_note'] ?? '');
        $validStatuses = ['new','read','contacted','qualified','proposal_sent','negotiation','converted','lost'];
        if (in_array($newStatus, $validStatuses)) {
            $oldStatus = $db->query("SELECT status FROM enquiries WHERE id=$id")->fetchColumn() ?: 'new';
            $db->prepare("UPDATE enquiries SET status=? WHERE id=?")->execute([$newStatus, $id]);
            // Log status change
            $db->prepare("INSERT INTO enquiry_status_history (enquiry_id, old_status, new_status, changed_by, notes) VALUES (?,?,?,?,?)")
               ->execute([$id, $oldStatus, $newStatus, $adminUser['name'] ?? 'Admin', $statusNote]);
            // Auto-log note
            if ($statusNote) {
                $db->prepare("INSERT INTO enquiry_notes (enquiry_id, note, note_type, created_by) VALUES (?,?,'status_change',?)")
                   ->execute([$id, "Status changed to {$newStatus}: {$statusNote}", $adminUser['name'] ?? 'Admin']);
            }
            setFlash('success', "Status updated to <strong>" . ucfirst($newStatus) . "</strong>.");
        } else {
            setFlash('error', 'Invalid status.');
        }
        redirect(SITE_URL . '/admin/enquiry-view.php?id=' . $id);
    }

    // Add note
    if ($action === 'add_note') {
        $note = trim($_POST['note'] ?? '');
        $noteType = $_POST['note_type'] ?? 'note';
        if ($note) {
            $db->prepare("INSERT INTO enquiry_notes (enquiry_id, note, note_type, created_by) VALUES (?,?,?,?)")
               ->execute([$id, $note, $noteType, $adminUser['name'] ?? 'Admin']);
            setFlash('success', 'Note added.');
        }
        redirect(SITE_URL . '/admin/enquiry-view.php?id=' . $id);
    }

    // Mark as converted
    if ($action === 'mark_converted') {
        $convValue = (float)($_POST['converted_value'] ?? 0);
        $convNote = trim($_POST['conversion_note'] ?? '');
        $now = date('Y-m-d H:i:s');
        $old = $db->query("SELECT status FROM enquiries WHERE id=$id")->fetchColumn() ?: 'new';
        $db->prepare("UPDATE enquiries SET status='converted', converted_at=?, converted_value=? WHERE id=?")
           ->execute([$now, $convValue > 0 ? $convValue : null, $id]);
        $db->prepare("INSERT INTO enquiry_status_history (enquiry_id, old_status, new_status, changed_by, notes) VALUES (?,?,?,?,?)")
           ->execute([$id, $old, 'converted', $adminUser['name'] ?? 'Admin', $convNote ?: 'Converted to booking']);
        $db->prepare("INSERT INTO enquiry_notes (enquiry_id, note, note_type, created_by) VALUES (?,?,?,?)")
           ->execute([$id, "Converted to booking" . ($convValue > 0 ? " (Value: \$" . number_format($convValue) . ")" : "") . ". {$convNote}", 'conversion', $adminUser['name'] ?? 'Admin']);
        setFlash('success', 'Marked as <strong>Converted</strong>!');
        redirect(SITE_URL . '/admin/enquiry-view.php?id=' . $id);
    }
}

// ── Load enquiry data ────────────────────────────────
$rows = fetchAll('SELECT * FROM enquiries WHERE id = ? LIMIT 1', [$id]);
$enq = $rows[0] ?? null;
if (!$enq) { setFlash('error', 'Enquiry not found.'); redirect(SITE_URL . '/admin/enquiries.php'); }

// Auto-mark as read when viewed
if ($enq['status'] === 'new') {
    $db->prepare("UPDATE enquiries SET status='read' WHERE id=?")->execute([$id]);
    $db->prepare("INSERT INTO enquiry_status_history (enquiry_id, old_status, new_status, changed_by) VALUES (?,?,?,?)")
       ->execute([$id, 'new', 'read', $adminUser['name'] ?? 'System']);
    $enq['status'] = 'read';
}

// Load notes
$notes = fetchAll("SELECT * FROM enquiry_notes WHERE enquiry_id=? ORDER BY created_at DESC", [$id]);

// Load status history
$history = fetchAll("SELECT * FROM enquiry_status_history WHERE enquiry_id=? ORDER BY created_at DESC", [$id]);

// ── Derived data ─────────────────────────────────────
$fullName = trim($enq['first_name'] . ' ' . $enq['last_name']);
$mailtoSubject = rawurlencode('Re: Your enquiry — ' . ($enq['package_title'] ?: SITE_NAME));
$mailtoBody = rawurlencode("Hi {$enq['first_name']},\n\nThank you for your interest in " . ($enq['package_title'] ?: 'our tours') . ".\n\n");
$mailto = 'mailto:' . rawurlencode($enq['email']) . '?subject=' . $mailtoSubject . '&body=' . $mailtoBody;

$packageLink = '';
if (!empty($enq['package_id'])) {
    $pkgRows = fetchAll('SELECT slug FROM packages WHERE id = ? LIMIT 1', [(int)$enq['package_id']]);
    if (!empty($pkgRows[0]['slug'])) {
        $packageLink = SITE_URL . '/package-details.php?slug=' . rawurlencode($pkgRows[0]['slug']);
    }
}

$initials = strtoupper(substr($enq['first_name'], 0, 1) . substr($enq['last_name'], 0, 1));

// Status config
$statusConfig = [
    'new'           => ['label' => 'New',           'color' => '#f79009', 'bg' => '#fffaeb', 'icon' => 'fa-star'],
    'read'          => ['label' => 'Read',          'color' => '#344054', 'bg' => '#f2f4f7', 'icon' => 'fa-envelope-open'],
    'contacted'     => ['label' => 'Contacted',     'color' => '#175cd3', 'bg' => '#eff4ff', 'icon' => 'fa-phone'],
    'qualified'     => ['label' => 'Qualified',     'color' => '#3538cd', 'bg' => '#eef0ff', 'icon' => 'fa-check-circle'],
    'proposal_sent' => ['label' => 'Proposal Sent', 'color' => '#9b5de5', 'bg' => '#f5efff', 'icon' => 'fa-file-lines'],
    'negotiation'   => ['label' => 'Negotiation',   'color' => '#e07c00', 'bg' => '#fff3e0', 'icon' => 'fa-handshake'],
    'converted'     => ['label' => 'Converted',     'color' => '#027a48', 'bg' => '#ecfdf3', 'icon' => 'fa-trophy'],
    'lost'          => ['label' => 'Lost',          'color' => '#b42318', 'bg' => '#fef3f2', 'icon' => 'fa-ban'],
];

$sc = $statusConfig[$enq['status']] ?? $statusConfig['new'];
// Exclude current status from available transitions
$transitions = $statusConfig;
unset($transitions[$enq['status']]);
// Exclude 'new' from available (you can't go back to new)
// Actually you can keep all available, it's up to the admin
?>


<style>
/* ── CRM Timeline ── */
.crm-timeline { position: relative; padding: 0; margin: 0; list-style: none; }
.crm-timeline::before {
  content: ''; position: absolute; left: 20px; top: 0; bottom: 0;
  width: 2px; background: var(--adm-border); border-radius: 2px;
}
.crm-timeline-item {
  position: relative; padding: 0 0 24px 56px; margin: 0;
}
.crm-timeline-item:last-child { padding-bottom: 0; }
.crm-timeline-dot {
  position: absolute; left: 12px; top: 4px;
  width: 18px; height: 18px; border-radius: 50%;
  border: 3px solid var(--adm-accent); background: #fff;
  z-index: 1;
}
.crm-timeline-dot.dot-note { border-color: #98a2b3; }
.crm-timeline-dot.dot-status { border-color: var(--adm-warning); }
.crm-timeline-dot.dot-email { border-color: var(--adm-accent); }
.crm-timeline-dot.dot-call { border-color: #12b76a; }
.crm-timeline-dot.dot-conversion { border-color: var(--adm-success); }
.crm-timeline-content { }
.crm-timeline-content .tl-head {
  display: flex; align-items: center; gap: 8px; margin-bottom: 4px;
  font-size: 13px; font-weight: 600; color: var(--adm-text);
}
.crm-timeline-content .tl-meta { font-size: 12px; color: var(--adm-text-muted); }
.crm-timeline-content .tl-text {
  margin-top: 6px; padding: 12px 14px;
  background: #f9fafb; border: 1px solid var(--adm-border);
  border-radius: 8px; font-size: 13px; line-height: 1.5;
  color: var(--adm-text-secondary); white-space: pre-wrap;
}
.crm-timeline-content .tl-badge {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 2px 10px; border-radius: 999px;
  font-size: 11px; font-weight: 600;
}

/* ── CRM Status Badge ── */
.crm-status-badge {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 6px 14px; border-radius: 999px;
  font-size: 13px; font-weight: 600;
}

/* ── Quick stats ── */
.crm-quick-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
.crm-quick-stat {
  padding: 14px 16px; background: #f9fafb; border: 1px solid var(--adm-border);
  border-radius: 10px; text-align: center;
}
.crm-quick-stat .qs-num { font-size: 20px; font-weight: 800; color: var(--adm-text); }
.crm-quick-stat .qs-label { font-size: 11px; color: var(--adm-text-muted); margin-top: 2px; }

/* ── Notes form ── */
.note-quick-btns { display: flex; gap: 6px; margin: 8px 0; flex-wrap: wrap; }
.note-quick-btns button {
  padding: 4px 12px; border-radius: 6px; font-size: 11px; font-weight: 600;
  border: 1px solid var(--adm-border); background: #fff; cursor: pointer; color: var(--adm-text-secondary);
  transition: all 0.15s;
}
.note-quick-btns button:hover { background: var(--adm-accent-soft); border-color: var(--adm-accent); color: var(--adm-accent); }

/* ── Status grid ── */
.status-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
@media (max-width: 768px) { .status-grid { grid-template-columns: repeat(2, 1fr); } }
.status-option {
  position: relative; padding: 12px; border-radius: 10px;
  border: 2px solid var(--adm-border); cursor: pointer; text-align: center;
  transition: all 0.15s;
}
.status-option:hover { border-color: var(--adm-accent); background: var(--adm-accent-soft); }
.status-option input[type="radio"] { position: absolute; opacity: 0; }
.status-option input[type="radio"]:checked + .status-option-inner .so-label { font-weight: 700; }
.status-option input[type="radio"]:checked ~ .status-option { border-color: var(--adm-accent); background: var(--adm-accent-soft); }

.conversion-panel { display: none; margin-top: 16px; padding: 16px; background: var(--adm-success-bg); border: 1px solid #abefc6; border-radius: 10px; }
</style>

<a href="<?= SITE_URL ?>/admin/enquiries.php" class="detail-back">
  <i class="fa-solid fa-arrow-left"></i> Back to all leads
</a>

<div class="card-box p-0">
  <div class="detail-header-row">
    <div style="display:flex;align-items:center;gap:16px;">
      <div class="avatar" style="width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;font-size:18px;font-weight:700;flex-shrink:0;"><?= e($initials) ?></div>
      <div>
        <h2><?= e($fullName) ?></h2>
        <p class="meta">
          Enquiry #<?= (int)$enq['id'] ?> · <?= e($enq['email']) ?> · Received <?= date('d M Y H:i', strtotime($enq['created_at'])) ?>
        </p>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:12px;">
      <span class="crm-status-badge" style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>;border:1px solid <?= $sc['color'] ?>33;">
        <i class="fa-solid <?= $sc['icon'] ?>"></i> <?= $sc['label'] ?>
      </span>
      <?php if ($enq['status'] === 'converted' && $enq['converted_value'] > 0): ?>
      <span style="font-size:13px;font-weight:700;color:var(--adm-success);">$<?= number_format((float)$enq['converted_value']) ?></span>
      <?php endif; ?>
    </div>
  </div>

  <div class="detail-grid" style="padding:24px;">
    <!-- LEFT: Main Content -->
    <div>
      <!-- Quick Stats -->
      <div class="crm-quick-stats">
        <div class="crm-quick-stat">
          <div class="qs-num"><?= strtotime($enq['created_at']) ? ceil((time() - strtotime($enq['created_at'])) / 86400) : '—' ?></div>
          <div class="qs-label">Days Since Lead</div>
        </div>
        <div class="crm-quick-stat">
          <div class="qs-num"><?= count($notes) ?></div>
          <div class="qs-label">Total Notes</div>
        </div>
        <div class="crm-quick-stat">
          <div class="qs-num"><?= count($history) ?></div>
          <div class="qs-label">Status Changes</div>
        </div>
      </div>

      <!-- Contact & Trip Details -->
      <div class="card-box" style="box-shadow:none;margin-bottom:20px;">
        <h4 style="margin:0 0 16px;font-size:14px;font-weight:700;">Contact &amp; Trip Details</h4>
        <dl class="detail-dl">
          <dt>Full name</dt><dd><?= e($fullName) ?></dd>
          <dt>Email</dt><dd><a href="mailto:<?= e($enq['email']) ?>"><?= e($enq['email']) ?></a></dd>
          <dt>Phone</dt><dd><?= e($enq['phone'] ?: '—') ?> <?php if($enq['phone']): ?><a href="https://wa.me/<?= preg_replace('/\D/','',$enq['phone']) ?>?text=Hi%20<?= urlencode($enq['first_name']) ?>%2C%20regarding%20your%20enquiry" target="_blank" style="margin-left:6px;font-size:12px;color:#25D366;"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a><?php endif; ?></dd>
          <dt>Country</dt><dd><?= e($enq['country'] ?: '—') ?></dd>
          <dt>Adults / Children</dt><dd><?= $enq['adults'] !== null ? (int)$enq['adults'] : '—' ?> / <?= $enq['children'] !== null ? (int)$enq['children'] : '—' ?></dd>
          <dt>Source</dt><dd><?= e($enq['source'] ?? 'Website') ?></dd>
          <dt>Package</dt><dd><?= $enq['package_title'] ? e($enq['package_title']) . ($packageLink ? ' <a href="'.e($packageLink).'" target="_blank" style="font-size:12px;">View <i class="fa-solid fa-arrow-up-right-from-square"></i></a>' : '') : 'General enquiry' ?></dd>
        </dl>
      </div>

      <!-- Original Message -->
      <div class="card-box" style="box-shadow:none;margin-bottom:20px;">
        <h4 style="margin:0 0 12px;font-size:14px;font-weight:700;">Original Message</h4>
        <div class="detail-message" style="margin-top:0;"><?= e($enq['message'] ?? '') ?></div>
      </div>

      <!-- Timeline: Notes & Activity -->
      <div class="card-box" style="box-shadow:none;">
        <h4 style="margin:0 0 16px;font-size:14px;font-weight:700;">Activity Timeline</h4>

        <?php if (empty($notes) && empty($history)): ?>
          <p style="color:var(--adm-text-muted);font-size:13px;">No activity recorded yet.</p>
        <?php endif; ?>

        <ul class="crm-timeline">
          <?php
          // Merge notes and history into a single timeline
          $timeline = [];
          foreach ($notes as $n) {
            $timeline[] = ['type' => 'note', 'data' => $n, 'date' => $n['created_at']];
          }
          foreach ($history as $h) {
            $timeline[] = ['type' => 'history', 'data' => $h, 'date' => $h['created_at']];
          }
          usort($timeline, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
          ?>
          <?php foreach ($timeline as $tl): ?>
            <?php if ($tl['type'] === 'note'): $n = $tl['data']; ?>
            <li class="crm-timeline-item">
              <div class="crm-timeline-dot dot-<?= e($n['note_type']) ?>"></div>
              <div class="crm-timeline-content">
                <div class="tl-head">
                  <span class="tl-badge" style="background:<?= $n['note_type']==='conversion'?'#ecfdf3':'#f2f4f7' ?>;color:<?= $n['note_type']==='conversion'?'#027a48':'#344054' ?>;">
                    <?php $iconMap=['note'=>'note-sticky','email'=>'envelope','call'=>'phone','meeting'=>'handshake','conversion'=>'trophy']; ?><i class="fa-solid fa-<?= $iconMap[$n['note_type']] ?? 'circle' ?>"></i>
                    <?= ucfirst($n['note_type']) ?>
                  </span>
                  <span class="tl-meta">by <?= e($n['created_by'] ?? 'System') ?> · <?= date('d M Y H:i', strtotime($n['created_at'])) ?></span>
                </div>
                <div class="tl-text"><?= e($n['note']) ?></div>
              </div>
            </li>
            <?php else: $h = $tl['data']; ?>
            <li class="crm-timeline-item">
              <div class="crm-timeline-dot dot-status"></div>
              <div class="crm-timeline-content">
                <div class="tl-head">
                  <span>Status changed</span>
                  <span class="tl-meta">by <?= e($h['changed_by'] ?? 'System') ?> · <?= date('d M Y H:i', strtotime($h['created_at'])) ?></span>
                </div>
                <div style="margin-top:4px;font-size:13px;">
                  <span class="crm-status-badge" style="background:<?= ($statusConfig[$h['old_status']]??[])['bg']??'#f2f4f7' ?>;color:<?= ($statusConfig[$h['old_status']]??[])['color']??'#344054' ?>;font-size:11px;padding:2px 10px;"><?= ucfirst($h['old_status'] ?? '—') ?></span>
                  <i class="fa-solid fa-arrow-right" style="margin:0 8px;color:var(--adm-text-muted);font-size:12px;"></i>
                  <span class="crm-status-badge" style="background:<?= ($statusConfig[$h['new_status']]??[])['bg']??'#f2f4f7' ?>;color:<?= ($statusConfig[$h['new_status']]??[])['color']??'#344054' ?>;font-size:11px;padding:2px 10px;"><?= ucfirst($h['new_status']) ?></span>
                  <?php if ($h['notes']): ?><div class="tl-text" style="margin-top:8px;"><?= e($h['notes']) ?></div><?php endif; ?>
                </div>
              </div>
            </li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <!-- RIGHT: Sidebar Actions -->
    <div class="detail-sidebar">
      <!-- Quick Actions -->
      <div class="card-box" style="margin-bottom:16px;">
        <h4 style="margin:0 0 14px;font-size:13px;font-weight:700;">Quick Actions</h4>
        <div class="action-stack">
          <a href="<?= e($mailto) ?>" class="btn-primary-admin" style="justify-content:center;">
            <i class="fa-solid fa-reply"></i> Reply via Email
          </a>
          <?php if ($enq['phone']): ?>
          <a href="tel:<?= e($enq['phone']) ?>" class="btn-secondary-admin" style="justify-content:center;">
            <i class="fa-solid fa-phone"></i> Call <?= e($enq['first_name']) ?>
          </a>
          <a href="https://wa.me/<?= preg_replace('/\D/','',$enq['phone']) ?>?text=Hi%20<?= urlencode($enq['first_name']) ?>%2C%20I'm%20following%20up%20on%20your%20enquiry%20for%20<?= urlencode($enq['package_title'] ?: 'our tours') ?>" target="_blank" class="btn-secondary-admin" style="justify-content:center;color:#25D366;border-color:#25D36644;">
            <i class="fa-brands fa-whatsapp"></i> WhatsApp
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Status Update -->
      <div class="card-box" style="margin-bottom:16px;">
        <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;">Update Status</h4>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="action" value="update_status">
          <div class="status-grid">
            <?php foreach ($transitions as $val => $cfg): ?>
            <label class="status-option" style="background:<?= $val===$enq['status']?$cfg['bg']:'#fff' ?>;border-color:<?= $val===$enq['status']?$cfg['color'].'44':'var(--adm-border)' ?>;">
              <input type="radio" name="new_status" value="<?= $val ?>" <?= $val===$enq['status']?'checked':'' ?>>
              <div class="status-option-inner">
                <div style="font-size:18px;margin-bottom:4px;"><i class="fa-solid <?= $cfg['icon'] ?>" style="color:<?= $cfg['color'] ?>;"></i></div>
                <div class="so-label" style="font-size:11px;font-weight:600;color:var(--adm-text);"><?= $cfg['label'] ?></div>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
          <input type="text" name="status_note" class="form-control-admin" style="margin-top:12px;font-size:13px;" placeholder="Note for this status change (optional)">
          <button type="submit" class="btn-primary-admin" style="width:100%;justify-content:center;margin-top:10px;">
            <i class="fa-solid fa-arrow-right"></i> Update Status
          </button>
        </form>
      </div>

      <!-- Conversion Panel (Show when marking converted) -->
      <div class="card-box" style="margin-bottom:16px;border-color:#abefc6;background:var(--adm-success-bg);">
        <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;color:#027a48;">
          <i class="fa-solid fa-trophy me-1"></i> Mark as Converted
        </h4>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="action" value="mark_converted">
          <div class="form-group" style="margin-bottom:10px;">
            <label style="font-size:12px;">Booking Value ($)</label>
            <input type="number" name="converted_value" class="form-control-admin" step="0.01" min="0" placeholder="0.00" style="font-size:13px;">
          </div>
          <div class="form-group" style="margin-bottom:10px;">
            <label style="font-size:12px;">Notes / Booking Reference</label>
            <input type="text" name="conversion_note" class="form-control-admin" placeholder="e.g. Booking confirmed for Aug 2025" style="font-size:13px;">
          </div>
          <button type="submit" class="btn-primary-admin" style="width:100%;justify-content:center;background:var(--adm-success);" onclick="return confirm('Mark this lead as converted?')">
            <i class="fa-solid fa-check-circle"></i> Mark as Converted
          </button>
        </form>
      </div>

      <!-- Add Note -->
      <div class="card-box">
        <h4 style="margin:0 0 12px;font-size:13px;font-weight:700;">Add Note</h4>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="action" value="add_note">
          <div class="note-quick-btns">
            <button type="button" onclick="this.closest('form').querySelector('[name=note_type]').value='note';this.closest('.note-quick-btns').querySelectorAll('button').forEach(b=>b.style.background='#fff');this.style.background='#2563eb';this.style.color='#fff';" style="background:#2563eb;color:#fff;">📝 Note</button>
            <button type="button" onclick="this.closest('form').querySelector('[name=note_type]').value='email';this.closest('.note-quick-btns').querySelectorAll('button').forEach(b=>b.style.background='#fff');this.style.background='#2563eb';this.style.color='#fff';">📧 Email</button>
            <button type="button" onclick="this.closest('form').querySelector('[name=note_type]').value='call';this.closest('.note-quick-btns').querySelectorAll('button').forEach(b=>b.style.background='#fff');this.style.background='#2563eb';this.style.color='#fff';">📞 Call</button>
            <button type="button" onclick="this.closest('form').querySelector('[name=note_type]').value='meeting';this.closest('.note-quick-btns').querySelectorAll('button').forEach(b=>b.style.background='#fff');this.style.background='#2563eb';this.style.color='#fff';">🤝 Meeting</button>
          </div>
          <input type="hidden" name="note_type" value="note">
          <textarea name="note" class="form-control-admin" rows="3" placeholder="Add an internal note..." style="font-size:13px;"></textarea>
          <button type="submit" class="btn-secondary-admin" style="width:100%;justify-content:center;margin-top:10px;">
            <i class="fa-solid fa-plus"></i> Add Note
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
