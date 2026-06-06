<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['status'=>'method_not_allowed'], 405);

$name  = trim($_POST['name']     ?? '');
$email = trim($_POST['email']    ?? '');
$phone = trim($_POST['phone']    ?? '');
$pass  = trim($_POST['password'] ?? '');

if (!$name || !$email || !$pass)                jsonOut(['status'=>'invalid_data']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonOut(['status'=>'invalid_email']);

$db  = getDB();
$chk = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$chk->execute([$email]);
if ($chk->fetch()) jsonOut(['status'=>'email_exists']);

$referral = 'ECO-' . strtoupper(substr(md5(uniqid($email,true)), 0, 6));

$db->prepare("INSERT INTO users (name,email,phone,password,role,points,referral_code) VALUES (?,?,?,?,'user',0,?)")
   ->execute([$name, $email, $phone ?: null, $pass, $referral]);

$userId = (int)$db->lastInsertId();
startSession();
session_regenerate_id(true);
$_SESSION['user_id'] = $userId;
$_SESSION['role']    = 'user';

jsonOut(['status'=>'success','name'=>$name]);
