<?php
declare(strict_types=1);

session_start();
if (!isset($_SESSION['mathcraft_signature'])) {
    http_response_code(403);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><title>Access required</title><p>Approval is required. <a href="../auth.php">Request access</a>.</p>';
    exit;
}