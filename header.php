<?php
require_once 'config.php';
if (empty($_SESSION['authenticated'])) {
    header('Location: auth.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slack Archive</title>
    <link rel="stylesheet" href="index.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">Slack Archive</div>
        <div class="nav-links">
            <a href="index.php">Channels</a>
            <a href="search.php">Search</a>
            <a href="auth.php?logout=1">Logout</a>
        </div>
    </nav>
    <main class="container">
