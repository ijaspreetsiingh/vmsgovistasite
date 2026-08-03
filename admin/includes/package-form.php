<?php
// Shared form for create + edit. $pkg = existing data (edit) or [] (create)
$p = $pkg ?? [];
$v = function($key, $default='') use ($p) { return isset($p[$key]) ? htmlspecialchars((string)$p[$key], ENT_QUOTES) : $default; };
?>
<form method="POST" enctype="multipart/form-data" id="packageForm">
<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

<!-- ===== TAB NAV ===== -->
<div class="pkg-tab-nav">
<?php
$tabs = [
  'basic'      => ['fa-circle-info',  'Basic Info'],
  'images'     => ['fa-images',       'Images'],
  'details'    => ['fa-list-check',   'Tour Details'],
  'overview'   => ['fa-file-lines',   'Overview'],
  'itinerary'  => ['fa-map-location', 'Itinerary'],
  'costs'      => ['fa-dollar-sign',  'Cost & Pricing'],
  'faqs'       => ['fa-circle-question', 'FAQs'],
  'extra'      => ['fa-gear',         'Extra Info'],
  'settings'   => ['fa-sliders',      'Settings'],
];
foreach ($tabs as $tid => [$icon, $label]):
?>
<button type="button" class="tab-btn<?= $tid === 'basic' ? ' active' : '' ?>" data-tab="tab-<?= $tid ?>">
  <i class="fa-solid <?= $icon ?>"></i><?= $label ?>
</button>
<?php endforeach; ?>
</div>

<!-- ===== TAB: BASIC INFO ===== -->
<div class="tab-pane active" id="tab-basic">
<div class="form-section">
  <div class="form-section-title"><i class="fa-solid fa-circle-info me-2"></i>Basic Package Information</div>
  <div class="form-group">
    <label>Package Title <span class="req">*</span></label>
    <input type="text" name="title" class="form-control-admin" value="<?= $v('title') ?>" required placeholder="e.g. The Luxury Island Hopper">
  </div>
  <div class="form-row">
    <div class="form-group">
      <label>Slug (URL) <span class="req">*</span></label>
      <input type="text" name="slug" id="slugField" class="form-control-admin" value="<?= $v('slug') ?>" required placeholder="luxury-island-hopper">
      <div class="form-hint">Leave blank to auto-generate from title</div>
    </div>
    <div class="form-group">
      <label>Short Title</label>
      <input type="text" name="short_title" class="form-control-admin" value="<?= $v('short_title') ?>" placeholder="Island Hopper">
    </div>
  </div>
  <div class="form-group">
    <label>Short Description</label>
    <textarea name="short_desc" class="form-control-admin" rows="3" placeholder="Brief description shown on cards..."><?= $v('short_desc') ?></textarea>
  </div>
  <?php
  $catOpts   = getSettingsOptions('category');
  $tourOpts  = getSettingsOptions('tour_type');
  $destOpts  = getSettingsOptions('destination');
  $countryOpts = getSettingsOptions('country');
  $cityOpts  = getSettingsOptions('city');
  ?>
  <div class="form-row-3">
    <div class="form-group">
      <label>Category</label>
      <select name="category" class="form-control-admin">
        <option value="">— Select —</option>
        <?php foreach ($catOpts as $val): ?>
          <option value="<?= e($val) ?>" <?= $v('category')===$val?'selected':'' ?>><?= e($val) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="form-hint">Manage in <a href="<?= SITE_URL ?>/admin/settings.php" style="color:#58a6ff;">Package Settings</a></div>
    </div>
    <div class="form-group">
      <label>Tour Type</label>
      <select name="tour_type" class="form-control-admin">
        <option value="">— Select —</option>
        <?php foreach ($tourOpts as $val): ?>
          <option value="<?= e($val) ?>" <?= $v('tour_type')===$val?'selected':'' ?>><?= e($val) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="form-hint">Manage in <a href="<?= SITE_URL ?>/admin/settings.php" style="color:#58a6ff;">Package Settings</a></div>
    </div>
    <div class="form-group">
      <label>Destination</label>
      <select name="destination" class="form-control-admin">
        <option value="">— Select —</option>
        <?php foreach ($destOpts as $val): ?>
          <option value="<?= e($val) ?>" <?= $v('destination')===$val?'selected':'' ?>><?= e($val) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="form-hint">Manage in <a href="<?= SITE_URL ?>/admin/settings.php" style="color:#58a6ff;">Package Settings</a></div>
    </div>
  </div>
  <div class="form-row-3">
    <div class="form-group">
      <label>Country</label>
      <select name="country" class="form-control-admin">
        <option value="">— Select —</option>
        <?php foreach ($countryOpts as $val): ?>
          <option value="<?= e($val) ?>" <?= $v('country')===$val?'selected':'' ?>><?= e($val) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="form-hint">Manage in <a href="<?= SITE_URL ?>/admin/settings.php" style="color:#58a6ff;">Package Settings</a></div>
    </div>
    <div class="form-group">
      <label>City</label>
      <select name="city" class="form-control-admin">
        <option value="">— Select —</option>
        <?php foreach ($cityOpts as $val): ?>
          <option value="<?= e($val) ?>" <?= $v('city')===$val?'selected':'' ?>><?= e($val) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="form-hint">Manage in <a href="<?= SITE_URL ?>/admin/settings.php" style="color:#58a6ff;">Package Settings</a></div>
    </div>
    <div class="form-group">
      <label>Sort Order</label>
      <input type="number" name="sort_order" class="form-control-admin" value="<?= $v('sort_order','0') ?>" min="0">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label>Number of Days <span class="req">*</span></label>
      <input type="number" name="days" class="form-control-admin" value="<?= $v('days','1') ?>" min="1" required>
    </div>
    <div class="form-group">
      <label>Number of Nights</label>
      <input type="number" name="nights" class="form-control-admin" value="<?= $v('nights','0') ?>" min="0">
    </div>
  </div>
