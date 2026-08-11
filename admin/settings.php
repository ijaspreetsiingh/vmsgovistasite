<?php
$pageTitle = 'Package Settings';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/mailer.php';

$db = getDB();
$types = ['category' => 'Category', 'tour_type' => 'Tour Type', 'destination' => 'Destination', 'country' => 'Country', 'city' => 'City'];

// ── Handle Add / Edit / Delete ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    // ── Save Email & SMTP settings ──
    if ($action === 'smtp_save') {
        $map = [
            'mail_enabled'       => isset($_POST['mail_enabled']) ? '1' : '0',
            'smtp_host'          => trim($_POST['smtp_host'] ?? ''),
            'smtp_port'          => trim($_POST['smtp_port'] ?? '587'),
            'smtp_user'          => trim($_POST['smtp_user'] ?? ''),
            'smtp_encryption'    => in_array($_POST['smtp_encryption'] ?? '', ['tls','ssl','none'], true) ? $_POST['smtp_encryption'] : 'tls',
            'smtp_from_email'    => trim($_POST['smtp_from_email'] ?? ''),
            'smtp_from_name'     => trim($_POST['smtp_from_name'] ?? SITE_NAME),
            'admin_notify_email' => trim($_POST['admin_notify_email'] ?? ''),
        ];
        // Password: keep old if left blank
        $pass = (string)($_POST['smtp_pass'] ?? '');
        if ($pass !== '') {
            $map['smtp_pass'] = $pass;
        }
        foreach ($map as $k => $v) {
            setSetting($k, $v);
        }
        setFlash('success', 'Email & SMTP settings saved.');
        redirect(SITE_URL . '/admin/settings.php#email-smtp');
    }

    // ── Send a test email ──
    if ($action === 'test_email') {
        $to = trim($_POST['test_to'] ?? '');
        if ($to === '') {
            $to = getSetting('admin_notify_email', '');
        }
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'Enter a valid test recipient email address.');
        } else {
            $res = sendMail(
                $to,
                'Test Email — ' . SITE_NAME,
                emailTemplate('SMTP Test Email',
                    '<p style="margin:0;font-size:14px;color:#475467;line-height:1.7;">If you are reading this, your <strong>SMTP configuration is working perfectly</strong>. Great job! &#127881;</p>')
            );
            setFlash($res['success'] ? 'success' : 'error', ($res['success'] ? 'Test email sent to ' . e($to) . '. ' : 'Test failed: ') . e($res['message']));
        }
        redirect(SITE_URL . '/admin/settings.php#email-smtp');
    }

    if ($action === 'add' || $action === 'edit') {
        $type  = trim($_POST['type'] ?? '');
        $value = trim($_POST['value'] ?? '');
        $sort  = (int)($_POST['sort_order'] ?? 0);
        $id    = (int)($_POST['id'] ?? 0);

        if ($type && $value) {
            try {
                if ($action === 'add') {
                    $db->prepare("INSERT INTO package_settings (type, value, sort_order) VALUES (?,?,?)")
                       ->execute([$type, $value, $sort]);
                    setFlash('success', 'Setting added successfully.');
                } else {
                    $db->prepare("UPDATE package_settings SET type=?, value=?, sort_order=? WHERE id=?")
                       ->execute([$type, $value, $sort, $id]);
                    setFlash('success', 'Setting updated successfully.');
                }
            } catch (Exception $e) {
                setFlash('error', 'Duplicate value or error: ' . $e->getMessage());
            }
        } else {
            setFlash('error', 'Type and value are required.');
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db->prepare("DELETE FROM package_settings WHERE id=?")->execute([$id]);
            setFlash('success', 'Setting deleted.');
        }
    }

    redirect(SITE_URL . '/admin/settings.php');
}

