<?php
require_once __DIR__ . '/../config/db.php';

// ── Strict email validation ────────────────────────────────────
function validateEmailStrict(string $email): array {
    $email = trim($email);
    
    // Basic format check
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'message' => 'Please enter a valid email address.'];
    }
    
    $domain = strtolower(substr(strrchr($email, '@'), 1));
    
    // Block disposable/temporary email domains
    $disposableDomains = [
        'mailinator.com', 'guerrillamail.com', '10minutemail.com', 'tempmail.com',
        'throwaway.email', 'fakeinbox.com', 'trashmail.com', 'yopmail.com',
        'maildrop.cc', 'dispostable.com', 'getnada.com', 'temp-mail.org',
        'mailnesia.com', 'spamgourmet.com', 'mintemail.com', 'spambob.com',
        'spamcowboy.com', 'spamhole.com', 'spamhere.com', 'spamspot.com',
        'bccto.me', 'chacuo.net', 'ddcrew.com', 'emailondeck.com',
        'emailmiser.com', 'fakemailgenerator.com', 'incognitomail.com',
        'lazyinbox.com', 'lukecarriere.com', 'mailcatch.com', 'mailforspam.com',
        'mailmetrash.com', 'moakt.com', 'mytrashmail.com', 'nospam.ze.tc',
        'objectmail.com', 'ordinaryamerican.net', 'pookmail.com', 'proxymail.eu',
        'quickinbox.com', 'rcpt.at', 'recode.me', 'spam4.me', 'spamavert.com',
        'spamcannon.com', 'spamcero.com', 'spamcon.org', 'spamday.com',
        'spamex.com', 'spamfree24.com', 'spamfree24.de', 'spamfree24.eu',
        'spamfree24.net', 'spamfree24.org', 'spamgourmet.net', 'spamherelots.com',
        'spamhereplease.com', 'spamobox.com', 'spamoff.de', 'spamspot.com',
        'spamstack.net', 'superrito.com', 'teleworm.com', 'tempalias.com',
        'tempemail.com', 'tempemail.net', 'tempinbox.com', 'tempmail.de',
        'tempmail.eu', 'tempmail.it', 'tempmail.net', 'tempmail.org',
        'tempmail24.com', 'temporaryemail.net', 'temporaryinbox.com',
        'thankyou2010.com', 'thisisnotmyrealemail.com', 'trash-mail.com',
        'trash2009.com', 'trashdevil.com', 'trashmail.at', 'trashmail.me',
        'trashymail.com', 'turual.com', 'twinmail.de', 'uggsrock.com',
        'wegwerfmail.de', 'wegwerfmail.net', 'wegwerfmail.org', 'whyspam.me',
        'willselfdestruct.com', 'winemaven.com', 'wronghead.com', 'wuzup.net',
        'xemaps.com', 'xents.com', 'xmaily.com', 'xoxy.net', 'yep.it',
        'yogamaven.com', 'yomail.info', 'yopmail.fr', 'yopmail.net',
        'yuurok.com', 'z1p.biz', 'zepp.dk', 'zoemail.net'
    ];
    
    if (in_array($domain, $disposableDomains, true)) {
        return ['valid' => false, 'message' => 'Temporary/disposable email addresses are not allowed. Please use a real email address.'];
    }
    
    // Block common fake patterns
    $fakePatterns = [
        '/^test@/', '/^fake@/', '/^dummy@/', '/^spam@/', '/^trash@/',
        '/^noreply@/', '/^no[-_]reply@/', '/^donotreply@/', '/^admin@/',
        '/^info@/', '/^support@/', '/^sales@/', '/^contact@/',
        '/^example@/', '/^user@/', '/^demo@/', '/^sample@/',
        '/@example\.(com|org|net)$/', '/@test\.(com|org|net)$/',
        '/@localhost/', '/@local/', '/@invalid/'
    ];
    
    foreach ($fakePatterns as $pattern) {
        if (preg_match($pattern, $email)) {
            return ['valid' => false, 'message' => 'Please use a real personal email address.'];
        }
    }
    
    // Optional: Check MX record exists (can be slow, disable if needed)
    // if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
    //     return ['valid' => false, 'message' => 'Email domain does not exist.'];
    // }
    
    return ['valid' => true, 'message' => ''];
}

