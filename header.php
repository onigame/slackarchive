<?php
require_once 'config.php';
$publicPages = ['login.php', 'tos.php', 'privacy.php'];
$currentPage = basename($_SERVER['PHP_SELF']);

if (empty($_SESSION['authenticated']) && !in_array($currentPage, $publicPages)) {
    header('Location: login.php');
    exit;
}

$dateRange = '';
$dateFile = __DIR__ . '/date_range.txt';
if (file_exists($dateFile)) {
    $dateRange = ' (' . file_get_contents($dateFile) . ')';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Left Out Slack Archive</title>
    <link rel="stylesheet" href="index.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">Left Out Slack Archive<span style="font-size: 0.9rem; font-weight: 400; color: #ddd;"><?= htmlspecialchars($dateRange) ?></span></div>
        <div class="nav-links">
            <?php if (!empty($_SESSION['authenticated'])): ?>
                <a href="index.php">Channels</a>
                <a href="search.php">Search</a>
                <a href="auth.php?logout=1">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="tos.php">Terms</a>
                <a href="privacy.php">Privacy</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="container">
