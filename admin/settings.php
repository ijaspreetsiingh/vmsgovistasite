<?php
$pageTitle = 'Package Settings';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$types = ['category' => 'Category', 'tour_type' => 'Tour Type', 'destination' => 'Destination', 'country' => 'Country', 'city' => 'City'];

// ── Handle Add / Edit / Delete ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

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
