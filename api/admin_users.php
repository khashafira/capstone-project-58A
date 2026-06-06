<?php
require_once __DIR__ . '/config.php';
requireAdmin();
$db = getDB();

$users = $db->query("
    SELECT u.id, u.name, u.email, u.role, u.points,
           COUNT(wt.id) AS transactions,
           CASE
               WHEN u.points >= 5000 THEN '🏆 Eco Champion'
               WHEN u.points >= 2000 THEN '⚡ Eco Warrior'
               WHEN u.points >= 500  THEN '🌿 Eco Saver'
               ELSE '🌱 Eco Starter'
           END AS level
    FROM users u
    LEFT JOIN waste_transactions wt ON wt.user_id = u.id
    GROUP BY u.id
    ORDER BY u.points DESC
")->fetchAll();

jsonOut($users);
