<?php
require_once 'config.php';
$pageTitle = 'Browse Waste';
$pdo = db();
$categories = $pdo->query("SELECT category_id, category_name FROM waste_categories ORDER BY category_name")->fetchAll();
$search = trim($_GET['q'] ?? '');
$categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?: 0;
$sql = "SELECT wi.*, wc.category_name, l.area, l.city, u.name AS owner_name
        FROM waste_items wi JOIN waste_categories wc ON wi.category_id=wc.category_id
        JOIN locations l ON wi.location_id=l.location_id JOIN users u ON wi.user_id=u.user_id
        WHERE wi.status IN ('Available','Requested','Matched')";
$params = [];
if ($search !== '') {
    $sql .= " AND (wi.title LIKE ? OR wi.description LIKE ? OR l.area LIKE ? OR l.city LIKE ?)";
    $like = '%' . $search . '%'; array_push($params, $like, $like, $like, $like);
}
if ($categoryId > 0) { $sql .= " AND wi.category_id = ?"; $params[] = $categoryId; }
$sql .= " ORDER BY CASE wi.urgency WHEN 'Urgent' THEN 0 WHEN 'Normal' THEN 1 ELSE 2 END, wi.created_at DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $items = $stmt->fetchAll();
require 'includes/header.php';
?>
<section class="page-heading"><span class="eyebrow">COMMUNITY EXCHANGE</span><h1>Browse available materials</h1><p>Find reusable items and recyclable materials in your area.</p></section>
<form class="filter-bar" method="get"><label class="search-box"><span>⌕</span><input type="search" name="q" value="<?= e($search) ?>" placeholder="Search material or location..."></label><select name="category"><option value="0">All categories</option><?php foreach ($categories as $cat): ?><option value="<?= (int)$cat['category_id'] ?>" <?= $categoryId===(int)$cat['category_id']?'selected':'' ?>><?= e($cat['category_name']) ?></option><?php endforeach; ?></select><button class="btn btn-primary" type="submit">Search</button><a class="btn btn-light" href="waste.php">Reset</a></form>
<div class="results-line"><strong><?= count($items) ?> materials found</strong><span>Showing local reuse opportunities</span></div>
<div class="item-grid">
<?php if (!$items): ?><div class="empty-state"><h3>No matching materials found</h3><p>Try another search or post a new item.</p><a class="btn btn-primary" href="post_waste.php">Post an item</a></div>
<?php else: foreach ($items as $item): ?>
<article class="item-card"><div class="item-card-top"><span class="category-pill"><?= e($item['category_name']) ?></span><span class="status-pill status-<?= strtolower(e($item['status'])) ?>"><?= e($item['status']) ?></span></div><div class="item-illustration"><?= $item['category_name']==='Electronics'?'⌘':($item['category_name']==='Metal'?'⚙':($item['category_name']==='Furniture'?'▤':($item['category_name']==='Plastic'?'◉':'♧'))) ?></div><h3><?= e($item['title']) ?></h3><p class="item-description"><?= e($item['description'] ?: 'No additional description provided.') ?></p><div class="item-facts"><span><b>Quantity</b><?= e($item['quantity']) ?> <?= e($item['unit']) ?></span><span><b>Condition</b><?= e($item['condition_note']) ?></span></div><p class="location-line">⌖ <?= e($item['area']) ?>, <?= e($item['city']) ?></p><p class="owner-line">Listed by <?= e($item['owner_name']) ?></p><?php if ($item['status']==='Available'): ?><a class="btn btn-primary btn-full" href="request_item.php?item_id=<?= (int)$item['item_id'] ?>">Request material</a><?php else: ?><button class="btn btn-disabled btn-full" disabled><?= e($item['status']) ?></button><?php endif; ?></article>
<?php endforeach; endif; ?>
</div>
<?php require 'includes/footer.php'; ?>
