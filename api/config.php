<?php
// api/config.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'smart_waste');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function jsonOut(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Aman dipanggil berkali-kali — tidak akan error jika session sudah aktif
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function requireLogin(): int {
    startSession();
    if (empty($_SESSION['user_id'])) {
        jsonOut(['status' => 'not_logged_in'], 401);
    }
    return (int) $_SESSION['user_id'];
}

function requireAdmin(): int {
    $userId = requireLogin();
    $db  = getDB();
    $chk = $db->prepare("SELECT role FROM users WHERE id = ?");
    $chk->execute([$userId]);
    $row = $chk->fetch();
    if (!$row || $row['role'] !== 'admin') {
        jsonOut(['status' => 'forbidden'], 403);
    }
    return $userId;
}
