<?php
// DELETE AFTER TESTING
set_time_limit(30);
$u = '#';
$p = '#';
$d = '#_stockry';

echo '<pre style="background:#111;color:#eee;padding:20px;font-family:monospace;font-size:13px">';
echo "PHP: ".PHP_VERSION." | Server: ".($_SERVER['SERVER_NAME']??'?')."\n\n";

// Test 1: PDO with localhost (no port = forces Unix socket)
echo "--- PDO Tests ---\n";
$dsns = [
    "mysql:host=localhost;dbname=$d;charset=utf8mb4"                           => "PDO localhost (socket)",
    "mysql:host=localhost;port=3306;dbname=$d;charset=utf8mb4"                 => "PDO localhost:3306",
    "mysql:host=127.0.0.1;port=3306;dbname=$d;charset=utf8mb4"                => "PDO 127.0.0.1:3306",
    "mysql:host=sql111.ezyro.com;port=3306;dbname=$d;charset=utf8mb4"         => "PDO sql111.ezyro.com",
    "mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=$d;charset=utf8mb4" => "PDO unix_socket /var/run/mysqld",
    "mysql:unix_socket=/tmp/mysql.sock;dbname=$d;charset=utf8mb4"             => "PDO unix_socket /tmp/mysql.sock",
    "mysql:unix_socket=/var/lib/mysql/mysql.sock;dbname=$d;charset=utf8mb4"   => "PDO unix_socket /var/lib/mysql",
];
foreach ($dsns as $dsn => $label) {
    try {
        $pdo = new PDO($dsn, $u, $p, [PDO::ATTR_TIMEOUT=>5, PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        $cnt = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "✅ SUCCESS: $label | users=$cnt\n";
    } catch(Exception $e) {
        $msg = str_replace($p,'***',$e->getMessage());
        echo "❌ $label\n   ".substr($msg,0,100)."\n";
    }
}

// Test 2: mysqli variations
echo "\n--- mysqli Tests ---\n";
$mysqli_hosts = [
    ['localhost',null,null],
    ['127.0.0.1',3306,null],
    ['sql111.ezyro.com',3306,null],
    [null,null,'/var/run/mysqld/mysqld.sock'],
    [null,null,'/tmp/mysql.sock'],
];
foreach ($mysqli_hosts as [$h,$port,$sock]) {
    $label = $sock ? "socket:$sock" : "$h:".($port??'default');
    if ($sock) {
        $c = @mysqli_connect(null,$u,$p,$d,null,$sock);
    } else {
        $c = @mysqli_connect($h,$u,$p,$d,$port);
    }
    if ($c) {
        $r = mysqli_query($c,"SELECT COUNT(*) as n FROM users");
        $n = mysqli_fetch_assoc($r)['n'];
        echo "✅ SUCCESS: $label | users=$n\n";
        mysqli_close($c);
    } else {
        echo "❌ $label — ".substr(mysqli_connect_error()??'timeout',0,100)."\n";
    }
}

echo "\n--- Socket file existence ---\n";
$socks = ['/var/run/mysqld/mysqld.sock','/tmp/mysql.sock','/var/lib/mysql/mysql.sock'];
foreach($socks as $s) echo (file_exists($s)?"✅ EXISTS":"❌ NOT FOUND").": $s\n";

echo '</pre><p style="color:red">DELETE dbtest.php after done!</p>';