</div>
</div>

<!-- ===== TAB: IMAGES ===== -->
<div class="tab-pane" id="tab-images">
<div class="form-section">
  <div class="form-section-title"><i class="fa-solid fa-images me-2"></i>Package Images</div>
  <div class="form-row">
    <div class="form-group">
      <label>Main Image (Hero)</label>
      <?php if (!empty($p['main_image'])): ?>
        <img src="<?= e(packageImageUrl($p['main_image'])) ?>" class="img-preview" id="preview-main"><br>
      <?php else: ?>
        <img src="" id="preview-main" class="img-preview" style="display:none;">
      <?php endif; ?>
      <input type="file" name="main_image" class="form-control-admin" accept="image/*" data-preview="preview-main">
      <input type="hidden" name="main_image_existing" value="<?= $v('main_image') ?>">
    </div>
    <div class="form-group">
      <label>Thumbnail Image</label>
      <?php if (!empty($p['thumbnail_image'])): ?>
        <img src="<?= e(packageImageUrl($p['thumbnail_image'])) ?>" class="img-preview" id="preview-thumb"><br>
      <?php else: ?>
        <img src="" id="preview-thumb" class="img-preview" style="display:none;">
      <?php endif; ?>
      <input type="file" name="thumbnail_image" class="form-control-admin" accept="image/*" data-preview="preview-thumb">
      <input type="hidden" name="thumbnail_image_existing" value="<?= $v('thumbnail_image') ?>">
    </div>
  </div>
  <div class="form-group">
    <label>Breadcrumb / Header Background Image</label>
    <?php if (!empty($p['breadcrumb_image'])): ?>
      <img src="<?= e(packageImageUrl($p['breadcrumb_image'])) ?>" class="img-preview" id="preview-breadcrumb"><br>
    <?php else: ?>
      <img src="" id="preview-breadcrumb" class="img-preview" style="display:none;">
    <?php endif; ?>
    <input type="file" name="breadcrumb_image" class="form-control-admin" accept="image/*" data-preview="preview-breadcrumb">
    <input type="hidden" name="breadcrumb_image_existing" value="<?= $v('breadcrumb_image') ?>">
  </div>
  <div class="form-group">
    <label>Gallery Images (select multiple)</label>
    <input type="file" name="gallery_images[]" class="form-control-admin" accept="image/*" multiple>
    <div class="form-hint">Hold Ctrl / Cmd to select multiple images</div>
  </div>
  <?php if (!empty($p['images'])): ?>
  <div>
    <label style="display:block;color:#c9d1d9;font-size:13px;font-weight:500;margin-bottom:10px;">Existing Gallery Images</label>
    <div style="display:flex;flex-wrap:wrap;gap:10px;">
    <?php foreach ($p['images'] as $img): ?>
      <div style="position:relative;display:inline-block;">
        <img src="<?= e(packageImageUrl($img['image_path'])) ?>" style="width:90px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #30363d;">
        <label style="display:flex;align-items:center;gap:4px;font-size:11px;color:#f85149;margin-top:3px;cursor:pointer;">
          <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>"> Delete
        </label>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
