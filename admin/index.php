<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$totalPackages   = $db->query("SELECT COUNT(*) FROM packages")->fetchColumn();
$publishedPkgs   = $db->query("SELECT COUNT(*) FROM packages WHERE status='published'")->fetchColumn();
$draftPkgs       = $db->query("SELECT COUNT(*) FROM packages WHERE status='draft'")->fetchColumn();
$totalEnquiries  = $db->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
$newEnquiries    = $db->query("SELECT COUNT(*) FROM enquiries WHERE status='new'")->fetchColumn();
$recentPkgs      = fetchAll("SELECT id, title, slug, status, destination, days, is_featured, is_popular, show_on_homepage, created_at FROM packages ORDER BY id DESC LIMIT 8");
$recentEnqs      = fetchAll("SELECT * FROM enquiries ORDER BY created_at DESC LIMIT 5");
?>

<?php
// ── CRM pipeline stats ──
$crmStats = [];
$statusList = ['new','read','contacted','qualified','proposal_sent','negotiation','converted','lost'];
$totalLeads = 0;
foreach ($statusList as $st) {
    $c = (int)$db->query("SELECT COUNT(*) FROM enquiries WHERE status='$st'")->fetchColumn();
    $crmStats[$st] = $c;
    $totalLeads += $c;
}
$convertedCount = $crmStats['converted'] ?? 0;
$conversionRate = $totalLeads > 0 ? round(($convertedCount / $totalLeads) * 100) : 0;
$newLeads = $crmStats['new'] ?? 0;
?>

<div class="stats-grid">
  <a href="<?= SITE_URL ?>/admin/packages.php" class="stat-card stat-blue" style="text-decoration:none;color:inherit;">
    <div class="stat-icon-wrap"><i class="fa-solid fa-suitcase"></i></div>
    <div class="stat-body">
      <div class="stat-label">Total Packages</div>
      <div class="stat-num"><?= $totalPackages ?></div>
      <div class="stat-meta"><?= $publishedPkgs ?> published</div>
    </div>
  </a>
  <a href="<?= SITE_URL ?>/admin/enquiries.php" class="stat-card stat-amber" style="text-decoration:none;color:inherit;">
    <div class="stat-icon-wrap"><i class="fa-solid fa-envelope"></i></div>
    <div class="stat-body">
      <div class="stat-label">New Leads (Pipeline)</div>
      <div class="stat-num"><?= $totalLeads ?></div>
      <?php if ($newLeads > 0): ?>
      <div class="stat-meta"><?= $newLeads ?> waiting</div>
      <?php endif; ?>
    </div>
  </a>
  <a href="<?= SITE_URL ?>/admin/enquiries.php?status=converted" class="stat-card stat-green" style="text-decoration:none;color:inherit;">
    <div class="stat-icon-wrap"><i class="fa-solid fa-trophy"></i></div>
    <div class="stat-body">
      <div class="stat-label">Converted Leads</div>
      <div class="stat-num"><?= $convertedCount ?></div>
      <div class="stat-meta"><?= $conversionRate ?>% conversion rate</div>
    </div>
  </a>
  <a href="<?= SITE_URL ?>/admin/enquiries.php?status=new" class="stat-card stat-red" style="text-decoration:none;color:inherit;">
    <div class="stat-icon-wrap"><i class="fa-solid fa-bell"></i></div>
    <div class="stat-body">
      <div class="stat-label">New / Unread</div>
      <div class="stat-num"><?= $newLeads ?></div>
      <?php if ($newLeads > 0): ?>
      <div class="stat-meta">Needs attention!</div>
      <?php endif; ?>
    </div>
  </a>
</div>

