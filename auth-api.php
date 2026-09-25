<?php
declare(strict_types=1);

const AUTH_DATA_FILE = __DIR__ . '/auth-data/requests.json';
require_once __DIR__ . '/auth-config.php';

session_start();
header('Content-Type: application/json; charset=UTF-8');

function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function readRequests(): array
{
    if (!is_file(AUTH_DATA_FILE)) {
        return [];
    }

    $requests = json_decode(file_get_contents(AUTH_DATA_FILE) ?: '[]', true);
    return is_array($requests) ? array_values(array_filter($requests, 'is_array')) : [];
}

function updateRequests(callable $update): array
{
    $handle = fopen(AUTH_DATA_FILE, 'c+');
    if ($handle === false || !flock($handle, LOCK_EX)) {
        respond(['error' => 'The request store is unavailable.'], 503);
    }

    rewind($handle);
    $requests = json_decode(stream_get_contents($handle) ?: '[]', true);
    $requests = is_array($requests) ? array_values(array_filter($requests, 'is_array')) : [];
    $requests = $update($requests);

    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $requests;
}

function requestBody(): array
{
    $body = json_decode(file_get_contents('php://input') ?: '{}', true);
    return is_array($body) ? $body : [];
}

function adminKeyIsValid(): bool
{
    $providedPassword = $_SERVER['HTTP_X_ADMIN_PASSWORD'] ?? '';
    return is_string($providedPassword) && password_verify($providedPassword, AUTH_ADMIN_PASSWORD_HASH);
}

function cleanName(mixed $value): string
{
    if (!is_string($value)) {
        return '';
    }
    return trim(preg_replace('/[^\P{C}\n\r\t]/u', '', $value) ?? '');
}

function browserUserAgent(): string
{
    return substr(trim($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown browser'), 0, 500);
}

function signatureIsApproved(string $signature): bool
{
    foreach (readRequests() as $request) {
        if (($request['signature'] ?? '') === $signature && ($request['status'] ?? '') === 'approved') {
            return true;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $signature = $_GET['signature'] ?? '';
    if (!is_string($signature) || !preg_match('/^[a-f0-9]{64}$/', $signature)) {
        respond(['error' => 'A valid browser signature is required.'], 400);
    }

    foreach (readRequests() as $request) {
        if (($request['signature'] ?? '') === $signature) {
            respond(['status' => $request['status'], 'request' => $request]);
        }
    }
    respond(['status' => 'not_requested']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Method not allowed.'], 405);
}

$body = requestBody();
$action = $body['action'] ?? 'request';

if ($action === 'authorize') {
    $signature = $body['signature'] ?? '';
    if (!is_string($signature) || !preg_match('/^[a-f0-9]{64}$/', $signature) || !signatureIsApproved($signature)) {
        respond(['error' => 'This browser is not approved.'], 403);
    }
    $_SESSION['mathcraft_signature'] = $signature;
    respond(['status' => 'authorized']);
}

if ($action === 'request') {
    $signature = $body['signature'] ?? '';
    $name = cleanName($body['name'] ?? '');
    if (!is_string($signature) || !preg_match('/^[a-f0-9]{64}$/', $signature)) {
        respond(['error' => 'A valid browser signature is required.'], 400);
    }
    if (mb_strlen($name) < 2 || mb_strlen($name) > 40) {
        respond(['error' => 'A name between 2 and 40 characters is required.'], 400);
    }

    $requests = updateRequests(function (array $requests) use ($signature, $name): array {
        foreach ($requests as &$request) {
            if (($request['signature'] ?? '') === $signature) {
                $request['name'] = $name;
                $request['user_agent'] = browserUserAgent();
                if ($request['status'] === 'denied') {
                    $request['status'] = 'pending';
                    $request['updated_at'] = gmdate('c');
                }
                return $requests;
            }
        }

        $requests[] = [
            'id' => bin2hex(random_bytes(8)),
            'name' => $name,
            'signature' => $signature,
            'user_agent' => browserUserAgent(),
            'status' => 'pending',
            'created_at' => gmdate('c'),
            'updated_at' => gmdate('c'),
        ];
        return $requests;
    });

    foreach ($requests as $request) {
        if (($request['signature'] ?? '') === $signature) {
            respond(['status' => $request['status'], 'request' => $request]);
        }
    }
}

if (!in_array($action, ['approve', 'deny'], true) || !adminKeyIsValid()) {
    respond(['error' => 'Admin authentication failed.'], 403);
}

$id = $body['id'] ?? '';
if (!is_string($id) || !preg_match('/^[a-f0-9]{16}$/', $id)) {
    respond(['error' => 'A valid request id is required.'], 400);
}

$requests = updateRequests(function (array $requests) use ($id, $action): array {
    foreach ($requests as &$request) {
        if (($request['id'] ?? '') === $id) {
            $request['status'] = $action === 'approve' ? 'approved' : 'denied';
            $request['updated_at'] = gmdate('c');
        }
    }
    return $requests;
});

foreach ($requests as $request) {
    if (($request['id'] ?? '') === $id) {
        respond(['status' => $request['status'], 'request' => $request]);
    }
}
respond(['error' => 'Request not found.'], 404);