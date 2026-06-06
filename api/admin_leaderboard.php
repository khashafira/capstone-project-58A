<?php
require_once __DIR__ . '/config.php';
requireAdmin();
$db = getDB();

$lb = $db->query("
    SELECT u.id, u.name, u.points, COUNT(wt.id) AS transactions
    FROM users u
    LEFT JOIN waste_transactions wt ON wt.user_id = u.id
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY u.points DESC
    LIMIT 10
")->fetchAll();

jsonOut($lb);