<!-- Pipeline quick view -->
<div class="card-box" style="margin-bottom:24px;padding:16px 20px;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
    <h4 style="margin:0;font-size:14px;font-weight:700;">Lead Pipeline</h4>
    <a href="<?= SITE_URL ?>/admin/enquiries.php" style="font-size:12px;font-weight:600;">View All <i class="fa-solid fa-arrow-right"></i></a>
  </div>
  <div style="display:flex;gap:4px;align-items:stretch;">
    <?php
    $barColors = ['#f79009','#98a2b3','#175cd3','#3538cd','#9b5de5','#e07c00','#12b76a','#b42318'];
    $barLabels = ['New','Read','Contacted','Qualified','Proposal','Negotiation','Won','Lost'];
    $maxVal = max(1, max($crmStats));
    foreach ($statusList as $i => $st):
        $pct = $totalLeads > 0 ? round(($crmStats[$st] / $totalLeads) * 100) : 0;
        $barH = max(4, ($crmStats[$st] / $maxVal) * 80);
    ?>
    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;">
      <div style="font-size:15px;font-weight:800;color:var(--adm-text);"><?= $crmStats[$st] ?></div>
      <div style="width:100%;height:100px;display:flex;align-items:flex-end;justify-content:center;background:#f4f5f7;border-radius:6px;position:relative;">
        <div style="width:70%;height:<?= $barH ?>px;border-radius:4px 4px 0 0;background:<?= $barColors[$i] ?>;transition:height 0.3s;"></div>
      </div>
      <div style="font-size:10px;font-weight:600;color:var(--adm-text-muted);text-align:center;"><?= $barLabels[$i] ?></div>
      <div style="font-size:10px;color:var(--adm-text-muted);"><?= $pct ?>%</div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="card-box p-0 mb-6">
  <div class="liquid-card-header">
    <h3>Recent Packages</h3>
    <a href="<?= SITE_URL ?>/admin/package-create.php" class="btn-primary-admin" style="font-size:13px;">
      <i class="fa-solid fa-plus"></i> Add New
    </a>
  </div>
  <table class="admin-table">
    <thead>
      <tr>
        <th>Package</th>
        <th>Destination</th>
        <th>Duration</th>
        <th>Flags</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($recentPkgs as $pkg): ?>
      <tr>
        <td class="cell-title"><?= e($pkg['title']) ?></td>
        <td><?= e($pkg['destination'] ?? '—') ?></td>
        <td><?= (int)$pkg['days'] ?> Days</td>
        <td>
          <?php if ($pkg['is_featured']): ?><span class="badge-flag">Featured</span><?php endif; ?>
          <?php if ($pkg['is_popular']): ?><span class="badge-flag">Popular</span><?php endif; ?>
          <?php if ($pkg['show_on_homepage']): ?><span class="badge-flag">Home</span><?php endif; ?>
        </td>
        <td>
          <?php if ($pkg['status'] === 'published'): ?>
            <span class="badge-pub">Published</span>
          <?php elseif ($pkg['status'] === 'draft'): ?>
            <span class="badge-draft">Draft</span>
          <?php else: ?>
            <span class="badge-arc">Archived</span>
          <?php endif; ?>
        </td>
        <td>
          <a href="<?= SITE_URL ?>/admin/package-edit.php?id=<?= $pkg['id'] ?>" class="btn-edit-admin">Edit</a>
          <a href="<?= SITE_URL ?>/package-details.php?slug=<?= e($pkg['slug']) ?>" target="_blank" class="btn-secondary-admin" style="font-size:11px;padding:6px 10px;margin-left:4px;">View</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($recentPkgs)): ?>
      <tr><td colspan="6" style="text-align:center;color:#6e7681;padding:32px;">No packages yet. <a href="<?= SITE_URL ?>/admin/package-create.php">Create your first package →</a></td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if (!empty($recentEnqs)): ?>
<div class="card-box p-0">
  <div class="liquid-card-header">
    <h3>Recent Leads</h3>
    <a href="<?= SITE_URL ?>/admin/enquiries.php" class="btn-primary-admin" style="font-size:13px;">
      <i class="fa-solid fa-envelope"></i> CRM Pipeline
    </a>
  </div>
  <table class="admin-table">
    <thead><tr><th>Name</th><th>Email</th><th>Package</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php
    $scLabels = ['new'=>'New','read'=>'Read','contacted'=>'Contacted','qualified'=>'Qualified','proposal_sent'=>'Proposal','negotiation'=>'Negotiation','converted'=>'Won','lost'=>'Lost'];
    $scColors = ['new'=>'#f79009','read'=>'#344054','contacted'=>'#175cd3','qualified'=>'#3538cd','proposal_sent'=>'#9b5de5','negotiation'=>'#e07c00','converted'=>'#027a48','lost'=>'#b42318'];
    $scBgs = ['new'=>'#fffaeb','read'=>'#f2f4f7','contacted'=>'#eff4ff','qualified'=>'#eef0ff','proposal_sent'=>'#f5efff','negotiation'=>'#fff3e0','converted'=>'#ecfdf3','lost'=>'#fef3f2'];?>
    <?php foreach ($recentEnqs as $enq): $st=$enq['status']; ?>
      <tr>
        <td class="cell-title"><?= e($enq['first_name'] . ' ' . $enq['last_name']) ?></td>
        <td><?= e($enq['email']) ?></td>
        <td><?= e($enq['package_title'] ?? '—') ?></td>
        <td><?= date('d M Y', strtotime($enq['created_at'])) ?></td>
        <td><span style="display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:600;background:<?= $scBgs[$st]??'#f2f4f7' ?>;color:<?= $scColors[$st]??'#344054' ?>;"><?= $scLabels[$st]??ucfirst($st) ?></span></td>
        <td><a href="<?= SITE_URL ?>/admin/enquiry-view.php?id=<?= (int)$enq['id'] ?>" class="btn-edit-admin">Open CRM</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
