<?php
require_once __DIR__ . '/config.php';
requireAdmin();
$db = getDB();

$rewards = $db->query("
    SELECT id, title, icon, category, points_required,
           stock, total_redeemed, is_active,
           DATE_FORMAT(created_at, '%d %b %Y') AS created_at
    FROM rewards
    ORDER BY is_active DESC, id ASC
")->fetchAll();

jsonOut($rewards);
