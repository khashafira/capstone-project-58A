<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['status'=>'method_not_allowed'], 405);
$userId = requireLogin();
$trxId  = (int)($_POST['trx_id'] ?? 0);
if ($trxId <= 0) jsonOut(['status'=>'invalid_id']);

$db = getDB();
$db->beginTransaction();
try {
    $stmt = $db->prepare("SELECT id,user_id,points,status FROM waste_transactions WHERE id=? AND user_id=? FOR UPDATE");
    $stmt->execute([$trxId,$userId]);
    $trx = $stmt->fetch();
    if (!$trx) { $db->rollBack(); jsonOut(['status'=>'not_found']); }
    if ($trx['status'] === 'Selesai') {
        $db->rollBack();
        $pts = (int)$db->query("SELECT points FROM users WHERE id=$userId")->fetchColumn();
        jsonOut(['status'=>'already_scanned','new_points'=>$pts]);
    }
    $db->prepare("UPDATE waste_transactions SET status='Selesai' WHERE id=?")->execute([$trxId]);
    $db->prepare("UPDATE users SET points=points+? WHERE id=?")->execute([$trx['points'],$userId]);
    $db->commit();
    $newPts = (int)$db->query("SELECT points FROM users WHERE id=$userId")->fetchColumn();
    jsonOut(['status'=>'success','points_earned'=>$trx['points'],'new_points'=>$newPts]);
} catch (Throwable $e) {
    $db->rollBack();
    error_log('scan.php: '.$e->getMessage());
    jsonOut(['status'=>'error'],500);
}
