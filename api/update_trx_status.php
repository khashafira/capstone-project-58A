<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['status'=>'method_not_allowed'], 405);
requireAdmin();
$id     = (int)($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');
if ($id <= 0 || !in_array($status, ['Pending','Selesai','Dibatalkan'])) jsonOut(['status'=>'invalid_data']);
$db = getDB();
$db->prepare("UPDATE waste_transactions SET status = ? WHERE id = ?")->execute([$status, $id]);
jsonOut(['status'=>'success']);