// ── Slug generator ─────────────────────────────────────────
function makeSlug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function uniqueSlug(string $base, ?int $excludeId = null): string {
    $db   = getDB();
    $slug = makeSlug($base);
    $orig = $slug;
    $i    = 1;
    while (true) {
        $sql  = 'SELECT id FROM packages WHERE slug = ?';
        $args = [$slug];
        if ($excludeId) { $sql .= ' AND id != ?'; $args[] = $excludeId; }
        $stmt = $db->prepare($sql);
        $stmt->execute($args);
        if (!$stmt->fetch()) break;
        $slug = $orig . '-' . $i++;
    }
    return $slug;
}

// ── Image upload ────────────────────────────────────────────
function uploadImage(array $file, string $subfolder = 'packages'): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) return null;

    $ext  = match($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => 'jpg',
    };
    $name   = uniqid('img_', true) . '.' . $ext;
    $dir    = rtrim(UPLOAD_DIR, '/\\') . '/';
    if (!is_dir($dir)) mkdir($dir, 0750, true);
    $target = $dir . $name;
    if (!move_uploaded_file($file['tmp_name'], $target)) return null;
    return 'uploads/packages/' . $name;
}

// ── CSRF ────────────────────────────────────────────────────
function csrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF token mismatch.');
    }
}

// ── XSS helpers ─────────────────────────────────────────────
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function safeText(?string $s): string {
    return e((string)($s ?? ''));
}

// ── Redirect helper ─────────────────────────────────────────
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// ── Flash messages ──────────────────────────────────────────
function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ── Package helpers ─────────────────────────────────────────
function getPackageBySlug(string $slug): ?array {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM packages WHERE slug = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    $pkg  = $stmt->fetch();
    if (!$pkg) return null;

    $id = $pkg['id'];

    $pkg['images']      = fetchAll('SELECT * FROM package_images    WHERE package_id=? ORDER BY sort_order', [$id]);
    $pkg['itinerary']   = fetchAll('SELECT * FROM package_itinerary  WHERE package_id=? ORDER BY day_number, sort_order', [$id]);
    $pkg['inclusions']  = fetchAll('SELECT item FROM package_inclusions WHERE package_id=? ORDER BY sort_order', [$id]);
    $pkg['exclusions']  = fetchAll('SELECT item FROM package_exclusions WHERE package_id=? ORDER BY sort_order', [$id]);
    $pkg['highlights']  = fetchAll('SELECT item FROM package_highlights WHERE package_id=? ORDER BY sort_order', [$id]);
    $pkg['faqs']        = fetchAll('SELECT * FROM package_faqs       WHERE package_id=? ORDER BY sort_order', [$id]);
    $pkg['info']        = fetchAll('SELECT * FROM package_info        WHERE package_id=? ORDER BY sort_order', [$id]);

    return $pkg;
}

