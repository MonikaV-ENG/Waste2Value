<?php
require_once 'config.php';
$pageTitle = 'Post Waste';
$pdo = db();
$categories = $pdo->query("SELECT category_id, category_name FROM waste_categories ORDER BY category_name")->fetchAll();
$locations = $pdo->query("SELECT location_id, area, city FROM locations ORDER BY city, area")->fetchAll();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? ''); $userType = $_POST['user_type'] ?? 'Owner';
    $title = trim($_POST['title'] ?? ''); $description = trim($_POST['description'] ?? '');
    $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $locationId = filter_input(INPUT_POST, 'location_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_FLOAT);
    $unit = trim($_POST['unit'] ?? 'kg'); $condition = $_POST['condition_note'] ?? 'Good'; $urgency = $_POST['urgency'] ?? 'Normal';
    if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Enter a name (up to 100 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!in_array($userType, ['Owner','Recycler','Both'], true)) $errors[] = 'Choose a valid user type.';
    if ($title === '' || mb_strlen($title) > 150) $errors[] = 'Enter an item title (up to 150 characters).';
    if (!$categoryId) $errors[] = 'Choose a category.';
    if (!$locationId) $errors[] = 'Choose a location.';
    if ($quantity === false || $quantity <= 0) $errors[] = 'Quantity must be greater than zero.';
    if ($unit === '' || mb_strlen($unit) > 30) $errors[] = 'Enter a valid unit.';
    if (!in_array($condition, ['New','Good','Fair','Damaged','Mixed'], true)) $errors[] = 'Choose a valid condition.';
    if (!in_array($urgency, ['Low','Normal','Urgent'], true)) $errors[] = 'Choose a valid urgency.';
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $find = $pdo->prepare("SELECT user_id FROM users WHERE email=?"); $find->execute([$email]); $userId = $find->fetchColumn();
            if (!$userId) {
                $addUser = $pdo->prepare("INSERT INTO users (name,email,phone,user_type) VALUES (?,?,?,?)");
                $addUser->execute([$name,$email,$phone ?: null,$userType]); $userId = (int)$pdo->lastInsertId();
            } else {
                $userId = (int)$userId;
                $updateUser = $pdo->prepare("UPDATE users SET name=?, phone=?, user_type=? WHERE user_id=?");
                $updateUser->execute([$name,$phone ?: null,$userType,$userId]);
            }
            $addItem = $pdo->prepare("INSERT INTO waste_items (user_id,category_id,location_id,title,description,quantity,unit,condition_note,urgency) VALUES (?,?,?,?,?,?,?,?,?)");
            $addItem->execute([$userId,$categoryId,$locationId,$title,$description ?: null,$quantity,$unit,$condition,$urgency]);
            $pdo->commit(); flash('success','Your material was listed successfully.'); redirect('waste.php');
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Could not save the item. Check that the database has been installed and try again.';
        }
    }
}
require 'includes/header.php';
?>
<section class="page-heading"><span class="eyebrow">MAKE A LISTING</span><h1>Post a reusable material</h1><p>Share a few details so the right person can find and reuse it.</p></section>
<?php if ($errors): ?><div class="alert alert-error"><strong>Please fix the following:</strong><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form class="form-card" method="post">
<div class="form-section-title"><span class="form-step">01</span><div><h2>Contact details</h2><p>How can an interested person reach you?</p></div></div>
<div class="form-grid"><label>Full name <span class="required">*</span><input name="name" required maxlength="100" value="<?= e($_POST['name'] ?? '') ?>" placeholder="e.g. Monika V"></label><label>Email address <span class="required">*</span><input type="email" name="email" required maxlength="150" value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com"></label><label>Phone number<input name="phone" maxlength="25" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="Optional"></label><label>I am a<select name="user_type"><option value="Owner">Material owner</option><option value="Recycler" <?= ($_POST['user_type'] ?? '')==='Recycler'?'selected':'' ?>>Recycler</option><option value="Both" <?= ($_POST['user_type'] ?? '')==='Both'?'selected':'' ?>>Both</option></select></label></div>
<hr>
<div class="form-section-title"><span class="form-step">02</span><div><h2>Material information</h2><p>Describe what you want to give away or recycle.</p></div></div>
<div class="form-grid"><label class="span-2">Item title <span class="required">*</span><input name="title" required maxlength="150" value="<?= e($_POST['title'] ?? '') ?>" placeholder="e.g. Clean cardboard boxes"></label><label>Category <span class="required">*</span><select name="category_id" required><option value="">Select category</option><?php foreach ($categories as $cat): ?><option value="<?= (int)$cat['category_id'] ?>" <?= (string)($categoryId ?? ($_POST['category_id'] ?? ''))===(string)$cat['category_id']?'selected':'' ?>><?= e($cat['category_name']) ?></option><?php endforeach; ?></select></label><label>Description<textarea name="description" rows="3" placeholder="Condition, size, collection details..."><?= e($_POST['description'] ?? '') ?></textarea></label><label>Quantity <span class="required">*</span><div class="inline-input"><input type="number" name="quantity" min="0.01" step="0.01" required value="<?= e($_POST['quantity'] ?? '') ?>" placeholder="e.g. 10"><select name="unit"><option <?= ($_POST['unit'] ?? 'kg')==='kg'?'selected':'' ?>>kg</option><option <?= ($_POST['unit'] ?? '')==='items'?'selected':'' ?>>items</option><option <?= ($_POST['unit'] ?? '')==='pieces'?'selected':'' ?>>pieces</option><option <?= ($_POST['unit'] ?? '')==='bags'?'selected':'' ?>>bags</option><option <?= ($_POST['unit'] ?? '')==='litres'?'selected':'' ?>>litres</option></select></div></label><label>Condition<select name="condition_note"><?php foreach (['New','Good','Fair','Damaged','Mixed'] as $v): ?><option <?= ($_POST['condition_note'] ?? 'Good')===$v?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?></select></label><label>Pickup urgency<select name="urgency"><?php foreach (['Low','Normal','Urgent'] as $v): ?><option <?= ($_POST['urgency'] ?? 'Normal')===$v?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?></select></label><label>Pickup area <span class="required">*</span><select name="location_id" required><option value="">Select area</option><?php foreach ($locations as $loc): ?><option value="<?= (int)$loc['location_id'] ?>" <?= (string)($_POST['location_id'] ?? '')===(string)$loc['location_id']?'selected':'' ?>><?= e($loc['area']) ?>, <?= e($loc['city']) ?></option><?php endforeach; ?></select></label></div>
<div class="form-actions"><a href="waste.php" class="btn btn-light">Cancel</a><button class="btn btn-primary" type="submit">Publish listing →</button></div>
</form>
<?php require 'includes/footer.php'; ?>