</div>

<!-- ===== TAB: TOUR DETAILS ===== -->
<div class="tab-pane" id="tab-details">
<div class="form-section">
  <div class="form-section-title"><i class="fa-solid fa-list-check me-2"></i>Tour Detail Fields (shown as feature icons on details page)</div>
  <div class="form-row">
    <div class="form-group">
      <label>Transportation</label>
      <input type="text" name="transportation" class="form-control-admin" value="<?= $v('transportation') ?>" placeholder="e.g. Bus, Airlines">
    </div>
    <div class="form-group">
      <label>Accommodation</label>
      <input type="text" name="accommodation" class="form-control-admin" value="<?= $v('accommodation') ?>" placeholder="e.g. 5 Stars Hotels">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label>Maximum Altitude</label>
      <input type="text" name="max_altitude" class="form-control-admin" value="<?= $v('max_altitude') ?>" placeholder="e.g. 5,416 metres">
    </div>
    <div class="form-group">
      <label>Departure From</label>
      <input type="text" name="departure_from" class="form-control-admin" value="<?= $v('departure_from') ?>" placeholder="e.g. Kathmandu Airport">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label>Best Season</label>
      <input type="text" name="best_season" class="form-control-admin" value="<?= $v('best_season') ?>" placeholder="e.g. Feb To May">
    </div>
    <div class="form-group">
      <label>Meals</label>
      <input type="text" name="meals" class="form-control-admin" value="<?= $v('meals') ?>" placeholder="e.g. Meals Included">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label>Language</label>
      <input type="text" name="language" class="form-control-admin" value="<?= $v('language') ?>" placeholder="e.g. EN, ES, FR">
    </div>
    <div class="form-group">
      <label>Fitness Level</label>
      <input type="text" name="fitness_level" class="form-control-admin" value="<?= $v('fitness_level') ?>" placeholder="e.g. Moderate to High">
    </div>
  </div>
  <div class="form-row-3">
    <div class="form-group">
      <label>Group Size Min</label>
      <input type="number" name="group_size_min" class="form-control-admin" value="<?= $v('group_size_min') ?>" min="0" placeholder="7">
    </div>
    <div class="form-group">
      <label>Group Size Max</label>
      <input type="number" name="group_size_max" class="form-control-admin" value="<?= $v('group_size_max') ?>" min="0" placeholder="14">
    </div>
    <div class="form-group">
      <label>Rating (0.0–5.0)</label>
      <input type="number" name="rating" class="form-control-admin" value="<?= $v('rating','5.0') ?>" min="0" max="5" step="0.1" placeholder="4.8">
    </div>
  </div>
  <div class="form-row-3">
    <div class="form-group">
      <label>Min Age</label>
      <input type="number" name="min_age" class="form-control-admin" value="<?= $v('min_age') ?>" placeholder="14">
    </div>
    <div class="form-group">
      <label>Max Age</label>
      <input type="number" name="max_age" class="form-control-admin" value="<?= $v('max_age') ?>" placeholder="60">
    </div>
    <div class="form-group">
      <label>Review Count</label>
      <input type="number" name="review_count" class="form-control-admin" value="<?= $v('review_count','0') ?>" min="0">
    </div>
  </div>
  <div class="form-group">
    <label>Google Maps Embed URL</label>
    <input type="text" name="map_embed" class="form-control-admin" value="<?= $v('map_embed') ?>" placeholder="https://www.google.com/maps/embed?pb=...">
    <div class="form-hint">Paste the src URL from Google Maps embed code</div>
  </div>
