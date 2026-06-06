<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['status'=>'method_not_allowed'], 405);
$userId  = requireLogin();
$oldPass = trim($_POST['old_password'] ?? '');
$newPass = trim($_POST['new_password'] ?? '');
if (!$oldPass || !$newPass || strlen($newPass) < 6) jsonOut(['status'=>'invalid_data']);

$db   = getDB();
$stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) jsonOut(['status'=>'not_found']);

$ok = (strlen($user['password']) === 60 && str_starts_with($user['password'],'$'))
    ? password_verify($oldPass, $user['password'])
    : ($oldPass === $user['password']);
if (!$ok) jsonOut(['status'=>'wrong_password']);

$db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newPass, $userId]);
jsonOut(['status'=>'success']);
