<?php
header('Content-Type: application/json');
// Restrict CORS to your domain only - adjust for production
$allowedOrigins = ['https://vmsgovista.com', 'https://www.vmsgovista.com', 'http://localhost'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
}
header('Vary: Origin');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'homepage':
        $limit = max(1, min(12, (int)($_GET['limit'] ?? 4)));
        $data  = getHomepagePackages($limit);
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'featured':
        $data = getFeaturedPackages((int)($_GET['limit'] ?? 4));
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'popular':
        $data = getPopularPackages((int)($_GET['limit'] ?? 8));
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'detail':
        $slug = trim($_GET['slug'] ?? '');
        if (!$slug) { echo json_encode(['success' => false, 'message' => 'Slug required']); break; }
        $pkg = getPackageBySlug($slug);
        if (!$pkg) { http_response_code(404); echo json_encode(['success' => false, 'message' => 'Not found']); break; }
        echo json_encode(['success' => true, 'data' => $pkg]);
        break;

    case 'list':
    default:
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(50, (int)($_GET['per_page'] ?? 9)));
        $filters = [
            'destination' => $_GET['destination'] ?? '',
            'tour_type'   => $_GET['tour_type']   ?? '',
            'min_price'   => $_GET['min_price']   ?? '',
            'max_price'   => $_GET['max_price']   ?? '',
            'days'        => $_GET['days']         ?? '',
        ];
        $result = getAllPublishedPackages($filters, $page, $perPage);
        echo json_encode(['success' => true, 'data' => $result]);
        break;
}
