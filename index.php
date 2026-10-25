<?php
require_once 'config.php';
$pageTitle = 'Dashboard';
try {
    $pdo = db();
    $totalItems = (int)$pdo->query("SELECT COUNT(*) FROM waste_items")->fetchColumn();
    $availableItems = (int)$pdo->query("SELECT COUNT(*) FROM waste_items WHERE status='Available'")->fetchColumn();
    $totalRequests = (int)$pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn();
    $completedPickups = (int)$pdo->query("SELECT COUNT(*) FROM pickups WHERE status='Completed'")->fetchColumn();
    $latest = $pdo->query("SELECT wi.item_id, wi.title, wi.quantity, wi.unit, wi.condition_note, wi.status, wc.category_name, l.area, l.city
        FROM waste_items wi JOIN waste_categories wc ON wi.category_id=wc.category_id
        JOIN locations l ON wi.location_id=l.location_id ORDER BY wi.created_at DESC LIMIT 4")->fetchAll();
} catch (Throwable $ex) {
    $setupNeeded = true;
}
require 'includes/header.php';
?>
<?php if (!empty($setupNeeded)): ?>
<section class="hero">
  <div class="hero-copy"><span class="eyebrow">FIRST-TIME SETUP</span><h1>Let's turn waste into <span class="text-green">value.</span></h1>
  <p>Set up the database once, then start using the Waste2Value mini project.</p>
  <a class="btn btn-primary" href="install.php">Install / Set up database</a></div>
  <div class="hero-art"><div class="leaf-circle">↻</div><div class="art-caption">Reuse · Recycle · Revalue</div></div>
</section>
<div class="alert alert-info">Database not found or not configured yet. Click “Install / Set up database”. On standard XAMPP, the default database settings should work.</div>
<?php else: ?>
<section class="hero">
  <div class="hero-copy">
    <span class="eyebrow">SMART WASTE EXCHANGE</span>
    <h1>One person's waste.<br><span class="text-green">Another's resource.</span></h1>
    <p>Connect households, hostels, colleges, restaurants and workshops with people who can reuse or recycle their materials.</p>
    <div class="hero-actions"><a class="btn btn-primary" href="post_waste.php">＋ Post an item</a><a class="btn btn-light" href="waste.php">Explore materials →</a></div>
    <div class="hero-points"><span>✓ Local reuse</span><span>✓ Less landfill</span><span>✓ Circular economy</span></div>
  </div>
  <div class="hero-art"><div class="art-orbit orbit-one"></div><div class="art-orbit orbit-two"></div><div class="plant">♻</div><div class="floating-card card-top"><span class="mini-icon">↗</span><span><b>Give materials</b><small>a second life</small></span></div><div class="floating-card card-bottom"><span class="mini-icon orange">✦</span><span><b>Find local value</b><small>near your area</small></span></div></div>
</section>
<section class="stats-grid">
  <div class="stat-card"><span class="stat-icon green-bg">♻</span><div><p>Items listed</p><strong><?= $totalItems ?></strong></div></div>
  <div class="stat-card"><span class="stat-icon blue-bg">⌕</span><div><p>Available now</p><strong><?= $availableItems ?></strong></div></div>
  <div class="stat-card"><span class="stat-icon purple-bg">⇄</span><div><p>Reuse requests</p><strong><?= $totalRequests ?></strong></div></div>
  <div class="stat-card"><span class="stat-icon orange-bg">✓</span><div><p>Pickups completed</p><strong><?= $completedPickups ?></strong></div></div>
</section>
<section class="section-heading"><div><span class="eyebrow">FRESHLY LISTED</span><h2>Materials looking for a new home</h2><p>Explore recently listed items from your community.</p></div><a class="text-link" href="waste.php">View all materials →</a></section>
<div class="item-grid">
<?php if (!$latest): ?><div class="empty-state"><h3>No items yet</h3><p>Be the first to list a reusable material.</p><a class="btn btn-primary" href="post_waste.php">Post waste</a></div>
<?php else: foreach ($latest as $item): ?>
<article class="item-card"><div class="item-card-top"><span class="category-pill"><?= e($item['category_name']) ?></span><span class="status-pill"><?= e($item['status']) ?></span></div><div class="item-illustration"><?= $item['category_name']==='Electronics'?'⌘':($item['category_name']==='Metal'?'⚙':($item['category_name']==='Furniture'?'▤':($item['category_name']==='Plastic'?'◉':'♧'))) ?></div><h3><?= e($item['title']) ?></h3><p class="item-meta"><?= e($item['quantity']) ?> <?= e($item['unit']) ?> · <?= e($item['condition_note']) ?></p><p class="location-line">⌖ <?= e($item['area']) ?>, <?= e($item['city']) ?></p><a class="card-link" href="waste.php">View details <span>→</span></a></article>
<?php endforeach; endif; ?>
</div>
<section class="how-section"><div><span class="eyebrow">HOW IT WORKS</span><h2>Waste exchange in three simple steps</h2></div><div class="steps-grid"><div class="step"><span>01</span><h3>List it</h3><p>Post materials you no longer need with quantity and location.</p></div><div class="step"><span>02</span><h3>Match it</h3><p>Interested people send a request for available materials.</p></div><div class="step"><span>03</span><h3>Reuse it</h3><p>Coordinate pickup and give materials a useful second life.</p></div></div></section>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