</div>
</div>

<!-- ===== TAB: OVERVIEW ===== -->
<div class="tab-pane" id="tab-overview">
<div class="form-section">
  <div class="form-section-title"><i class="fa-solid fa-file-lines me-2"></i>Overview & Description</div>
  <div class="form-group">
    <label>Full Overview / Description</label>
    <textarea name="overview" class="form-control-admin" rows="10" placeholder="Complete package overview shown in the Overview tab..."><?= $v('overview') ?></textarea>
  </div>
</div>
<div class="form-section">
  <div class="form-section-title">Highlights</div>
  <div id="highlights-wrapper">
  <?php foreach (($p['highlights'] ?? []) as $i => $hi): ?>
    <div class="repeater-item">
      <button type="button" class="remove-btn">Remove</button>
      <input type="text" name="highlights[]" class="form-control-admin" value="<?= htmlspecialchars($hi['item']) ?>" placeholder="e.g. Duration: 10 Days / 9 Nights">
    </div>
  <?php endforeach; ?>
  </div>
  <template id="highlights-wrapper-template">
    <input type="text" name="highlights[]" class="form-control-admin" placeholder="e.g. Professional Local Guide Included">
  </template>
  <button type="button" class="add-repeater-btn" data-target="highlights-wrapper">
    <i class="fa-solid fa-plus me-1"></i> Add Highlight
  </button>
</div>
</div>

<!-- ===== TAB: ITINERARY ===== -->
<div class="tab-pane" id="tab-itinerary">
<div class="form-section">
  <div class="form-section-title"><i class="fa-solid fa-map-location me-2"></i>Itinerary Days</div>
  <div id="itinerary-wrapper">
  <?php foreach (($p['itinerary'] ?? []) as $i => $day): ?>
    <div class="repeater-item">
      <button type="button" class="remove-btn">Remove</button>
      <div class="form-row" style="margin-bottom:10px;">
        <div class="form-group" style="margin-bottom:0;">
          <label>Day Number</label>
          <input type="number" name="itin_day[]" class="form-control-admin" value="<?= (int)$day['day_number'] ?>" min="1">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label>Day Title</label>
          <input type="text" name="itin_title[]" class="form-control-admin" value="<?= htmlspecialchars($day['title']) ?>" placeholder="e.g. Day 1 – Arrival In Kathmandu">
        </div>
      </div>
      <div class="form-group" style="margin-bottom:8px;">
        <label>Description</label>
        <textarea name="itin_desc[]" class="form-control-admin" rows="3"><?= htmlspecialchars($day['description'] ?? '') ?></textarea>
      </div>
      <div class="form-row">
        <div class="form-group" style="margin-bottom:0;">
          <label>Activities</label>
          <input type="text" name="itin_activities[]" class="form-control-admin" value="<?= htmlspecialchars($day['activities'] ?? '') ?>" placeholder="e.g. Sightseeing, Hiking">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label>Meals</label>
          <input type="text" name="itin_meals[]" class="form-control-admin" value="<?= htmlspecialchars($day['meals'] ?? '') ?>" placeholder="e.g. Breakfast, Dinner">
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
  <template id="itinerary-wrapper-template">
    <div class="form-row" style="margin-bottom:10px;">
      <div class="form-group" style="margin-bottom:0;">
        <label>Day Number</label>
        <input type="number" name="itin_day[]" class="form-control-admin" value="1" min="1">
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label>Day Title</label>
        <input type="text" name="itin_title[]" class="form-control-admin" placeholder="e.g. Day 1 – Arrival">
      </div>
    </div>
    <div class="form-group" style="margin-bottom:8px;">
      <label>Description</label>
      <textarea name="itin_desc[]" class="form-control-admin" rows="3"></textarea>
    </div>
    <div class="form-row">
      <div class="form-group" style="margin-bottom:0;">
        <label>Activities</label>
        <input type="text" name="itin_activities[]" class="form-control-admin" placeholder="e.g. City tour">
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label>Meals</label>
        <input type="text" name="itin_meals[]" class="form-control-admin" placeholder="e.g. Breakfast">
      </div>
    </div>
  </template>
  <button type="button" class="add-repeater-btn" data-target="itinerary-wrapper">
    <i class="fa-solid fa-plus me-1"></i> Add Day
  </button>
