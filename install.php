<?php
if (($_GET['secret'] ?? '') !== 'stockry_install_2025') die('<h2>Forbidden</h2>');

$user  = '#9';
$pass  = '#';
$db    = '#_stockry';
$hosts = ['localhost', 'X', 'sql111.ezyro.com'];

$conn      = null;
$goodHost  = null;

echo '<!DOCTYPE html><html><head><style>body{font-family:monospace;padding:20px;background:#111;color:#eee}
.ok{color:#0f0}.err{color:#f44}.warn{color:#fa0}pre{background:#222;padding:10px;border-radius:6px}</style></head><body>';
echo '<h2>Stockry Install</h2><pre>';

foreach ($hosts as $host) {
    $c = @mysqli_connect($host, $user, $pass, $db, 3306);
    if ($c) {
        echo "<span class='ok'>✅ Connected via: $host</span>\n";
        $conn     = $c;
        $goodHost = $host;
        break;
    } else {
        echo "<span class='err'>❌ Failed $host: " . mysqli_connect_error() . "</span>\n";
    }
}

if (!$conn) {
    echo "<span class='err'>❌ ALL connection methods failed. Your MySQL may need to be reset from the hosting control panel.\n";
    echo "Go to: Profreehost cPanel → MySQL Databases → Delete and recreate the database.</span>\n";
    echo '</pre></body></html>'; exit;
}

mysqli_set_charset($conn, 'utf8mb4');
echo "\n<span class='warn'>Use this in config.php → DB_HOST = '$goodHost'</span>\n\n";

// Create tables
$tables = [
'users' => "CREATE TABLE IF NOT EXISTS `users` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(120) NOT NULL,
  `email`        VARCHAR(180) NOT NULL UNIQUE,
  `country_code` VARCHAR(8)  NOT NULL DEFAULT '+1',
  `phone`        VARCHAR(20) NOT NULL DEFAULT '',
  `password`     VARCHAR(255) NOT NULL,
  `avatar`       VARCHAR(255) DEFAULT NULL,
  `is_verified`  TINYINT(1) DEFAULT 0,
  `is_active`    TINYINT(1) DEFAULT 1,
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  `last_login`   DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'watchlist' => "CREATE TABLE IF NOT EXISTS `watchlist` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT UNSIGNED NOT NULL,
  `symbol`     VARCHAR(20) NOT NULL,
  `name`       VARCHAR(120),
  `asset_type` VARCHAR(20) DEFAULT 'stock',
  `added_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_user_sym` (`user_id`,`symbol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'predictions' => "CREATE TABLE IF NOT EXISTS `predictions` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`      INT UNSIGNED NOT NULL,
  `symbol`       VARCHAR(20) NOT NULL,
  `signal`       VARCHAR(10) NOT NULL,
  `target_price` DECIMAL(14,4),
  `confidence`   TINYINT UNSIGNED,
  `risk`         VARCHAR(10),
  `summary`      TEXT,
  `raw_data`     TEXT,
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'price_cache' => "CREATE TABLE IF NOT EXISTS `price_cache` (
  `symbol`     VARCHAR(20) PRIMARY KEY,
  `data`       LONGTEXT,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'symbols' => "CREATE TABLE IF NOT EXISTS `symbols` (
  `symbol`     VARCHAR(20) PRIMARY KEY,
  `name`       VARCHAR(180) NOT NULL,
  `exchange`   VARCHAR(60),
  `sector`     VARCHAR(80),
  `asset_type` VARCHAR(20) DEFAULT 'stock',
  `is_active`  TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

'news_cache' => "CREATE TABLE IF NOT EXISTS `news_cache` (
  `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `symbol`    VARCHAR(20),
  `headline`  VARCHAR(500),
  `source`    VARCHAR(100),
  `url`       VARCHAR(500),
  `sentiment` VARCHAR(10) DEFAULT 'NEUTRAL',
  `published` DATETIME,
  `cached_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

echo "Creating tables...\n";
foreach ($tables as $name => $sql) {
    if (mysqli_query($conn, $sql)) echo "<span class='ok'>✅ $name</span>\n";
    else echo "<span class='err'>❌ $name: " . mysqli_error($conn) . "</span>\n";
}

// Seed symbols
$symbols = [
  ['AAPL','Apple Inc.','NASDAQ','Technology','stock'],
  ['MSFT','Microsoft Corporation','NASDAQ','Technology','stock'],
  ['GOOGL','Alphabet Inc.','NASDAQ','Technology','stock'],
  ['AMZN','Amazon.com Inc.','NASDAQ','Consumer Discretionary','stock'],
  ['META','Meta Platforms Inc.','NASDAQ','Technology','stock'],
  ['TSLA','Tesla Inc.','NASDAQ','Automotive','stock'],
  ['NVDA','NVIDIA Corporation','NASDAQ','Semiconductors','stock'],
  ['NFLX','Netflix Inc.','NASDAQ','Entertainment','stock'],
  ['AMD','Advanced Micro Devices','NASDAQ','Semiconductors','stock'],
  ['INTC','Intel Corporation','NASDAQ','Semiconductors','stock'],
  ['ORCL','Oracle Corporation','NYSE','Technology','stock'],
  ['CRM','Salesforce Inc.','NYSE','Software','stock'],
  ['ADBE','Adobe Inc.','NASDAQ','Software','stock'],
  ['SNOW','Snowflake Inc.','NYSE','Cloud','stock'],
  ['UBER','Uber Technologies','NYSE','Transportation','stock'],
  ['SHOP','Shopify Inc.','NYSE','E-Commerce','stock'],
  ['BABA','Alibaba Group','NYSE','E-Commerce','stock'],
  ['TSM','Taiwan Semiconductor','NYSE','Semiconductors','stock'],
  ['ASML','ASML Holding N.V.','NASDAQ','Semiconductors','stock'],
  ['JPM','JPMorgan Chase & Co.','NYSE','Financials','stock'],
  ['BAC','Bank of America Corp.','NYSE','Financials','stock'],
  ['GS','Goldman Sachs Group','NYSE','Financials','stock'],
  ['MS','Morgan Stanley','NYSE','Financials','stock'],
  ['V','Visa Inc.','NYSE','Financials','stock'],
  ['MA','Mastercard Inc.','NYSE','Financials','stock'],
  ['JNJ','Johnson & Johnson','NYSE','Healthcare','stock'],
  ['PFE','Pfizer Inc.','NYSE','Healthcare','stock'],
  ['UNH','UnitedHealth Group','NYSE','Healthcare','stock'],
  ['XOM','Exxon Mobil Corp.','NYSE','Energy','stock'],
  ['CVX','Chevron Corporation','NYSE','Energy','stock'],
  ['WMT','Walmart Inc.','NYSE','Consumer Staples','stock'],
  ['COST','Costco Wholesale','NASDAQ','Consumer Staples','stock'],
  ['PG','Procter & Gamble','NYSE','Consumer Staples','stock'],
  ['KO','Coca-Cola Company','NYSE','Consumer Staples','stock'],
  ['DIS','Walt Disney Company','NYSE','Entertainment','stock'],
  ['PYPL','PayPal Holdings','NASDAQ','Fintech','stock'],
  ['PLTR','Palantir Technologies','NYSE','Technology','stock'],
  ['COIN','Coinbase Global','NASDAQ','Crypto Exchange','stock'],
  ['SPY','SPDR S&P 500 ETF','NYSE Arca','Index ETF','etf'],
  ['QQQ','Invesco QQQ NASDAQ ETF','NASDAQ','Index ETF','etf'],
  ['GLD','SPDR Gold Shares','NYSE Arca','Commodity ETF','etf'],
  ['TLT','iShares 20+ Year Treasury','NYSE Arca','Bond ETF','etf'],
  ['ARKK','ARK Innovation ETF','NYSE Arca','Active ETF','etf'],
  ['VTI','Vanguard Total Market','NYSE Arca','Index ETF','etf'],
  ['BTC/USD','Bitcoin','Crypto','Cryptocurrency','crypto'],
  ['ETH/USD','Ethereum','Crypto','Cryptocurrency','crypto'],
  ['SOL/USD','Solana','Crypto','Cryptocurrency','crypto'],
  ['BNB/USD','BNB','Crypto','Cryptocurrency','crypto'],
  ['XRP/USD','XRP','Crypto','Cryptocurrency','crypto'],
  ['DOGE/USD','Dogecoin','Crypto','Cryptocurrency','crypto'],
  ['EUR/USD','Euro / US Dollar','FX','Forex','forex'],
  ['GBP/USD','British Pound / USD','FX','Forex','forex'],
  ['USD/JPY','US Dollar / Yen','FX','Forex','forex'],
  ['USD/INR','US Dollar / Rupee','FX','Forex','forex'],
  ['AUD/USD','Australian Dollar / USD','FX','Forex','forex'],
  ['SPX','S&P 500 Index','NYSE','US Index','index'],
  ['NDX','NASDAQ 100 Index','NASDAQ','US Index','index'],
  ['DJI','Dow Jones Industrial','NYSE','US Index','index'],
];

echo "\nSeeding symbols...\n";
$ok = 0;
foreach ($symbols as $s) {
    $q = "INSERT IGNORE INTO symbols (symbol,name,exchange,sector,asset_type) VALUES ("
       . "'" . mysqli_real_escape_string($conn,$s[0]) . "',"
       . "'" . mysqli_real_escape_string($conn,$s[1]) . "',"
       . "'" . mysqli_real_escape_string($conn,$s[2]) . "',"
       . "'" . mysqli_real_escape_string($conn,$s[3]) . "',"
       . "'" . mysqli_real_escape_string($conn,$s[4]) . "')";
    if (mysqli_query($conn, $q)) $ok++;
}
echo "<span class='ok'>✅ $ok symbols seeded</span>\n";

// Test users table
$t = mysqli_query($conn, "SELECT COUNT(*) as c FROM users");
$row = mysqli_fetch_assoc($t);
echo "\n<span class='ok'>✅ users table has " . $row['c'] . " user(s)</span>\n";

mysqli_close($conn);
echo '</pre>';
echo '<h3 style="color:#0f0">✅ Done! Now update config.php with DB_HOST = \'' . $goodHost . '\'</h3>';
echo '<p style="color:#aaa">⚠ Delete or rename this install.php file after you are done.</p>';
echo '</body></html>';
