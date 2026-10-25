<?php
require_once 'config.php';
$pageTitle='About Waste2Value';
require 'includes/header.php';
?>
<section class="page-heading"><span class="eyebrow">ABOUT THE PROJECT</span><h1>Small actions. Less waste. More value.</h1><p>Waste2Value is a DBMS mini project that demonstrates how a local material exchange can connect people who have reusable waste with people who can reuse or recycle it.</p></section>
<div class="about-grid"><article class="feature-card"><div class="feature-icon">♻</div><h2>Problem statement</h2><p>Reusable materials are often discarded because owners do not know who nearby could use them. Waste2Value organizes listings and requests in one place.</p></article><article class="feature-card"><div class="feature-icon">▦</div><h2>Database concepts</h2><p>Relational tables, primary and foreign keys, constraints, joins, filtering, insert/update operations, and transactional request acceptance.</p></article><article class="feature-card"><div class="feature-icon">⇄</div><h2>Workflow</h2><p>Post a material, browse or search listings, submit a request, accept or reject the request, and track the resulting pickup.</p></article></div>
<section class="schema-section"><h2>Database schema</h2><p>The installer creates these 10 connected tables:</p><div class="schema-pills"><?php foreach (['users','waste_categories','locations','waste_items','requests','matches','pickups','recycling_centers','transactions','reviews'] as $table): ?><span><?= e($table) ?></span><?php endforeach; ?></div><div class="alert alert-info">For the academic demonstration, this version uses sample data and a shared demo workflow. It does not implement login, email notifications, or real payment processing.</div></section>
<?php require 'includes/footer.php'; ?>
