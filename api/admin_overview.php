<?php
require_once __DIR__ . '/config.php';
requireAdmin();
$db = getDB();

$total_users  = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$total_trx    = $db->query("SELECT COUNT(*) FROM waste_transactions")->fetchColumn();
$total_points = $db->query("SELECT COALESCE(SUM(points),0) FROM users WHERE role = 'user'")->fetchColumn();
$total_weight = $db->query("SELECT COALESCE(SUM(weight),0) FROM waste_transactions")->fetchColumn();

jsonOut([
    'total_users'        => (int) $total_users,
    'total_transactions' => (int) $total_trx,
    'total_points'       => (int) $total_points,
    'total_weight'       => number_format((float) $total_weight, 1),
]);
