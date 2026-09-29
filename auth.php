<?php
require_once 'config.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if (DEV_MODE_BYPASS) {
    $_SESSION['authenticated'] = true;
    $_SESSION['discord_user'] = ['id' => 'dev_id', 'username' => 'dev_user'];
    header('Location: index.php');
    exit;
}

if (!isset($_GET['code'])) {
    $authUrl = "https://discord.com/api/oauth2/authorize?client_id=" . DISCORD_CLIENT_ID . "&redirect_uri=" . urlencode(DISCORD_REDIRECT_URI) . "&response_type=code&scope=identify%20guilds";
    header("Location: $authUrl");
    exit;
}

$code = $_GET['code'];
$tokenUrl = "https://discord.com/api/oauth2/token";
$data = [
    'client_id' => DISCORD_CLIENT_ID,
    'client_secret' => DISCORD_CLIENT_SECRET,
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => DISCORD_REDIRECT_URI
];
$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$response = curl_exec($ch);
curl_close($ch);
$tokenData = json_decode($response, true);

if (!isset($tokenData['access_token'])) {
    die("Failed to authenticate with Discord.");
}
$accessToken = $tokenData['access_token'];

$ch = curl_init("https://discord.com/api/users/@me");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
$user = json_decode(curl_exec($ch), true);
curl_close($ch);

$ch = curl_init("https://discord.com/api/users/@me/guilds");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
$guilds = json_decode(curl_exec($ch), true);
curl_close($ch);

$inGuild = false;
foreach ($guilds as $guild) {
    if ($guild['id'] == DISCORD_GUILD_ID) {
        $inGuild = true;
        break;
    }
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM allowed_exceptions WHERE discord_user_id = ?");
$stmt->execute([$user['id']]);
$isException = $stmt->fetch();

if ($inGuild || $isException) {
    $_SESSION['authenticated'] = true;
    $_SESSION['discord_user'] = $user;
    header('Location: index.php');
} else {
    die("Access Denied. You are not a member of the required Discord server.");
}
?>
