<?php
// File debug sementara — hapus setelah selesai
require_once __DIR__ . '/config.php';

$now = new DateTime();
$nowUTC7 = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

$pickup_date = '2026-05-28';
$pickup_time = '07:00 - 09:00';

$parts    = explode('-', $pickup_time);
$endHour  = trim($parts[1]);
$deadline = new DateTime($pickup_date . ' ' . $endHour . ':00');
$deadlineWIB = new DateTime($pickup_date . ' ' . $endHour . ':00', new DateTimeZone('Asia/Jakarta'));

jsonOut([
    'php_default_timezone' => date_default_timezone_get(),
    'now_server'           => $now->format('Y-m-d H:i:s'),
    'now_WIB'              => $nowUTC7->format('Y-m-d H:i:s'),
    'deadline'             => $deadline->format('Y-m-d H:i:s'),
    'deadline_WIB'         => $deadlineWIB->format('Y-m-d H:i:s'),
    'is_done_default'      => $now > $deadline ? 'SELESAI' : 'PENDING',
    'is_done_WIB'          => $nowUTC7 > $deadlineWIB ? 'SELESAI' : 'PENDING',
]);