</div>
</div>

<!-- ===== TAB: COST & PRICING ===== -->
<div class="tab-pane" id="tab-costs">
<div class="form-section">
  <div class="form-section-title"><i class="fa-solid fa-dollar-sign me-2"></i>Pricing</div>
  <div class="form-row">
    <div class="form-group">
      <label>Original Price <span class="req">*</span></label>
      <input type="number" name="price_original" class="form-control-admin" value="<?= $v('price_original','0') ?>" step="0.01" min="0" required placeholder="699">
    </div>
    <div class="form-group">
      <label>Discounted Price</label>
      <input type="number" name="price_discounted" class="form-control-admin" value="<?= $v('price_discounted') ?>" step="0.01" min="0" placeholder="499">
    </div>
  </div>
  <div class="form-row-3">
    <div class="form-group">
      <label>Discount % (shown as badge)</label>
      <input type="number" name="discount_pct" class="form-control-admin" value="<?= $v('discount_pct') ?>" min="0" max="100" placeholder="20">
    </div>
    <div class="form-group">
      <label>Price Per Adult</label>
      <input type="number" name="price_per_adult" class="form-control-admin" value="<?= $v('price_per_adult') ?>" step="0.01" min="0" placeholder="499">
    </div>
    <div class="form-group">
      <label>Price Per Child</label>
      <input type="number" name="price_per_child" class="form-control-admin" value="<?= $v('price_per_child') ?>" step="0.01" min="0" placeholder="349">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label>Currency</label>
      <select name="currency" class="form-control-admin">
        <?php foreach (['USD','EUR','GBP','AED','INR','AUD'] as $c): ?>
          <option value="<?= $c ?>" <?= $v('currency','USD')===$c?'selected':'' ?>><?= $c ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Price Label</label>
      <input type="text" name="price_label" class="form-control-admin" value="<?= $v('price_label','From') ?>" placeholder="From">
    </div>
  </div>
  <div class="form-group">
    <label>Price Notes</label>
    <textarea name="price_notes" class="form-control-admin" rows="2" placeholder="e.g. Price per person, based on double occupancy"><?= $v('price_notes') ?></textarea>
  </div>
