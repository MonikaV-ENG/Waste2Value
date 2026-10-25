<?php
require_once 'config.php';
$pdo = db();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pickupId=filter_input(INPUT_POST,'pickup_id',FILTER_VALIDATE_INT);
    $action=$_POST['action'] ?? '';
    if ($pickupId && in_array($action,['complete','cancel'],true)) {
        try {
            $pdo->beginTransaction();
            $q=$pdo->prepare("SELECT p.*, m.match_id, m.item_id, m.status AS match_status FROM pickups p JOIN matches m ON m.match_id=p.match_id WHERE p.pickup_id=? FOR UPDATE");
            $q->execute([$pickupId]); $p=$q->fetch();
            if (!$p) throw new RuntimeException('Pickup record not found.');
            if ($action==='complete') {
                $pdo->prepare("UPDATE pickups SET status='Completed' WHERE pickup_id=?")->execute([$pickupId]);
                $pdo->prepare("UPDATE matches SET status='Completed' WHERE match_id=?")->execute([$p['match_id']]);
                $pdo->prepare("UPDATE waste_items SET status='Collected' WHERE item_id=?")->execute([$p['item_id']]);
                flash('success','Pickup marked as completed.');
            } else {
                $pdo->prepare("UPDATE pickups SET status='Cancelled' WHERE pickup_id=?")->execute([$pickupId]);
                $pdo->prepare("UPDATE matches SET status='Cancelled' WHERE match_id=?")->execute([$p['match_id']]);
                $pdo->prepare("UPDATE waste_items SET status='Available' WHERE item_id=?")->execute([$p['item_id']]);
                $pdo->prepare("UPDATE requests SET status='Cancelled' WHERE request_id=(SELECT request_id FROM matches WHERE match_id=?)")->execute([$p['match_id']]);
                flash('success','Pickup cancelled and item made available again.');
            }
            $pdo->commit();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('error',$ex instanceof RuntimeException?$ex->getMessage():'Could not update pickup.');
        }
    }
    redirect('matches.php');
}
$matches=$pdo->query("SELECT m.match_id,m.status AS match_status,m.matched_at,wi.title,wi.quantity,wi.unit,owner.name AS owner_name,req.name AS requester_name,r.requested_quantity,p.pickup_id,p.pickup_date,p.pickup_note,p.status AS pickup_status FROM matches m JOIN waste_items wi ON wi.item_id=m.item_id JOIN requests rr ON rr.request_id=m.request_id JOIN users owner ON owner.user_id=wi.user_id JOIN users req ON req.user_id=rr.requester_id JOIN requests r ON r.request_id=m.request_id JOIN pickups p ON p.match_id=m.match_id ORDER BY m.matched_at DESC")->fetchAll();
$pageTitle='Matches & Pickups'; require 'includes/header.php';
?>
<section class="page-heading"><span class="eyebrow">FULFILMENT TRACKER</span><h1>Matches & pickups</h1><p>Accepted requests become matches. Track each pickup through to completion.</p></section>
<div class="table-card"><div class="table-heading"><div><h2>Matched materials</h2><p>Mark a pickup completed once the material has been collected.</p></div><span class="count-badge"><?= count($matches) ?> matches</span></div>
<?php if (!$matches): ?><div class="empty-state"><h3>No matches yet</h3><p>Go to Requests and accept a pending request to create your first match.</p><a class="btn btn-primary" href="requests.php">View requests</a></div>
<?php else: ?><div class="table-scroll"><table><thead><tr><th>Material</th><th>Owner → Requester</th><th>Quantity</th><th>Pickup status</th><th>Pickup date</th><th>Update</th></tr></thead><tbody>
<?php foreach ($matches as $m): ?><tr><td><strong><?= e($m['title']) ?></strong><small>Match #<?= (int)$m['match_id'] ?></small></td><td><?= e($m['owner_name']) ?><small>to <?= e($m['requester_name']) ?></small></td><td><?= e($m['requested_quantity']) ?> <?= e($m['unit']) ?></td><td><span class="status-pill status-<?= strtolower(str_replace(' ','-',e($m['pickup_status']))) ?>"><?= e($m['pickup_status']) ?></span></td><td><?= $m['pickup_date']?e(date('d M Y',strtotime($m['pickup_date']))):'<span class="muted">Not scheduled</span>' ?></td><td><?php if (in_array($m['pickup_status'],['Scheduled','In Progress'],true)): ?><form method="post" class="action-inline"><input type="hidden" name="pickup_id" value="<?= (int)$m['pickup_id'] ?>"><button class="btn btn-small btn-primary" name="action" value="complete" data-confirm="Mark this pickup completed?">Complete</button><button class="btn btn-small btn-light" name="action" value="cancel" data-confirm="Cancel this pickup and reopen the item?">Cancel</button></form><?php else: ?><span class="muted">—</span><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></div>
<?php require 'includes/footer.php'; ?>
