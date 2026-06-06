<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['status'=>'method_not_allowed'], 405);

$email = trim($_POST['email'] ?? '');
$pass  = trim($_POST['password'] ?? '');
if (!$email || !$pass) jsonOut(['status'=>'missing_fields']);

$db   = getDB();
$stmt = $db->prepare("SELECT id, name, email, password, role, points FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) jsonOut(['status'=>'error','message'=>'Email atau password salah']);

$ok = (strlen($user['password']) === 60 && str_starts_with($user['password'],'$'))
    ? password_verify($pass, $user['password'])
    : ($pass === $user['password']);

if (!$ok) jsonOut(['status'=>'error','message'=>'Email atau password salah']);

startSession();
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'];
$_SESSION['name']    = $user['name'];

jsonOut(['status'=>'success','role'=>$user['role'],'name'=>$user['name'],'points'=>(int)$user['points']]);