</div>
<div class="form-section">
  <div class="form-section-title">Inclusions (What's Included)</div>
  <div id="inclusions-wrapper">
  <?php foreach (($p['inclusions'] ?? []) as $inc): ?>
    <div class="repeater-item">
      <button type="button" class="remove-btn">Remove</button>
      <input type="text" name="inclusions[]" class="form-control-admin" value="<?= htmlspecialchars($inc['item']) ?>" placeholder="e.g. Hotel accommodation">
    </div>
  <?php endforeach; ?>
  </div>
  <template id="inclusions-wrapper-template">
    <input type="text" name="inclusions[]" class="form-control-admin" placeholder="e.g. Airport transfer">
  </template>
  <button type="button" class="add-repeater-btn" data-target="inclusions-wrapper">
    <i class="fa-solid fa-plus me-1"></i> Add Inclusion
  </button>
</div>
<div class="form-section">
  <div class="form-section-title">Exclusions (Not Included)</div>
  <div id="exclusions-wrapper">
  <?php foreach (($p['exclusions'] ?? []) as $exc): ?>
    <div class="repeater-item">
      <button type="button" class="remove-btn">Remove</button>
      <input type="text" name="exclusions[]" class="form-control-admin" value="<?= htmlspecialchars($exc['item']) ?>" placeholder="e.g. International flights">
    </div>
  <?php endforeach; ?>
  </div>
  <template id="exclusions-wrapper-template">
    <input type="text" name="exclusions[]" class="form-control-admin" placeholder="e.g. Travel insurance">
  </template>
  <button type="button" class="add-repeater-btn" data-target="exclusions-wrapper">
    <i class="fa-solid fa-plus me-1"></i> Add Exclusion
  </button>
</div>
</div>

<!-- ===== TAB: FAQs ===== -->
<div class="tab-pane" id="tab-faqs">
<div class="form-section">
  <div class="form-section-title"><i class="fa-solid fa-circle-question me-2"></i>Frequently Asked Questions</div>
  <div id="faqs-wrapper">
  <?php foreach (($p['faqs'] ?? []) as $faq): ?>
    <div class="repeater-item">
      <button type="button" class="remove-btn">Remove</button>
      <div class="form-group" style="margin-bottom:8px;">
        <label>Question</label>
        <input type="text" name="faq_question[]" class="form-control-admin" value="<?= htmlspecialchars($faq['question']) ?>" placeholder="e.g. What is the difficulty level?">
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label>Answer</label>
        <textarea name="faq_answer[]" class="form-control-admin" rows="3"><?= htmlspecialchars($faq['answer']) ?></textarea>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
  <template id="faqs-wrapper-template">
    <div class="form-group" style="margin-bottom:8px;">
      <label>Question</label>
      <input type="text" name="faq_question[]" class="form-control-admin" placeholder="e.g. Do I need travel insurance?">
    </div>
    <div class="form-group" style="margin-bottom:0;">
      <label>Answer</label>
      <textarea name="faq_answer[]" class="form-control-admin" rows="3"></textarea>
    </div>
  </template>
  <button type="button" class="add-repeater-btn" data-target="faqs-wrapper">
    <i class="fa-solid fa-plus me-1"></i> Add FAQ
  </button>
</div>
</div>

<!-- ===== TAB: EXTRA INFO ===== -->
<div class="tab-pane" id="tab-extra">
<div class="form-section">
  <div class="form-section-title"><i class="fa-solid fa-gear me-2"></i>Important Information (Cancellation, Visa, Notes, Terms)</div>
  <div id="info-wrapper">
  <?php foreach (($p['info'] ?? []) as $inf): ?>
    <div class="repeater-item">
      <button type="button" class="remove-btn">Remove</button>
      <div class="form-row" style="margin-bottom:10px;">
        <div class="form-group" style="margin-bottom:0;">
          <label>Type</label>
          <select name="info_type[]" class="form-control-admin">
            <?php foreach (['important_notes','cancellation_policy','visa_info','payment_policy','terms_conditions','safety_info','documents_required','other'] as $t): ?>
              <option value="<?= $t ?>" <?= ($inf['info_type']??'')===$t?'selected':'' ?>><?= ucwords(str_replace('_',' ',$t)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label>Title (optional)</label>
          <input type="text" name="info_title[]" class="form-control-admin" value="<?= htmlspecialchars($inf['title'] ?? '') ?>" placeholder="e.g. Cancellation Policy">
        </div>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label>Content</label>
        <textarea name="info_content[]" class="form-control-admin" rows="4"><?= htmlspecialchars($inf['content'] ?? '') ?></textarea>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
  <template id="info-wrapper-template">
    <div class="form-row" style="margin-bottom:10px;">
      <div class="form-group" style="margin-bottom:0;">
        <label>Type</label>
        <select name="info_type[]" class="form-control-admin">
          <option value="important_notes">Important Notes</option>
          <option value="cancellation_policy">Cancellation Policy</option>
          <option value="visa_info">Visa Information</option>
          <option value="payment_policy">Payment Policy</option>
          <option value="terms_conditions">Terms & Conditions</option>
          <option value="safety_info">Safety Information</option>
          <option value="documents_required">Documents Required</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label>Title (optional)</label>
        <input type="text" name="info_title[]" class="form-control-admin" placeholder="e.g. Cancellation Policy">
      </div>
    </div>
    <div class="form-group" style="margin-bottom:0;">
      <label>Content</label>
      <textarea name="info_content[]" class="form-control-admin" rows="4"></textarea>
    </div>
  </template>
  <button type="button" class="add-repeater-btn" data-target="info-wrapper">
    <i class="fa-solid fa-plus me-1"></i> Add Info Section
  </button>
</div>
</div>

<!-- ===== TAB: SETTINGS ===== -->
<div class="tab-pane" id="tab-settings">
<div class="form-section">
  <div class="form-section-title"><i class="fa-solid fa-sliders me-2"></i>Visibility & Status</div>
  <div class="form-row" style="margin-bottom:16px;">
    <div class="form-group">
      <label>Status <span class="req">*</span></label>
      <select name="status" class="form-control-admin">
        <option value="draft"     <?= $v('status','draft')==='draft'?'selected':'' ?>>Draft</option>
        <option value="published" <?= $v('status')==='published'?'selected':'' ?>>Published</option>
        <option value="archived"  <?= $v('status')==='archived'?'selected':'' ?>>Archived</option>
      </select>
    </div>
  </div>
  <div class="check-group" style="margin-bottom:20px;">
    <label class="check-label">
      <input type="checkbox" name="is_featured" value="1" <?= !empty($p['is_featured'])?'checked':'' ?>>
      <span>Featured Package</span>
    </label>
    <label class="check-label">
      <input type="checkbox" name="is_popular" value="1" <?= !empty($p['is_popular'])?'checked':'' ?>>
      <span>Popular Package</span>
    </label>
    <label class="check-label">
      <input type="checkbox" name="is_recommended" value="1" <?= !empty($p['is_recommended'])?'checked':'' ?>>
      <span>Recommended</span>
    </label>
    <label class="check-label">
      <input type="checkbox" name="show_on_homepage" value="1" <?= !empty($p['show_on_homepage'])?'checked':'' ?>>
      <span>Show on Homepage</span>
    </label>
  </div>
</div>
<div class="form-section">
  <div class="form-section-title">Booking CTAs</div>
  <div class="form-row">
    <div class="form-group">
      <label>Booking Button Text</label>
      <input type="text" name="booking_cta_text" class="form-control-admin" value="<?= $v('booking_cta_text','Check Availability') ?>">
    </div>
    <div class="form-group">
      <label>Booking Button URL</label>
      <input type="text" name="booking_cta_url" class="form-control-admin" value="<?= $v('booking_cta_url','#') ?>">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label>Enquiry Button Text</label>
      <input type="text" name="enquiry_cta_text" class="form-control-admin" value="<?= $v('enquiry_cta_text','Send Enquiry') ?>">
    </div>
    <div class="form-group">
      <label>WhatsApp Number</label>
      <input type="text" name="whatsapp_number" class="form-control-admin" value="<?= $v('whatsapp_number') ?>" placeholder="+1234567890">
    </div>
  </div>
</div>
</div>

<!-- ===== SUBMIT BAR ===== -->
<div class="pkg-form-submit">
  <button type="submit" name="action" value="publish" class="btn-primary-admin" style="padding:12px 28px;">
    <i class="fa-solid fa-cloud-arrow-up me-2"></i>Save & Publish
  </button>
  <button type="submit" name="action" value="draft" class="btn-secondary-admin" style="padding:12px 24px;">
    <i class="fa-solid fa-floppy-disk me-2"></i>Save as Draft
  </button>
  <a href="<?= SITE_URL ?>/admin/packages.php" class="btn-secondary-admin" style="padding:11px 20px;text-decoration:none;">Cancel</a>
</div>
</form>

<script>
(function(){
  // Tab switching
  var tabs   = document.querySelectorAll('.tab-btn');
  var panes  = document.querySelectorAll('.tab-pane');
  tabs.forEach(function(btn) {
    btn.addEventListener('click', function() {
      var target = btn.dataset.tab;
      panes.forEach(function(p){ p.classList.remove('active'); });
      tabs.forEach(function(b){ b.classList.remove('active'); });
      var pane = document.getElementById(target);
      if (pane) { pane.classList.add('active'); }
      btn.classList.add('active');
    });
  });

  // Auto-generate slug from title
  var titleInput = document.querySelector('input[name=title]');
  var slugField  = document.getElementById('slugField');
  if (titleInput && slugField) {
    titleInput.addEventListener('input', function() {
      if (!slugField.dataset.manual) {
        slugField.value = titleInput.value
          .toLowerCase().trim()
          .replace(/[^a-z0-9\s-]/g,'')
          .replace(/[\s-]+/g,'-')
          .replace(/^-+|-+$/g,'');
      }
    });
    slugField.addEventListener('input', function(){ slugField.dataset.manual = '1'; });
  }
})();
</script>
