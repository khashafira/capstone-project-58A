<?php
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['status'=>'method_not_allowed'], 405);
$userId   = requireLogin();
$rewardId = (int)($_POST['reward_id'] ?? 0);
if ($rewardId <= 0) jsonOut(['status'=>'invalid_reward']);

$db = getDB();
$db->beginTransaction();
try {
    $r = $db->prepare("SELECT id,title,points_required,stock,is_active FROM rewards WHERE id=? FOR UPDATE");
    $r->execute([$rewardId]);
    $reward = $r->fetch();
    if (!$reward || !$reward['is_active']) { $db->rollBack(); jsonOut(['status'=>'reward_not_found']); }
    if ($reward['stock'] <= 0)             { $db->rollBack(); jsonOut(['status'=>'stock_empty']); }

    $u = $db->prepare("SELECT id,points FROM users WHERE id=? FOR UPDATE");
    $u->execute([$userId]);
    $user = $u->fetch();
    if ($user['points'] < $reward['points_required']) { $db->rollBack(); jsonOut(['status'=>'not_enough_points','user_points'=>$user['points'],'required'=>$reward['points_required']]); }

    $db->prepare("UPDATE users SET points=points-? WHERE id=?")->execute([$reward['points_required'],$userId]);
    $db->prepare("UPDATE rewards SET stock=stock-1,total_redeemed=total_redeemed+1 WHERE id=?")->execute([$rewardId]);

    $vc = 'ECO-' . strtoupper(substr(md5(uniqid($userId.$rewardId,true)),0,8));
    $db->prepare("INSERT INTO redemptions (user_id,reward_id,points_used,status,voucher_code) VALUES (?,?,?,'Aktif',?)")
       ->execute([$userId,$rewardId,$reward['points_required'],$vc]);

    $db->commit();
    $newPts = (int)$db->query("SELECT points FROM users WHERE id=$userId")->fetchColumn();
    jsonOut(['status'=>'success','voucher_code'=>$vc,'reward_name'=>$reward['title'],'points_used'=>$reward['points_required'],'new_points'=>$newPts]);
} catch (Throwable $e) {
    $db->rollBack();
    error_log('redeem_reward.php: '.$e->getMessage());
    jsonOut(['status'=>'error'],500);
}
