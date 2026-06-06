<?php
require_once __DIR__ . '/config.php';
requireAdmin();
$db = getDB();

$trx = $db->query("
    SELECT wt.id, u.name,
           COALESCE(wt.type,'dropoff') AS type,
           wt.category, wt.weight, wt.points,
           COALESCE(wt.status,'Pending') AS status,
           wt.pickup_date, wt.pickup_time,
           DATE_FORMAT(wt.created_at, '%d %b %Y %H:%i') AS created_at
    FROM waste_transactions wt
    JOIN users u ON u.id = wt.user_id
    ORDER BY wt.created_at DESC
    LIMIT 200
")->fetchAll();

$hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

foreach ($trx as &$t) {
    if ($t['pickup_date']) {
        $d = new DateTime($t['pickup_date']);
        $t['pickup_label'] = $hari[$d->format('w')] . ', ' . $d->format('d') . ' ' .
                             $bulan[(int)$d->format('n')-1] . ' ' . $d->format('Y') .
                             ($t['pickup_time'] ? ' ' . $t['pickup_time'] : '');
    } else {
        $t['pickup_label'] = null;
    }
}
unset($t);

jsonOut($trx);
