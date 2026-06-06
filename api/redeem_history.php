<?php
require_once __DIR__ . '/config.php';
$userId = requireLogin();
$db     = getDB();

$stmt = $db->prepare("
    SELECT rd.id, r.title AS reward, r.icon, rd.points_used AS pts,
           rd.status, rd.voucher_code, DATE_FORMAT(rd.redeemed_at,'%d %b %Y') AS date
    FROM redemptions rd JOIN rewards r ON r.id=rd.reward_id
    WHERE rd.user_id=:uid ORDER BY rd.redeemed_at DESC LIMIT 50
");
$stmt->execute([':uid'=>$userId]);
jsonOut($stmt->fetchAll());
