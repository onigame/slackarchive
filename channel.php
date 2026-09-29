<?php
require_once 'header.php';
$pdo = getDB();

$channelId = $_GET['id'] ?? '';
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$limit = 50;
$offset = ($page - 1) * $limit;

if (!empty($_GET['ts']) && empty($_GET['page'])) {
    $targetTs = $_GET['ts'];
    $stmtMsg = $pdo->prepare("SELECT thread_ts, ts FROM messages WHERE channel_id = ? AND ts = ?");
    $stmtMsg->execute([$channelId, $targetTs]);
    $msg = $stmtMsg->fetch();
    if ($msg) {
        $parentTs = (!empty($msg['thread_ts']) && $msg['thread_ts'] !== $msg['ts']) ? $msg['thread_ts'] : $msg['ts'];
        $stmtRank = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE channel_id = ? AND (thread_ts IS NULL OR thread_ts = ts) AND ts <= ?");
        $stmtRank->execute([$channelId, $parentTs]);
        $rank = $stmtRank->fetchColumn();
        $page = ceil($rank / $limit);
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM channels WHERE id = ?");
$stmt->execute([$channelId]);
$channel = $stmt->fetch();

if (!$channel) die("Channel not found.");

$stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE channel_id = ? AND (thread_ts IS NULL OR thread_ts = ts)");
$stmt->execute([$channelId]);
$total = $stmt->fetchColumn();
$totalPages = ceil($total / $limit);

$stmt = $pdo->prepare("SELECT m.*, u.username, u.avatar_url, u.real_name FROM messages m LEFT JOIN users u ON m.user_id = u.id WHERE m.channel_id = ? AND (m.thread_ts IS NULL OR m.thread_ts = m.ts) ORDER BY m.ts ASC LIMIT $limit OFFSET $offset");
$stmt->execute([$channelId]);
$messages = $stmt->fetchAll();
?>
<div class="glass-panel">
    <h1>#<?= htmlspecialchars($channel['name']) ?></h1>
    <div class="pagination" style="margin-top:0; margin-bottom: 2rem;">
        <?php if ($page > 1): ?>
            <a href="?id=<?= urlencode($channelId) ?>&page=<?= $page - 1 ?>" class="btn">Previous</a>
        <?php endif; ?>
        <span>Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
            <a href="?id=<?= urlencode($channelId) ?>&page=<?= $page + 1 ?>" class="btn">Next</a>
        <?php endif; ?>
    </div>
    
    <div class="messages">
        <?php foreach ($messages as $msg): ?>
            <div class="message" id="msg-<?= htmlspecialchars($msg['ts']) ?>">
                <img src="<?= htmlspecialchars($msg['avatar_url'] ?: 'https://ui-avatars.com/api/?name='.urlencode($msg['real_name'] ?: $msg['username'] ?: 'U')) ?>" alt="avatar">
                <div class="message-content">
                    <div class="message-header">
                        <strong><?= htmlspecialchars($msg['real_name'] ?: $msg['username'] ?: 'Unknown User') ?></strong>
                        <span class="timestamp"><?= gmdate("Y-m-d H:i:s", (int)$msg['ts']) ?></span>
                    </div>
                    <div class="text"><?= nl2br(htmlspecialchars($msg['text'])) ?></div>
                    
                    <?php
                    $stmtReplies = $pdo->prepare("SELECT m.*, u.username, u.avatar_url, u.real_name FROM messages m LEFT JOIN users u ON m.user_id = u.id WHERE m.channel_id = ? AND m.thread_ts = ? AND m.ts != ? ORDER BY m.ts ASC");
                    $stmtReplies->execute([$channelId, $msg['ts'], $msg['ts']]);
                    $replies = $stmtReplies->fetchAll();
                    ?>
                    <?php if (count($replies) > 0): ?>
                        <div class="replies">
                            <?php foreach ($replies as $reply): ?>
                                <div class="message reply" id="msg-<?= htmlspecialchars($reply['ts']) ?>">
                                    <img src="<?= htmlspecialchars($reply['avatar_url'] ?: 'https://ui-avatars.com/api/?name='.urlencode($reply['real_name'] ?: $reply['username'] ?: 'U')) ?>" alt="avatar">
                                    <div class="message-content">
                                        <div class="message-header">
                                            <strong><?= htmlspecialchars($reply['real_name'] ?: $reply['username'] ?: 'Unknown User') ?></strong>
                                            <span class="timestamp"><?= gmdate("Y-m-d H:i:s", (int)$reply['ts']) ?></span>
                                        </div>
                                        <div class="text"><?= nl2br(htmlspecialchars($reply['text'])) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?id=<?= urlencode($channelId) ?>&page=<?= $page - 1 ?>" class="btn">Previous</a>
        <?php endif; ?>
        <span>Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
            <a href="?id=<?= urlencode($channelId) ?>&page=<?= $page + 1 ?>" class="btn">Next</a>
        <?php endif; ?>
    </div>
</div>
</main>
</body>
</html>
