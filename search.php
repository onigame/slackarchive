<?php
require_once 'header.php';
$pdo = getDB();

$channels = $pdo->query("SELECT id, name FROM channels ORDER BY name ASC")->fetchAll();
$users = $pdo->query("SELECT id, username, real_name FROM users ORDER BY real_name ASC")->fetchAll();

$query = $_GET['q'] ?? '';
$channelFilter = $_GET['channel'] ?? '';
$userFilter = $_GET['user'] ?? '';
$results = [];

if ($query) {
    $sql = "SELECT m.*, c.name as channel_name, u.username, u.real_name FROM messages m 
            LEFT JOIN channels c ON m.channel_id = c.id 
            LEFT JOIN users u ON m.user_id = u.id 
            WHERE m.text LIKE ?";
    $params = ["%$query%"];
    if ($channelFilter) {
        $sql .= " AND m.channel_id = ?";
        $params[] = $channelFilter;
    }
    if ($userFilter) {
        $sql .= " AND m.user_id = ?";
        $params[] = $userFilter;
    }
    $sql .= " ORDER BY m.ts DESC LIMIT 100";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}
?>
<div class="glass-panel">
    <h1>Search</h1>
    <form method="GET" action="search.php" class="search-form">
        <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Search messages..." required>
        <select name="channel">
            <option value="">All Channels</option>
            <?php foreach ($channels as $c): ?>
                <option value="<?= htmlspecialchars($c['id']) ?>" <?= $channelFilter == $c['id'] ? 'selected' : '' ?>>#<?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="user">
            <option value="">All Users</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= htmlspecialchars($u['id']) ?>" <?= $userFilter == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['real_name'] ?: $u['username']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn">Search</button>
    </form>

    <?php if ($query): ?>
        <h2>Results</h2>
        <div class="messages">
            <?php foreach ($results as $res): ?>
                <div class="message">
                    <div class="message-content">
                        <div class="message-header">
                            <strong><?= htmlspecialchars($res['real_name'] ?: $res['username'] ?: 'Unknown User') ?></strong> in #<?= htmlspecialchars($res['channel_name']) ?>
                            <span class="timestamp"><?= gmdate("Y-m-d H:i:s", (int)$res['ts']) ?></span>
                        </div>
                        <div class="text"><?= nl2br(htmlspecialchars($res['text'])) ?></div>
                        <a href="channel.php?id=<?= urlencode($res['channel_id']) ?>&ts=<?= urlencode($res['ts']) ?>#msg-<?= htmlspecialchars($res['ts']) ?>" class="btn-small">Jump to Context</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</main>
</body>
</html>
