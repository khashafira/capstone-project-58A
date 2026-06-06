<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['status'=>'method_not_allowed'], 405);
$userId = requireLogin();
$name   = trim($_POST['name']  ?? '');
$email  = trim($_POST['email'] ?? '');
$phone  = trim($_POST['phone'] ?? '');
if (!$name || !$email)                          jsonOut(['status'=>'invalid_data']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonOut(['status'=>'invalid_email']);
$db  = getDB();
$chk = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
$chk->execute([$email, $userId]);
if ($chk->fetch()) jsonOut(['status'=>'email_exists']);
$db->prepare("UPDATE users SET name=?,email=?,phone=? WHERE id=?")->execute([$name,$email,$phone?:null,$userId]);
jsonOut(['status'=>'success']);
