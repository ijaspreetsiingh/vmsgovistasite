<?php
// Shared save logic for package create & edit
// $isEdit = true/false, $editId = int (for edit)
function savePackage(array $post, array $files, bool $isEdit = false, ?int $editId = null): int {
    $db = getDB();

    $action = $post['action'] ?? 'draft';
    $status = ($action === 'publish') ? 'published' : ($post['status'] ?? 'draft');

    // Build slug
    $slug = trim($post['slug'] ?? '');
    if (!$slug) $slug = makeSlug($post['title'] ?? 'package');
    $slug = uniqueSlug($slug, $editId);

    // Handle image uploads
    $mainImg  = $post['main_image_existing'] ?? null;
    $thumbImg = $post['thumbnail_image_existing'] ?? null;
    $bcImg    = $post['breadcrumb_image_existing'] ?? null;

    if (!empty($files['main_image']['name']))      $mainImg  = uploadImage($files['main_image'])      ?? $mainImg;
    if (!empty($files['thumbnail_image']['name'])) $thumbImg = uploadImage($files['thumbnail_image']) ?? $thumbImg;
    if (!empty($files['breadcrumb_image']['name'])) $bcImg   = uploadImage($files['breadcrumb_image']) ?? $bcImg;

    $data = [
        'title'            => trim($post['title']            ?? ''),
        'slug'             => $slug,
        'short_title'      => trim($post['short_title']      ?? '') ?: null,
        'short_desc'       => trim($post['short_desc']       ?? '') ?: null,
        'overview'         => trim($post['overview']         ?? '') ?: null,
        'category'         => trim($post['category']         ?? '') ?: null,
        'tour_type'        => trim($post['tour_type']        ?? '') ?: null,
        'destination'      => trim($post['destination']      ?? '') ?: null,
        'country'          => trim($post['country']          ?? '') ?: null,
        'city'             => trim($post['city']             ?? '') ?: null,
        'days'             => max(1, (int)($post['days']     ?? 1)),
        'nights'           => max(0, (int)($post['nights']  ?? 0)),
        'price_original'   => (float)($post['price_original']  ?? 0),
        'price_discounted' => $post['price_discounted'] !== '' ? (float)$post['price_discounted'] : null,
        'currency'         => $post['currency']          ?? 'USD',
        'price_label'      => trim($post['price_label']     ?? 'From'),
        'discount_pct'     => $post['discount_pct'] !== '' ? (int)$post['discount_pct'] : null,
        'price_per_adult'  => $post['price_per_adult']  !== '' ? (float)$post['price_per_adult']  : null,
        'price_per_child'  => $post['price_per_child']  !== '' ? (float)$post['price_per_child']  : null,
        'price_notes'      => trim($post['price_notes']      ?? '') ?: null,
        'transportation'   => trim($post['transportation']   ?? '') ?: null,
        'accommodation'    => trim($post['accommodation']    ?? '') ?: null,
        'max_altitude'     => trim($post['max_altitude']     ?? '') ?: null,
        'departure_from'   => trim($post['departure_from']   ?? '') ?: null,
        'best_season'      => trim($post['best_season']      ?? '') ?: null,
        'meals'            => trim($post['meals']            ?? '') ?: null,
        'language'         => trim($post['language']         ?? '') ?: null,
        'fitness_level'    => trim($post['fitness_level']    ?? '') ?: null,
        'group_size_min'   => $post['group_size_min'] !== '' ? (int)$post['group_size_min'] : null,
        'group_size_max'   => $post['group_size_max'] !== '' ? (int)$post['group_size_max'] : null,
        'min_age'          => $post['min_age'] !== '' ? (int)$post['min_age'] : null,
        'max_age'          => $post['max_age'] !== '' ? (int)$post['max_age'] : null,
        'rating'           => (float)($post['rating']        ?? 0),
        'review_count'     => (int)($post['review_count']   ?? 0),
        'map_embed'        => trim($post['map_embed']        ?? '') ?: null,
        'main_image'       => $mainImg,
        'thumbnail_image'  => $thumbImg,
        'breadcrumb_image' => $bcImg,
        'status'           => $status,
        'is_featured'      => isset($post['is_featured'])      ? 1 : 0,
        'is_popular'       => isset($post['is_popular'])       ? 1 : 0,
        'is_recommended'   => isset($post['is_recommended'])   ? 1 : 0,
        'show_on_homepage' => isset($post['show_on_homepage']) ? 1 : 0,
        'sort_order'       => (int)($post['sort_order'] ?? 0),
        'booking_cta_text' => trim($post['booking_cta_text'] ?? 'Check Availability'),
        'booking_cta_url'  => trim($post['booking_cta_url']  ?? '#'),
        'enquiry_cta_text' => trim($post['enquiry_cta_text'] ?? 'Send Enquiry'),
        'whatsapp_number'  => trim($post['whatsapp_number']  ?? '') ?: null,
    ];

    if ($isEdit && $editId) {
        $sets = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
        $vals = array_values($data);
        $vals[] = $editId;
        $db->prepare("UPDATE packages SET $sets WHERE id = ?")->execute($vals);
        $pkgId = $editId;
    } else {
        $cols = '`' . implode('`, `', array_keys($data)) . '`';
        $phs  = implode(', ', array_fill(0, count($data), '?'));
        $db->prepare("INSERT INTO packages ($cols) VALUES ($phs)")->execute(array_values($data));
        $pkgId = (int)$db->lastInsertId();
    }

    // Delete marked gallery images
    if (!empty($post['delete_images'])) {
        $ids = array_map('intval', $post['delete_images']);
        $phs = implode(',', array_fill(0, count($ids), '?'));
        $rows = fetchAll("SELECT image_path FROM package_images WHERE id IN ($phs) AND package_id = ?", array_merge($ids, [$pkgId]));
        foreach ($rows as $r) { @unlink(UPLOAD_DIR . 'packages/' . basename($r['image_path'])); }
        $db->prepare("DELETE FROM package_images WHERE id IN ($phs) AND package_id = ?")->execute(array_merge($ids, [$pkgId]));
    }

    // Upload new gallery images
    if (!empty($files['gallery_images']['name'][0])) {
        $count  = count($files['gallery_images']['name']);
        $maxSrt = (int)($db->prepare("SELECT COALESCE(MAX(sort_order),0) FROM package_images WHERE package_id=?")->execute([$pkgId]) ? $db->query("SELECT COALESCE(MAX(sort_order),0) FROM package_images WHERE package_id=$pkgId")->fetchColumn() : 0);
        for ($gi = 0; $gi < $count; $gi++) {
            $singleFile = [
                'name'     => $files['gallery_images']['name'][$gi],
                'type'     => $files['gallery_images']['type'][$gi],
                'tmp_name' => $files['gallery_images']['tmp_name'][$gi],
                'error'    => $files['gallery_images']['error'][$gi],
                'size'     => $files['gallery_images']['size'][$gi],
            ];
            if ($singleFile['error'] !== UPLOAD_ERR_OK) continue;
            $path = uploadImage($singleFile);
            if ($path) {
                $db->prepare("INSERT INTO package_images (package_id, image_path, sort_order) VALUES (?,?,?)")
                   ->execute([$pkgId, $path, ++$maxSrt]);
            }
        }
    }

    // Save itinerary
    $db->prepare("DELETE FROM package_itinerary WHERE package_id=?")->execute([$pkgId]);
    $itinDays  = $post['itin_day']        ?? [];
    $itinTitles= $post['itin_title']      ?? [];
    $itinDescs = $post['itin_desc']       ?? [];
    $itinActs  = $post['itin_activities'] ?? [];
    $itinMeals = $post['itin_meals']      ?? [];
    $iStmt = $db->prepare("INSERT INTO package_itinerary (package_id,day_number,title,description,activities,meals,sort_order) VALUES (?,?,?,?,?,?,?)");
    foreach ($itinTitles as $i => $title) {
        if (trim($title) === '') continue;
        $iStmt->execute([$pkgId, (int)($itinDays[$i]??1), trim($title), trim($itinDescs[$i]??''), trim($itinActs[$i]??''), trim($itinMeals[$i]??''), $i]);
    }

    // Save inclusions
    $db->prepare("DELETE FROM package_inclusions WHERE package_id=?")->execute([$pkgId]);
    $incStmt = $db->prepare("INSERT INTO package_inclusions (package_id,item,sort_order) VALUES (?,?,?)");
    foreach (($post['inclusions'] ?? []) as $i => $item) {
        if (trim($item) === '') continue;
        $incStmt->execute([$pkgId, trim($item), $i]);
    }

    // Save exclusions
    $db->prepare("DELETE FROM package_exclusions WHERE package_id=?")->execute([$pkgId]);
    $excStmt = $db->prepare("INSERT INTO package_exclusions (package_id,item,sort_order) VALUES (?,?,?)");
    foreach (($post['exclusions'] ?? []) as $i => $item) {
        if (trim($item) === '') continue;
        $excStmt->execute([$pkgId, trim($item), $i]);
    }

    // Save highlights
    $db->prepare("DELETE FROM package_highlights WHERE package_id=?")->execute([$pkgId]);
    $hiStmt = $db->prepare("INSERT INTO package_highlights (package_id,item,sort_order) VALUES (?,?,?)");
    foreach (($post['highlights'] ?? []) as $i => $item) {
        if (trim($item) === '') continue;
        $hiStmt->execute([$pkgId, trim($item), $i]);
    }

    // Save FAQs
    $db->prepare("DELETE FROM package_faqs WHERE package_id=?")->execute([$pkgId]);
    $faqQs = $post['faq_question'] ?? [];
    $faqAs = $post['faq_answer']   ?? [];
    $faqStmt = $db->prepare("INSERT INTO package_faqs (package_id,question,answer,sort_order) VALUES (?,?,?,?)");
    foreach ($faqQs as $i => $q) {
        if (trim($q) === '') continue;
        $faqStmt->execute([$pkgId, trim($q), trim($faqAs[$i]??''), $i]);
    }

    // Save extra info
    $db->prepare("DELETE FROM package_info WHERE package_id=?")->execute([$pkgId]);
    $infoTypes    = $post['info_type']    ?? [];
    $infoTitles   = $post['info_title']   ?? [];
    $infoContents = $post['info_content'] ?? [];
    $infoStmt = $db->prepare("INSERT INTO package_info (package_id,info_type,title,content,sort_order) VALUES (?,?,?,?,?)");
    foreach ($infoTypes as $i => $type) {
        if (trim($infoContents[$i]??'') === '') continue;
        $infoStmt->execute([$pkgId, trim($type), trim($infoTitles[$i]??''), trim($infoContents[$i]), $i]);
    }

    return $pkgId;
}
