<?php
require_once __DIR__ . '/../config.php';
$pageTitle = $pageTitle ?? APP_NAME;
$flashMessage = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | Waste2Value</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
  <div class="container nav-wrap">
    <a class="brand" href="index.php"><span class="brand-mark">W2V</span><span>Waste<span class="brand-accent">2</span>Value</span></a>
    <button class="nav-toggle" type="button" aria-label="Toggle navigation" onclick="document.body.classList.toggle('nav-open')">☰</button>
    <nav class="main-nav">
      <a href="index.php">Dashboard</a>
      <a href="waste.php">Browse Waste</a>
      <a href="post_waste.php">Post Waste</a>
      <a href="requests.php">Requests</a>
      <a href="matches.php">Matches</a>
      <a href="about.php">About</a>
    </nav>
  </div>
</header>
<main class="container page-main">
<?php if ($flashMessage): ?>
  <div class="alert alert-<?= e($flashMessage['type']) ?>"><?= e($flashMessage['message']) ?></div>
<?php endif; ?>
