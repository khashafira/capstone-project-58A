<?php
require_once __DIR__ . '/config.php';
startSession();

if (empty($_SESSION['user_id'])) {
    jsonOut(['logged_in' => false]);
}

$db   = getDB();
$stmt = $db->prepare("SELECT id, name, role, points FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION = [];
    session_destroy();
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
    }
    jsonOut(['logged_in' => false]);
}

$_SESSION['role'] = $user['role'];

jsonOut([
    'logged_in' => true,
    'role'      => $user['role'],
    'name'      => $user['name'],
    'points'    => (int) $user['points'],
]);
