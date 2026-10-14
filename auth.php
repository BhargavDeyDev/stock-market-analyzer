<?php
ob_start();
set_time_limit(20);
error_reporting(0);
ini_set('display_errors', 0);

register_shutdown_function(function() {
    $err = error_get_last();
    $out = ob_get_clean();
    json_decode($out);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => 'Server error: ' . ($err['message'] ?? substr($out,0,200))]);
    } else {
        echo $out;
    }
});

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

// ── PDO with sql111.ezyro.com — CONFIRMED working via dbtest ─────
function db_connect() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    try {
        $dsn = 'mysql:host=sql111.ezyro.com;port=3306;dbname=ezyro_41324459_stockry;charset=utf8mb4';
        $pdo = new PDO($dsn, '#', '#', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 15,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ]);
    } catch (PDOException $e) {
        return null;
    }
    return $pdo;
}

function csrf_ok($t) { return isset($_SESSION['csrf']) && is_string($t) && hash_equals($_SESSION['csrf'], $t); }
function xc($s) { return htmlspecialchars(strip_tags(trim((string)$s))); }
function jerr($m) { ob_clean(); echo json_encode(['error'=>$m]); ob_end_flush(); exit; }
function jok($d)  { ob_clean(); echo json_encode($d);            ob_end_flush(); exit; }

$action = $_POST['action'] ?? '';

// ════════════════════════════════════════════════════════════════
//  SIGNUP
// ════════════════════════════════════════════════════════════════
if ($action === 'signup') {
    if (!csrf_ok($_POST['csrf'] ?? ''))
        jerr('Session expired. Please refresh and try again.');

    $name  = xc($_POST['name']  ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $cc    = xc($_POST['country_code'] ?? '+1');
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
    $pass  = $_POST['password']  ?? '';
    $pass2 = $_POST['password2'] ?? '';

    $errs = [];
    if (strlen($name)  < 2)                         $errs[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = 'Invalid email address.';
    if (strlen($phone) < 7)                         $errs[] = 'Invalid contact number.';
    if (strlen($pass)  < 8)                         $errs[] = 'Password must be at least 8 characters.';
    if ($pass !== $pass2)                           $errs[] = 'Passwords do not match.';
    if ($errs) jerr(implode(' ', $errs));

    $db = db_connect();
    if (!$db) jerr('Database connection failed. Please try again.');

    // Duplicate email
    $st = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $st->execute([$email]);
    if ($st->fetch()) jerr('This email is already registered. Please sign in.');

    // Duplicate phone
    $st = $db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
    $st->execute([$phone]);
    if ($st->fetch()) jerr('This phone number is already registered.');

    // Insert
    $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 10]);
    $st = $db->prepare(
        "INSERT INTO users (name, email, country_code, phone, password, is_active, created_at)
         VALUES (?, ?, ?, ?, ?, 1, NOW())"
    );
    $st->execute([$name, $email, $cc, $phone, $hash]);
    $uid = (int)$db->lastInsertId();

    if (!$uid) jerr('Account creation failed. Please try again.');

    $_SESSION['user_id'] = $uid;
    $_SESSION['user']    = ['id'=>$uid, 'name'=>$name, 'email'=>$email];

    jok(['ok'=>true, 'name'=>$name, 'redirect'=>'https://stockrypredict.unaux.com/?page=markets']);
}

// ════════════════════════════════════════════════════════════════
//  SIGNIN
// ════════════════════════════════════════════════════════════════
if ($action === 'signin') {
    if (!csrf_ok($_POST['csrf'] ?? ''))
        jerr('Session expired. Please refresh and try again.');

    $login = strtolower(trim($_POST['login'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    if (!$login || !$pass) jerr('Please enter your email/phone and password.');

    $db = db_connect();
    if (!$db) jerr('Database connection failed. Please try again.');

    $user = null;

    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $st = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $st->execute([$login]);
        $user = $st->fetch();
    }

    if (!$user) {
        $digits = preg_replace('/[^0-9]/', '', $login);
        if (strlen($digits) >= 7) {
            $st = $db->prepare("SELECT * FROM users WHERE phone = ? LIMIT 1");
            $st->execute([$digits]);
            $user = $st->fetch();
        }
    }

    if (!$user || !password_verify($pass, $user['password']))
        jerr('Invalid email/phone or password.');

    if (!(int)$user['is_active'])
        jerr('This account has been deactivated.');

    $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user']    = ['id'=>$user['id'], 'name'=>$user['name'], 'email'=>$user['email']];

    jok(['ok'=>true, 'name'=>$user['name'],
         'redirect'=>$_POST['redirect'] ?? 'https://stockrypredict.unaux.com/?page=markets']);
}

// ════════════════════════════════════════════════════════════════
//  SIGNOUT
// ════════════════════════════════════════════════════════════════
if ($action === 'signout') {
    session_destroy();
    jok(['ok'=>true, 'redirect'=>'https://stockrypredict.unaux.com']);
}

jerr('Unknown action.');
