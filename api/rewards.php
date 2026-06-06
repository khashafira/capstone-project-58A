<?php
require_once __DIR__ . '/config.php';
$userId = requireLogin();
$db     = getDB();

$stmt = $db->prepare("
    SELECT r.id, r.title, r.description, r.icon, r.category,
           r.points_required, r.stock, r.total_redeemed, u.points AS user_points
    FROM rewards r JOIN users u ON u.id=:uid
    WHERE r.is_active=1 ORDER BY r.points_required ASC
");
$stmt->execute([':uid'=>$userId]);
$rewards = $stmt->fetchAll();

$colors = ['Voucher Belanja'=>'var(--green-light)','Voucher Makanan'=>'var(--amber-light)','E-Wallet'=>'var(--blue-light)','Pulsa'=>'var(--coral-light)'];
foreach ($rewards as &$r) {
    $r['can_redeem'] = ($r['user_points'] >= $r['points_required']) && ($r['stock'] > 0);
    $r['color']      = $colors[$r['category']] ?? 'var(--gray-light)';
    unset($r['user_points']);
}

jsonOut($rewards);
