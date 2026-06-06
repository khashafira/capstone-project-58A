<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['status'=>'method_not_allowed'], 405);
$adminId = requireAdmin();
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) jsonOut(['status'=>'invalid_id']);
if ($id === $adminId) jsonOut(['status'=>'error','message'=>'Tidak bisa menghapus akun sendiri']);
$db = getDB();
$db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
jsonOut(['status'=>'success']);