function getPackageById(int $id): ?array {
    $db   = getDB();
    $stmt = $db->prepare('SELECT * FROM packages WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $pkg  = $stmt->fetch();
    if (!$pkg) return null;

    $pkg['images']      = fetchAll('SELECT * FROM package_images    WHERE package_id=? ORDER BY sort_order', [$id]);
    $pkg['itinerary']   = fetchAll('SELECT * FROM package_itinerary  WHERE package_id=? ORDER BY day_number, sort_order', [$id]);
    $pkg['inclusions']  = fetchAll('SELECT item FROM package_inclusions WHERE package_id=? ORDER BY sort_order', [$id]);
    $pkg['exclusions']  = fetchAll('SELECT item FROM package_exclusions WHERE package_id=? ORDER BY sort_order', [$id]);
    $pkg['highlights']  = fetchAll('SELECT item FROM package_highlights WHERE package_id=? ORDER BY sort_order', [$id]);
    $pkg['faqs']        = fetchAll('SELECT * FROM package_faqs       WHERE package_id=? ORDER BY sort_order', [$id]);
    $pkg['info']        = fetchAll('SELECT * FROM package_info        WHERE package_id=? ORDER BY sort_order', [$id]);

    return $pkg;
}

function fetchAll(string $sql, array $params = []): array {
    $db   = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getHomepagePackages(int $limit = 4): array {
    return fetchAll(
        "SELECT * FROM packages WHERE status='published' AND show_on_homepage=1 ORDER BY sort_order ASC, id DESC LIMIT ?",
        [$limit]
    );
}

function getFeaturedPackages(int $limit = 4): array {
    return fetchAll(
        "SELECT * FROM packages WHERE status='published' AND is_featured=1 ORDER BY sort_order ASC, id DESC LIMIT ?",
        [$limit]
    );
}

function getPopularPackages(int $limit = 8): array {
    return fetchAll(
        "SELECT * FROM packages WHERE status='published' AND is_popular=1 ORDER BY sort_order ASC, id DESC LIMIT ?",
        [$limit]
    );
}

function getAllPackages(): array {
    return fetchAll(
        "SELECT * FROM packages WHERE status='published' ORDER BY title ASC"
    );
}

function getAllPublishedPackages(array $filters = [], int $page = 1, int $perPage = 9): array {
    $db     = getDB();
    $where  = ["status='published'"];
    $params = [];

    if (!empty($filters['destination'])) {
        $where[]  = 'destination = ?';
        $params[] = $filters['destination'];
    }
    if (!empty($filters['tour_type'])) {
        $where[]  = 'tour_type = ?';
        $params[] = $filters['tour_type'];
    }
    if (!empty($filters['min_price'])) {
        $where[]  = 'price_discounted >= ?';
        $params[] = (float)$filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $where[]  = 'price_discounted <= ?';
        $params[] = (float)$filters['max_price'];
    }
    if (!empty($filters['days'])) {
        $where[]  = 'days <= ?';
        $params[] = (int)$filters['days'];
    }

    $whereSQL = implode(' AND ', $where);
    $countStmt = $db->prepare("SELECT COUNT(*) FROM packages WHERE $whereSQL");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $offset   = ($page - 1) * $perPage;
    $params[] = $perPage;
    $params[] = $offset;

    $order = 'ORDER BY sort_order ASC, id DESC';
    $rows  = fetchAll("SELECT * FROM packages WHERE $whereSQL $order LIMIT ? OFFSET ?", $params);

    return [
        'packages'   => $rows,
        'total'      => $total,
        'page'       => $page,
        'per_page'   => $perPage,
        'last_page'  => max(1, (int)ceil($total / $perPage)),
    ];
}

function getDistinctDestinations(): array {
    return fetchAll("SELECT DISTINCT destination FROM packages WHERE status='published' AND destination IS NOT NULL ORDER BY destination");
}

function getDistinctTourTypes(): array {
    return fetchAll("SELECT DISTINCT tour_type FROM packages WHERE status='published' AND tour_type IS NOT NULL ORDER BY tour_type");
}

function packageCoverImageUrl(?array $pkg): string {
    if (!$pkg) return packageImageUrl(null);
    // Use main_image if available
    if (!empty($pkg['main_image'])) {
        return packageImageUrl($pkg['main_image']);
    }
    // Fall back to first gallery image
    if (!empty($pkg['images'][0]['image_path'])) {
        return packageImageUrl($pkg['images'][0]['image_path']);
    }
    return packageImageUrl(null);
}

function packageGalleryImages(array $pkg): array {
    return $pkg['images'] ?? [];
}

function packageImageUrl(?string $path): string {
    if (!$path) return SITE_URL . '/assets/images/package/01.webp';
    if (str_starts_with($path, 'http')) return $path;
    return SITE_URL . '/' . ltrim($path, '/');
}

function formatPrice(float $amount, string $currency = 'USD'): string {
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'AED' => 'AED ',
        'INR' => '₹',
        'AUD' => 'A$',
    ];
    $sym = $symbols[$currency] ?? ($currency . ' ');
    return $sym . number_format($amount, 0);
}

// ── Settings helpers ──────────────────────────────────────────
function getSettings(?string $type = null): array {
    if ($type) {
        return fetchAll(
            "SELECT * FROM package_settings WHERE type = ? ORDER BY sort_order ASC, value ASC",
            [$type]
        );
    }
    $rows = fetchAll("SELECT * FROM package_settings ORDER BY type, sort_order ASC, value ASC");
    $grouped = [];
    foreach ($rows as $r) {
        $grouped[$r['type']][] = $r;
    }
    return $grouped;
}

function getSettingsOptions(?string $type = null): array {
    $rows = getSettings($type);
    if ($type) {
        $opts = [];
        foreach ($rows as $r) {
            $opts[$r['value']] = $r['value'];
        }
        return $opts;
    }
    $grouped = [];
    foreach ($rows as $t => $items) {
        foreach ($items as $r) {
            $grouped[$t][] = $r['value'];
        }
    }
    return $grouped;
}

function starRatingHtml(float $rating): string {
    $html  = '';
    $full  = (int)floor($rating);
    $half  = ($rating - $full) >= 0.5;
    for ($i = 0; $i < $full; $i++)        $html .= '<i class="fa-solid fa-star"></i>';
    if ($half)                             $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
    for ($i = $full + ($half?1:0); $i < 5; $i++) $html .= '<i class="fa-regular fa-star"></i>';
    return $html;
}
