<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['status'=>'method_not_allowed'], 405);
requireAdmin();
$db = getDB();

$id     = (int)($_POST['id'] ?? 0);
$title  = trim($_POST['title'] ?? '');
$pts    = (int)($_POST['points_required'] ?? 0);
$stock  = (int)($_POST['stock'] ?? 0);
$active = (int)($_POST['is_active'] ?? 1);
if ($id <= 0 || !$title || $pts <= 0) jsonOut(['status'=>'invalid_data']);

$db->prepare("UPDATE rewards SET title=?,description=?,icon=?,category=?,points_required=?,stock=?,is_active=? WHERE id=?")
   ->execute([
       $title, trim($_POST['description']??''), trim($_POST['icon']??'🎁'),
       trim($_POST['category']??'Umum'), $pts, $stock, $active, $id
   ]);

jsonOut(['status'=>'success']);
