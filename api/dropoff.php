<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['status'=>'method_not_allowed'], 405);
$userId   = requireLogin();
$category = trim($_POST['category'] ?? '');
$weight   = (float)($_POST['weight'] ?? 0);
if (!$category || $weight <= 0) jsonOut(['status'=>'invalid_data']);

$ptsMap = ['Plastik'=>20,'Kertas'=>15,'Logam'=>30,'Elektronik'=>50,'Organik'=>10,'Kaca'=>25,'Campuran'=>12,'B3'=>60];
$points = (int)round($weight * ($ptsMap[$category] ?? 20));
$qrCode = 'ECO-DO-' . date('Y') . '-' . strtoupper(substr(md5(uniqid($userId,true)),0,6));

$db = getDB();
$db->prepare("INSERT INTO waste_transactions (user_id,type,category,weight,points,status) VALUES (?,'dropoff',?,?,?,'Pending')")
   ->execute([$userId,$category,$weight,$points]);

jsonOut(['status'=>'success','trx_id'=>(int)$db->lastInsertId(),'qr_code'=>$qrCode,'points'=>$points,'category'=>$category,'weight'=>$weight]);
