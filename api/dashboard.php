<?php
require_once __DIR__ . '/config.php';
$userId = requireLogin();
$db     = getDB();

$user = $db->prepare("SELECT name, email, phone, points, referral_code FROM users WHERE id = ?");
$user->execute([$userId]);
$u = $user->fetch();
$points = (int)$u['points'];

$allTime = $db->prepare("SELECT COUNT(*) AS total_trx, COALESCE(SUM(weight),0) AS total_kg FROM waste_transactions WHERE user_id = ?");
$allTime->execute([$userId]);
$at = $allTime->fetch();

$tm = $db->prepare("SELECT COUNT(*) AS trx_month FROM waste_transactions WHERE user_id = ? AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
$tm->execute([$userId]);
$trxMonth = (int)$tm->fetch()['trx_month'];

$rank = (int)$db->prepare("SELECT COUNT(*)+1 FROM users WHERE role='user' AND points > ?")->execute([$points]) ? 1 : 1;
$rankStmt = $db->prepare("SELECT COUNT(*)+1 AS r FROM users WHERE role='user' AND points > ?");
$rankStmt->execute([$points]);
$rank = (int)$rankStmt->fetch()['r'];

$levels = [
    ['name'=>'🌱 Eco Starter', 'min'=>0,    'max'=>500,  'next'=>'🌿 Eco Saver'],
    ['name'=>'🌿 Eco Saver',   'min'=>500,  'max'=>2000, 'next'=>'⚡ Eco Warrior'],
    ['name'=>'⚡ Eco Warrior', 'min'=>2000, 'max'=>5000, 'next'=>'🏆 Eco Champion'],
    ['name'=>'🏆 Eco Champion','min'=>5000, 'max'=>5000, 'next'=>null],
];
$lv = $levels[0];
foreach ($levels as $l) { if ($points >= $l['min']) $lv = $l; else break; }

if ($lv['next'] === null) {
    $pct = 100; $ptsToNext = 0; $target = '🏆 Level Tertinggi!';
} else {
    $range     = $lv['max'] - $lv['min'];
    $pct       = $range > 0 ? min(100, round(($points - $lv['min']) / $range * 100)) : 100;
    $ptsToNext = $lv['max'] - $points;
    $target    = $lv['max'] . ' poin → ' . $lv['next'];
}

$totalKg = (float)$at['total_kg'];
$co2     = round($totalKg * 0.5, 1);
$trees   = round($co2 / 21, 1);

$recent = $db->prepare("SELECT DATE_FORMAT(created_at,'%d %b %Y %H:%i') AS date, COALESCE(type,'dropoff') AS type, category, weight, points, COALESCE(status,'Pending') AS status FROM waste_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$recent->execute([$userId]);

jsonOut([
    'name'         => $u['name'],
    'email'        => $u['email'],
    'phone'        => $u['phone'] ?? '',
    'referral_code'=> $u['referral_code'] ?? '',
    'points'       => $points,
    'total_trx'    => (int)$at['total_trx'],
    'total_kg'     => $totalKg,
    'trx_month'    => $trxMonth,
    'rank'         => $rank,
    'level'        => $lv['name'],
    'level_pct'    => $pct,
    'pts_to_next'  => $ptsToNext,
    'target_label' => $target,
    'co2'          => $co2,
    'trees'        => $trees,
    'recent_trx'   => $recent->fetchAll(),
]);
