<?php
require_once __DIR__ . '/../auth-guard.php';
header("Content-Type: text/html; charset=UTF-8");
$chunks = glob(__DIR__ . '/pixel/pixel_js_chunk_*.txt');
natsort($chunks);
foreach ($chunks as $chunk) {
    readfile($chunk);
}
?>