// ── Load all settings grouped ──
$all = fetchAll("SELECT * FROM package_settings ORDER BY FIELD(type,'category','tour_type','destination','country','city'), sort_order ASC, value ASC");
$grouped = [];
foreach ($all as $r) {
    $grouped[$r['type']][] = $r;
}
?>
<style>
.setting-page { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
@media(max-width:1100px){ .setting-page{grid-template-columns:1fr;} }
.setting-card { background:var(--adm-surface, #fff); border:1px solid var(--adm-border, #e4e7ec); border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(16,24,40,.06); }
.setting-card h3 { margin:0; padding:14px 18px; font-size:13px; font-weight:700; color:var(--adm-text, #101828); border-bottom:1px solid var(--adm-border, #e4e7ec); display:flex; align-items:center; gap:8px; }
.setting-card h3 .badge { background:var(--adm-accent, #003A59); color:#fff; font-size:10px; padding:2px 8px; border-radius:999px; }
.setting-card .list { padding:8px; }
.setting-item { display:flex; align-items:center; justify-content:space-between; padding:8px 12px; border-radius:6px; transition:background .15s; }
.setting-item:hover { background:var(--adm-bg, #f4f5f7); }
.setting-item .val { font-size:13px; color:var(--adm-text, #101828); font-weight:500; }
.setting-item .sort { font-size:11px; color:var(--adm-text-muted, #667085); margin-left:10px; }
.setting-item .actions { display:flex; gap:4px; }
.setting-item .actions button { background:none; border:none; color:var(--adm-text-muted, #667085); cursor:pointer; padding:4px 6px; border-radius:4px; font-size:12px; transition:all .15s; }
.setting-item .actions button:hover { color:var(--adm-text, #101828); background:var(--adm-border, #e4e7ec); }
.setting-item .actions .del:hover { color:#f04438; background:rgba(240,68,56,.1); }
.setting-empty { padding:20px; text-align:center; color:var(--adm-text-muted, #667085); font-size:13px; }
.add-form { display:flex; gap:8px; padding:12px; border-top:1px solid var(--adm-border, #e4e7ec); flex-wrap:wrap; }
.add-form select, .add-form input { background:#fff; border:1px solid var(--adm-border-strong, #d0d5dd); color:var(--adm-text, #101828); padding:7px 12px; border-radius:6px; font-size:13px; flex:1; min-width:100px; }
.add-form select:focus, .add-form input:focus { outline:none; border-color:var(--adm-accent, #003A59); box-shadow:0 0 0 3px rgba(0,58,89,.12); }
.add-form button { background:var(--adm-accent, #003A59); color:#fff; border:none; padding:7px 16px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; white-space:nowrap; transition:background .15s; }
.add-form button:hover { background:var(--adm-accent-hover, #002B43); }
</style>

<!-- ===== EMAIL & SMTP SETTINGS ===== -->
<div class="setting-card" id="email-smtp" style="margin-bottom:24px;">
    <h3>
        <i class="fa-solid fa-envelope-circle-check"></i> Email &amp; SMTP Settings
        <span class="badge">Notifications</span>
    </h3>
    <div style="padding:18px;">
        <p style="margin:0 0 16px;font-size:13px;color:#667085;line-height:1.6;">
            Configure your mail server here. Works with <strong>Gmail / Google Workspace</strong> (use an
            <a href="https://support.google.com/accounts/answer/185833" target="_blank" rel="noopener">App Password</a>),
            <strong>Outlook / Microsoft 365</strong>, or any <strong>official webmail SMTP</strong> of your hosting provider.
            When a package is booked or the contact form is submitted, the admin gets a notification and the customer gets a thank-you email.
        </p>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="smtp_save">

            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding:12px 14px;background:#f9fafb;border:1px solid #e4e7ec;border-radius:8px;">
                <input type="checkbox" name="mail_enabled" id="mail_enabled" style="width:18px;height:18px;accent-color:#003A59;" <?= mailIsEnabled() ? 'checked' : '' ?>>
                <label for="mail_enabled" style="font-size:13px;font-weight:600;color:#344054;margin:0;">Enable email notifications (admin alerts + customer thank-you emails)</label>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;">
                <div class="form-group" style="margin:0;">
                    <label style="display:block;font-size:12px;color:#475467;margin-bottom:4px;">SMTP Host *</label>
                    <input type="text" name="smtp_host" class="form-control-admin" placeholder="smtp.gmail.com" value="<?= e(getSetting('smtp_host')) ?>">
                    <small style="color:#98a2b3;font-size:11px;">e.g. smtp.gmail.com, smtp.office365.com, mail.yourdomain.com</small>
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="display:block;font-size:12px;color:#475467;margin-bottom:4px;">Port *</label>
                    <input type="number" name="smtp_port" class="form-control-admin" value="<?= e(getSetting('smtp_port', '587')) ?>" min="1" max="65535">
                    <small style="color:#98a2b3;font-size:11px;">587 (TLS) / 465 (SSL) / 25</small>
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="display:block;font-size:12px;color:#475467;margin-bottom:4px;">Encryption *</label>
                    <select name="smtp_encryption" class="form-control-admin">
                        <?php $enc = getSetting('smtp_encryption', 'tls'); ?>
                        <option value="tls" <?= $enc==='tls'?'selected':'' ?>>TLS (STARTTLS, port 587)</option>
                        <option value="ssl" <?= $enc==='ssl'?'selected':'' ?>>SSL (implicit, port 465)</option>
                        <option value="none" <?= $enc==='none'?'selected':'' ?>>None (plain, port 25)</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="display:block;font-size:12px;color:#475467;margin-bottom:4px;">SMTP Username</label>
                    <input type="text" name="smtp_user" class="form-control-admin" placeholder="you@gmail.com" value="<?= e(getSetting('smtp_user')) ?>">
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="display:block;font-size:12px;color:#475467;margin-bottom:4px;">SMTP Password / App Password</label>
                    <input type="password" name="smtp_pass" class="form-control-admin" placeholder="••••••••••••" autocomplete="new-password">
                    <small style="color:#98a2b3;font-size:11px;">Leave blank to keep current password</small>
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="display:block;font-size:12px;color:#475467;margin-bottom:4px;">From Email *</label>
                    <input type="email" name="smtp_from_email" class="form-control-admin" placeholder="noreply@yourdomain.com" value="<?= e(getSetting('smtp_from_email')) ?>">
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="display:block;font-size:12px;color:#475467;margin-bottom:4px;">From Name</label>
                    <input type="text" name="smtp_from_name" class="form-control-admin" value="<?= e(getSetting('smtp_from_name', SITE_NAME)) ?>">
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="display:block;font-size:12px;color:#475467;margin-bottom:4px;">Admin Notification Email *</label>
                    <input type="text" name="admin_notify_email" class="form-control-admin" placeholder="admin@yourdomain.com" value="<?= e(getSetting('admin_notify_email')) ?>">
                    <small style="color:#98a2b3;font-size:11px;">Multiple addresses allowed, comma-separated</small>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap;">
                <button type="submit" class="btn-primary-admin" style="background:#003A59;border:none;"><i class="fa-solid fa-save"></i> Save Settings</button>
                <span style="font-size:12px;color:#98a2b3;align-self:center;">Status: <?= mailIsEnabled() ? '<span style="color:#027a48;font-weight:600;">ON</span>' : '<span style="color:#b42318;font-weight:600;">OFF</span>' ?> <?= getSetting('smtp_host')!=='' ? '· SMTP configured' : '· SMTP not configured (will use PHP mail())' ?></span>
            </div>
        </form>

        <!-- Test email -->
        <form method="POST" style="margin-top:18px;padding-top:16px;border-top:1px dashed #e4e7ec;">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="test_email">
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <label style="font-size:13px;font-weight:600;color:#344054;">Send test email to:</label>
                <input type="email" name="test_to" class="form-control-admin" style="flex:1;min-width:200px;" placeholder="recipient@example.com (defaults to admin notification email)" value="<?= e(getSetting('admin_notify_email')) ?>">
                <button type="submit" class="btn-secondary-admin"><i class="fa-solid fa-paper-plane"></i> Send Test Email</button>
            </div>
        </form>
    </div>
</div>

<div class="setting-page">
    <?php foreach ($types as $tkey => $tlabel): 
        $items = $grouped[$tkey] ?? [];
    ?>
    <div class="setting-card">
        <h3>
            <i class="fa-solid fa-<?= $tkey === 'category' ? 'tags' : ($tkey === 'tour_type' ? 'route' : ($tkey === 'destination' ? 'location-dot' : ($tkey === 'country' ? 'flag' : 'building'))) ?>"></i>
            <?= $tlabel ?>s
            <span class="badge"><?= count($items) ?></span>
        </h3>
        <div class="list">
            <?php if (empty($items)): ?>
                <div class="setting-empty">No <?= strtolower($tlabel) ?> added yet.</div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                <div class="setting-item">
                    <div>
                        <span class="val"><?= e($item['value']) ?></span>
                        <span class="sort">#<?= (int)$item['sort_order'] ?></span>
                    </div>
                    <div class="actions">
                        <button onclick="editSetting(<?= $item['id'] ?>, '<?= e($item['type']) ?>', '<?= e($item['value']) ?>', <?= (int)$item['sort_order'] ?>)" title="Edit">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this setting?')">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="del" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- Add form -->
        <form method="POST" class="add-form">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="type" value="<?= $tkey ?>">
            <input type="text" name="value" placeholder="New <?= strtolower($tlabel) ?>..." required>
            <input type="number" name="sort_order" placeholder="Sort" value="0" min="0" style="max-width:70px;">
            <button type="submit"><i class="fa-solid fa-plus"></i> Add</button>
        </form>
    </div>
    <?php endforeach; ?>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:999;align-items:center;justify-content:center;" onclick="if(event.target===this)closeEdit()">
    <div style="background:#fff;border:1px solid #e4e7ec;border-radius:12px;padding:24px;max-width:400px;width:90%;box-shadow:0 8px 30px rgba(16,24,40,.18);">
        <h4 style="margin:0 0 16px;font-size:15px;color:#101828;">Edit Setting</h4>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-group" style="margin-bottom:12px;">
                <label style="display:block;font-size:12px;color:#475467;margin-bottom:4px;">Type</label>
                <select name="type" id="edit-type" class="form-control-admin">
                    <?php foreach ($types as $tkey => $tlabel): ?>
                    <option value="<?= $tkey ?>"><?= $tlabel ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label style="display:block;font-size:12px;color:#475467;margin-bottom:4px;">Value</label>
                <input type="text" name="value" id="edit-value" class="form-control-admin" required>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label style="display:block;font-size:12px;color:#475467;margin-bottom:4px;">Sort Order</label>
                <input type="number" name="sort_order" id="edit-sort" class="form-control-admin" min="0" value="0">
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeEdit()" class="btn-secondary-admin" style="padding:8px 16px;">Cancel</button>
                <button type="submit" class="btn-primary-admin" style="padding:8px 16px;"><i class="fa-solid fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSetting(id, type, value, sort) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-type').value = type;
    document.getElementById('edit-value').value = value;
    document.getElementById('edit-sort').value = sort;
    document.getElementById('editModal').style.display = 'flex';
}
function closeEdit() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
