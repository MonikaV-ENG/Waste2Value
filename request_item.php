<?php
require_once 'config.php';
$pdo = db();
$itemId = filter_input(INPUT_GET, 'item_id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare("SELECT wi.*, wc.category_name, l.area, l.city, u.name AS owner_name FROM waste_items wi JOIN waste_categories wc ON wi.category_id=wc.category_id JOIN locations l ON wi.location_id=l.location_id JOIN users u ON wi.user_id=u.user_id WHERE wi.item_id=?");
$stmt->execute([$itemId]); $item = $stmt->fetch();
if (!$item) { http_response_code(404); $pageTitle='Item not found'; require 'includes/header.php'; echo '<div class="empty-state"><h2>Item not found</h2><a href="waste.php" class="btn btn-primary">Back to materials</a></div>'; require 'includes/footer.php'; exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $email = strtolower(trim($_POST['email'] ?? '')); $phone = trim($_POST['phone'] ?? '');
    $quantity = filter_input(INPUT_POST, 'requested_quantity', FILTER_VALIDATE_FLOAT); $message = trim($_POST['message'] ?? '');
    if ($name === '') $errors[]='Enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid email address.';
    if ($quantity === false || $quantity <= 0 || $quantity > (float)$item['quantity']) $errors[]='Requested quantity must be greater than zero and cannot exceed the listed quantity.';
    if ($item['status'] !== 'Available') $errors[]='This item is no longer available for new requests.';
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $q=$pdo->prepare("SELECT user_id FROM users WHERE email=?"); $q->execute([$email]); $userId=$q->fetchColumn();
            if (!$userId) { $q=$pdo->prepare("INSERT INTO users (name,email,phone,user_type) VALUES (?,?,?,'Both')"); $q->execute([$name,$email,$phone ?: null]); $userId=(int)$pdo->lastInsertId(); }
            else { $userId=(int)$userId; }
            if ($userId === (int)$item['user_id']) throw new RuntimeException('You cannot request your own listing.');
            $q=$pdo->prepare("INSERT INTO requests (item_id,requester_id,requested_quantity,message) VALUES (?,?,?,?)");
            $q->execute([$itemId,$userId,$quantity,$message ?: null]);
            $pdo->prepare("UPDATE waste_items SET status='Requested' WHERE item_id=? AND status='Available'")->execute([$itemId]);
            $pdo->commit(); flash('success','Request submitted! The owner can review it on the Requests page.'); redirect('requests.php');
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = $ex instanceof RuntimeException ? $ex->getMessage() : 'Could not submit the request. Please try again.';
        }
    }
}
$pageTitle='Request Material'; require 'includes/header.php';
?>
<section class="page-heading"><span class="eyebrow">REQUEST MATERIAL</span><h1>Request this item</h1><p>Send a request to the listing owner. This demo does not send emails or messages automatically.</p></section>
<div class="request-layout"><aside class="summary-card"><span class="category-pill"><?= e($item['category_name']) ?></span><div class="item-illustration">♻</div><h2><?= e($item['title']) ?></h2><p><?= e($item['description'] ?? '') ?></p><div class="summary-row"><span>Available quantity</span><strong><?= e($item['quantity']) ?> <?= e($item['unit']) ?></strong></div><div class="summary-row"><span>Location</span><strong><?= e($item['area']) ?>, <?= e($item['city']) ?></strong></div><div class="summary-row"><span>Listed by</span><strong><?= e($item['owner_name']) ?></strong></div></aside>
<form class="form-card compact-form" method="post"><input type="hidden" name="item_id" value="<?= (int)$itemId ?>"><h2>Your request details</h2><?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?><label>Full name<input name="name" required value="<?= e($_POST['name'] ?? '') ?>"></label><label>Email address<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label><label>Phone number<input name="phone" value="<?= e($_POST['phone'] ?? '') ?>"></label><label>Quantity needed (<?= e($item['unit']) ?>)<input type="number" name="requested_quantity" min="0.01" max="<?= e($item['quantity']) ?>" step="0.01" required value="<?= e($_POST['requested_quantity'] ?? $item['quantity']) ?>"></label><label>Message to owner<textarea name="message" rows="4" placeholder="Tell the owner how you plan to reuse this material."><?= e($_POST['message'] ?? '') ?></textarea></label><div class="form-actions"><a href="waste.php" class="btn btn-light">Back</a><button class="btn btn-primary" type="submit" <?= $item['status']!=='Available'?'disabled':'' ?>>Submit request</button></div></form></div>
<?php require 'includes/footer.php'; ?>
