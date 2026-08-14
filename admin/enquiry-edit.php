<?php
$pageTitle = 'CRM – Edit Enquiry';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { setFlash('error', 'Invalid enquiry.'); redirect(SITE_URL . '/admin/enquiries.php'); }

$db = getDB();

// ── Handle save ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $fields = [
        'first_name', 'last_name', 'email', 'country', 'phone',
        'package_title', 'preferred_time', 'source', 'assigned_to', 'tags', 'message',
    ];
    $data = [];
    foreach ($fields as $f) {
        $data[$f] = trim($_POST[$f] ?? '');
    }
    $adults   = max(0, (int)($_POST['adults'] ?? 0));
    $children = max(0, (int)($_POST['children'] ?? 0));
    $travelDate = trim($_POST['travel_date'] ?? '');
    if ($travelDate && !strtotime($travelDate)) { $travelDate = ''; }

    $stmt = $db->prepare("UPDATE enquiries SET
        first_name=?, last_name=?, email=?, country=?, phone=?,
        package_title=?, adults=?, children=?, travel_date=?, preferred_time=?,
        source=?, assigned_to=?, tags=?, message=?, updated_at=NOW()
        WHERE id=?");
    $stmt->execute([
        $data['first_name'], $data['last_name'], $data['email'], $data['country'], $data['phone'],
        $data['package_title'], $adults, $children, $travelDate ?: null, $data['preferred_time'],
        $data['source'], $data['assigned_to'], $data['tags'], $data['message'], $id,
    ]);

    // Log the edit
    $db->prepare("INSERT INTO enquiry_notes (enquiry_id, note, note_type, created_by) VALUES (?,?,'note',?)")
       ->execute([$id, "Enquiry details edited by " . ($adminUser['name'] ?? 'Admin') . ".", $adminUser['name'] ?? 'Admin']);

    setFlash('success', 'Enquiry updated.');
    redirect(SITE_URL . '/admin/enquiry-view.php?id=' . $id);
}

// ── Load enquiry ─────────────────────────────────────
$rows = fetchAll('SELECT * FROM enquiries WHERE id = ? LIMIT 1', [$id]);
$enq = $rows[0] ?? null;
if (!$enq) { setFlash('error', 'Enquiry not found.'); redirect(SITE_URL . '/admin/enquiries.php'); }

$fullName = trim($enq['first_name'] . ' ' . $enq['last_name']);
?>

<a href="<?= SITE_URL ?>/admin/enquiry-view.php?id=<?= (int)$enq['id'] ?>" class="detail-back">
  <i class="fa-solid fa-arrow-left"></i> Back to enquiry
</a>

<div class="card-box" style="padding:24px;max-width:860px;">
  <div style="display:flex;align-items:center;gap:14px;margin-bottom:22px;">
    <div class="avatar" style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;font-size:17px;font-weight:700;flex-shrink:0;"><?= e(strtoupper(substr($enq['first_name'],0,1) . substr($enq['last_name'],0,1))) ?></div>
    <div>
      <h2 style="margin:0;font-size:20px;">Edit Enquiry #<?= (int)$enq['id'] ?></h2>
      <p class="meta" style="margin:2px 0 0;font-size:13px;color:var(--adm-text-muted);"><?= e($fullName) ?> · <?= e($enq['email']) ?></p>
    </div>
  </div>

  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

    <div class="detail-grid" style="grid-template-columns:1fr 1fr;gap:16px;">
      <div class="form-group">
        <label class="form-label-admin">First Name</label>
        <input type="text" name="first_name" class="form-control-admin" value="<?= e($enq['first_name']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label-admin">Last Name</label>
        <input type="text" name="last_name" class="form-control-admin" value="<?= e($enq['last_name']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label-admin">Email</label>
        <input type="email" name="email" class="form-control-admin" value="<?= e($enq['email']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label-admin">Phone</label>
        <input type="text" name="phone" class="form-control-admin" value="<?= e($enq['phone']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label-admin">Country</label>
        <input type="text" name="country" class="form-control-admin" value="<?= e($enq['country']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label-admin">Package</label>
        <input type="text" name="package_title" class="form-control-admin" value="<?= e($enq['package_title']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label-admin">Adults</label>
        <input type="number" name="adults" class="form-control-admin" min="0" value="<?= (int)($enq['adults'] ?? 0) ?>">
      </div>
      <div class="form-group">
        <label class="form-label-admin">Children</label>
        <input type="number" name="children" class="form-control-admin" min="0" value="<?= (int)($enq['children'] ?? 0) ?>">
      </div>
      <div class="form-group">
        <label class="form-label-admin">Travelling Date</label>
        <input type="date" name="travel_date" class="form-control-admin" value="<?= e($enq['travel_date'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label-admin">Preferred Time</label>
        <input type="text" name="preferred_time" class="form-control-admin" value="<?= e($enq['preferred_time']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label-admin">Source</label>
        <input type="text" name="source" class="form-control-admin" value="<?= e($enq['source'] ?? 'Website') ?>">
      </div>
      <div class="form-group">
        <label class="form-label-admin">Assigned To</label>
        <input type="text" name="assigned_to" class="form-control-admin" value="<?= e($enq['assigned_to']) ?>">
      </div>
      <div class="form-group" style="grid-column:1 / -1;">
        <label class="form-label-admin">Tags</label>
        <input type="text" name="tags" class="form-control-admin" value="<?= e($enq['tags']) ?>" placeholder="comma separated">
      </div>
      <div class="form-group" style="grid-column:1 / -1;">
        <label class="form-label-admin">Message</label>
        <textarea name="message" class="form-control-admin" rows="5"><?= e($enq['message'] ?? '') ?></textarea>
      </div>
    </div>

    <div style="display:flex;gap:10px;margin-top:22px;align-items:center;">
      <button type="submit" class="btn-primary-admin"><i class="fa-solid fa-save"></i> Save Changes</button>
      <a href="<?= SITE_URL ?>/admin/enquiry-view.php?id=<?= (int)$enq['id'] ?>" class="btn-ghost-admin">Cancel</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
