<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['status'=>'method_not_allowed'], 405);
requireAdmin();
$db = getDB();

$title = trim($_POST['title'] ?? '');
$pts   = (int)($_POST['points_required'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
if (!$title || $pts <= 0) jsonOut(['status'=>'invalid_data']);

$db->prepare("INSERT INTO rewards (title,description,icon,category,points_required,stock) VALUES (?,?,?,?,?,?)")
   ->execute([$title, trim($_POST['description']??''), trim($_POST['icon']??'🎁'), trim($_POST['category']??'Umum'), $pts, $stock]);

jsonOut(['status'=>'success', 'id'=>(int)$db->lastInsertId()]);
