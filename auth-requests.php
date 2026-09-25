<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/auth-config.php';
$providedKey = $_SERVER['HTTP_X_ADMIN_KEY'] ?? '';
$providedPassword = $_SERVER['HTTP_X_ADMIN_PASSWORD'] ?? '';
if (!is_string($providedPassword) || !password_verify($providedPassword, AUTH_ADMIN_PASSWORD_HASH)) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin authentication failed.']);
    exit;
}

$file = __DIR__ . '/auth-data/requests.json';
$requests = is_file($file) ? json_decode(file_get_contents($file) ?: '[]', true) : [];
$requests = is_array($requests) ? array_values(array_filter($requests, 'is_array')) : [];
echo json_encode(['requests' => is_array($requests) ? $requests : []], JSON_UNESCAPED_SLASHES);