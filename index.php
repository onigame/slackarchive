<?php
require_once 'header.php';
$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM channels ORDER BY name ASC");
$channels = $stmt->fetchAll();
?>
<div class="glass-panel">
    <h1>Channels</h1>
    <ul class="channel-list">
        <?php foreach ($channels as $channel): ?>
            <li>
                <a href="channel.php?id=<?= urlencode($channel['id']) ?>">#<?= htmlspecialchars($channel['name']) ?></a>
                <p><?= htmlspecialchars($channel['description'] ?? '') ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
</main>
</body>
</html>
