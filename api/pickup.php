<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['status'=>'method_not_allowed'], 405);
$userId      = requireLogin();
$category    = trim($_POST['category']    ?? '');
$weight      = (float)($_POST['weight']   ?? 0);
$pickupDate  = trim($_POST['pickup_date'] ?? '');
$pickupTime  = trim($_POST['pickup_time'] ?? '');
if (!$category || $weight <= 0) jsonOut(['status'=>'invalid_data']);

$ptsMap = ['Plastik'=>20,'Kertas'=>15,'Logam'=>30,'Elektronik'=>50,'Organik'=>10,'Kaca'=>25,'Campuran'=>12,'B3'=>60];
$points = (int)round($weight * ($ptsMap[$category] ?? 20));
$qrCode = 'ECO-PU-' . date('Y') . '-' . strtoupper(substr(md5(uniqid($userId,true)),0,6));

$bulanMap = ['Januari'=>1,'Februari'=>2,'Maret'=>3,'April'=>4,'Mei'=>5,'Juni'=>6,'Juli'=>7,'Agustus'=>8,'September'=>9,'Oktober'=>10,'November'=>11,'Desember'=>12];
$mysqlDate = null;
if ($pickupDate) {
    $clean = preg_replace('/^[^,]+,\s*/','',$pickupDate);
    $parts = explode(' ', trim($clean));
    if (count($parts) >= 3) $mysqlDate = sprintf('%04d-%02d-%02d',(int)$parts[2],$bulanMap[$parts[1]]??1,(int)$parts[0]);
}

$db = getDB();
$db->prepare("INSERT INTO waste_transactions (user_id,type,category,weight,points,status,pickup_date,pickup_time,address) VALUES (?,'pickup',?,?,?,'Pending',?,?,?)")
   ->execute([$userId,$category,$weight,$points,$mysqlDate,$pickupTime,trim($_POST['address']??'')]);

jsonOut(['status'=>'success','trx_id'=>(int)$db->lastInsertId(),'qr_code'=>$qrCode,'points'=>$points]);
