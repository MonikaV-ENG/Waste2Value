<?php
require_once 'config.php';
$pdo = db();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $requestId=filter_input(INPUT_POST,'request_id',FILTER_VALIDATE_INT);
    $action=$_POST['action'] ?? '';
    if ($requestId && in_array($action,['accept','reject'],true)) {
        try {
            $pdo->beginTransaction();
            $q=$pdo->prepare("SELECT r.*, wi.item_id, wi.status AS item_status FROM requests r JOIN waste_items wi ON wi.item_id=r.item_id WHERE r.request_id=? FOR UPDATE");
            $q->execute([$requestId]); $request=$q->fetch();
            if (!$request || $request['status']!=='Pending') throw new RuntimeException('This request has already been processed.');
            if ($action==='accept') {
                $pdo->prepare("UPDATE requests SET status='Accepted' WHERE request_id=?")->execute([$requestId]);
                $pdo->prepare("UPDATE requests SET status='Rejected' WHERE item_id=? AND request_id<>? AND status='Pending'")->execute([$request['item_id'],$requestId]);
                $pdo->prepare("UPDATE waste_items SET status='Matched' WHERE item_id=?")->execute([$request['item_id']]);
                $pdo->prepare("INSERT INTO matches (item_id,request_id) VALUES (?,?)")->execute([$request['item_id'],$requestId]);
                $matchId=(int)$pdo->lastInsertId();
                $pdo->prepare("INSERT INTO pickups (match_id,status) VALUES (?,'Scheduled')")->execute([$matchId]);
                flash('success','Request accepted and a pickup record was created.');
            } else {
                $pdo->prepare("UPDATE requests SET status='Rejected' WHERE request_id=?")->execute([$requestId]);
                $pending=$pdo->prepare("SELECT COUNT(*) FROM requests WHERE item_id=? AND status='Pending'"); $pending->execute([$request['item_id']]);
                if ((int)$pending->fetchColumn()===0) $pdo->prepare("UPDATE waste_items SET status='Available' WHERE item_id=? AND status='Requested'")->execute([$request['item_id']]);
                flash('success','Request rejected.');
            }
            $pdo->commit();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('error',$ex instanceof RuntimeException?$ex->getMessage():'Could not update request.');
        }
    }
    redirect('requests.php');
}
$requests=$pdo->query("SELECT r.*, wi.title, wi.unit, wi.quantity AS listed_quantity, u.name AS requester_name, u.email AS requester_email, u.phone AS requester_phone, owner.name AS owner_name FROM requests r JOIN waste_items wi ON wi.item_id=r.item_id JOIN users u ON u.user_id=r.requester_id JOIN users owner ON owner.user_id=wi.user_id ORDER BY r.created_at DESC")->fetchAll();
$pageTitle='Requests'; require 'includes/header.php';
?>
<section class="page-heading"><span class="eyebrow">REQUEST MANAGEMENT</span><h1>Material requests</h1><p>Review interest in listed materials and accept a request to create a match and pickup record.</p></section>
<div class="table-card"><div class="table-heading"><div><h2>All requests</h2><p>Demo workflow: accepting a request automatically creates a match and pickup.</p></div><span class="count-badge"><?= count($requests) ?> total</span></div>
<?php if (!$requests): ?><div class="empty-state"><h3>No requests yet</h3><p>When someone requests a listed material, it will appear here.</p><a class="btn btn-primary" href="waste.php">Browse materials</a></div>
<?php else: ?><div class="table-scroll"><table><thead><tr><th>Material</th><th>Requester</th><th>Quantity</th><th>Date</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach ($requests as $r): ?><tr><td><strong><?= e($r['title']) ?></strong><small>Owner: <?= e($r['owner_name']) ?></small></td><td><?= e($r['requester_name']) ?><small><?= e($r['requester_email']) ?></small></td><td><?= e($r['requested_quantity']) ?> <?= e($r['unit']) ?><small>Listed: <?= e($r['listed_quantity']) ?> <?= e($r['unit']) ?></small></td><td><?= e(date('d M Y',strtotime($r['created_at']))) ?></td><td><span class="status-pill status-<?= strtolower(e($r['status'])) ?>"><?= e($r['status']) ?></span></td><td><?php if ($r['status']==='Pending'): ?><div class="action-inline"><form method="post"><input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>"><button class="btn btn-small btn-primary" name="action" value="accept" data-confirm="Accept this request and create a pickup?">Accept</button></form><form method="post"><input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>"><button class="btn btn-small btn-light" name="action" value="reject" data-confirm="Reject this request?">Reject</button></form></div><?php else: ?><span class="muted">—</span><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></div>
<?php require 'includes/footer.php'; ?>
