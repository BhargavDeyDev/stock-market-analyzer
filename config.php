<?php
// ─── Database — CONFIRMED WORKING via dbtest.php ─────────────────
// sql111.ezyro.com with PDO is the only working connection method
define('DB_HOST', 'sql111.ezyro.com');
define('DB_PORT', '3306');
define('DB_USER', '#');
define('DB_PASS', '@');
define('DB_NAME', '#_stockry');

// ─── API Keys ─────────────────────────────────────────────────────
define('TWELVE_DATA_KEY', '#');
define('FINNHUB_KEY',     '#');
define('NEWS_API_KEY',    '#');
define('FRED_API_KEY',    '#');
define('GROQ_API_KEY',    '#');

// ─── App ──────────────────────────────────────────────────────────
define('SITE_URL',  'https://stockrypredict.unaux.com');
define('SITE_NAME', 'Stockry');

// ─── Session ──────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

// ─── DB — PDO with sql111.ezyro.com (confirmed working) ──────────
function getDB() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT
             . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 15,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ]);
    } catch (PDOException $e) {
        die(json_encode(['error' => 'DB error: ' . str_replace(DB_PASS, '***', $e->getMessage())]));
    }
    return $pdo;
}

// ─── Helpers ──────────────────────────────────────────────────────
function isLoggedIn()  { return !empty($_SESSION['user_id']); }
function currentUser() { return $_SESSION['user'] ?? null; }
function requireLogin() {
    if (!isLoggedIn()) { header('Location: ' . SITE_URL . '/'); exit; }
}
function clean($s) { return htmlspecialchars(strip_tags(trim($s))); }
function csrfToken() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verifyCsrf($t) { return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t); }
function apiGet($url) {
    // Use cURL if available (faster, better timeout control)
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT      => 'Stockry/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r ? json_decode($r, true) : null;
    }
    // Fallback to file_get_contents
    $ctx = stream_context_create(['http' => [
        'timeout' => 8,
        'header'  => "User-Agent: Stockry/1.0\r\n"
    ]]);
    $r = @file_get_contents($url, false, $ctx);
    return $r ? json_decode($r, true) : null;
}
