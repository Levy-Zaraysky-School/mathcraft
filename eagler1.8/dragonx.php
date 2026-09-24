<?php
header("Content-Type: text/html; charset=UTF-8");
$chunks = glob(__DIR__ . '/flamepvp/dragonx_chunk_*.txt');
natsort($chunks);
foreach ($chunks as $chunk) {
    readfile($chunk);
}
?>
