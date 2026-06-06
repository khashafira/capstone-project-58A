<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['status'=>'method_not_allowed'], 405);
requireAdmin();
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) jsonOut(['status'=>'invalid_id']);
$db = getDB();
$db->prepare("DELETE FROM redemptions WHERE reward_id = ?")->execute([$id]);
$db->prepare("DELETE FROM rewards WHERE id = ?")->execute([$id]);
jsonOut(['status'=>'success']);
