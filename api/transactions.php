<?php
require_once __DIR__ . '/config.php';
$userId = requireLogin();
$db     = getDB();

$rows = $db->prepare("
    SELECT id, COALESCE(type,'dropoff') AS type, category, weight, points,
           COALESCE(status,'Pending') AS status, pickup_date, pickup_time,
           DATE_FORMAT(created_at,'%d %b %Y %H:%i') AS created_at
    FROM waste_transactions WHERE user_id=? ORDER BY created_at DESC LIMIT 100
");
$rows->execute([$userId]);
$data = $rows->fetchAll();

$hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

foreach ($data as &$row) {
    $row['status_display'] = $row['status'];
    if (!empty($row['pickup_date'])) {
        $d = new DateTime($row['pickup_date']);
        $row['pickup_date_label'] = $hari[$d->format('w')].', '.$d->format('d').' '.$bulan[(int)$d->format('n')-1].' '.$d->format('Y');
    } else {
        $row['pickup_date_label'] = null;
    }
}
unset($row);

jsonOut($data);
