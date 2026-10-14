-- ================================================================
--  STOCKRY DATABASE SETUP
--  Run this entire script in phpMyAdmin > SQL tab
--  Database: #_stockry
-- ================================================================

-- Make sure you are using the correct database
USE `#_stockry`;

-- ----------------------------------------------------------------
-- TABLE 1: users
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(120)  NOT NULL,
  `email`        VARCHAR(180)  NOT NULL UNIQUE,
  `country_code` VARCHAR(8)    NOT NULL DEFAULT '+1',
  `phone`        VARCHAR(20)   NOT NULL,
  `password`     VARCHAR(255)  NOT NULL,
  `avatar`       VARCHAR(255)  DEFAULT NULL,
  `is_verified`  TINYINT(1)    DEFAULT 0,
  `is_active`    TINYINT(1)    DEFAULT 1,
  `created_at`   DATETIME      DEFAULT CURRENT_TIMESTAMP,
  `last_login`   DATETIME      DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- TABLE 2: watchlist
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `watchlist` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT UNSIGNED NOT NULL,
  `symbol`     VARCHAR(20)  NOT NULL,
  `name`       VARCHAR(120),
  `asset_type` ENUM('stock','crypto','forex','etf','index') DEFAULT 'stock',
  `added_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_user_sym` (`user_id`, `symbol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- TABLE 3: predictions
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `predictions` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`      INT UNSIGNED NOT NULL,
  `symbol`       VARCHAR(20)  NOT NULL,
  `signal`       ENUM('BUY','SELL','HOLD') NOT NULL,
  `target_price` DECIMAL(14,4),
  `confidence`   TINYINT UNSIGNED,
  `risk`         ENUM('LOW','MEDIUM','HIGH'),
  `summary`      TEXT,
  `raw_data`     JSON,
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- TABLE 4: price_cache  (reduces API calls)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `price_cache` (
  `symbol`     VARCHAR(20) PRIMARY KEY,
  `data`       LONGTEXT,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- TABLE 5: symbols  (verified whitelist — ONLY real symbols)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `symbols` (
  `symbol`     VARCHAR(20) PRIMARY KEY,
  `name`       VARCHAR(180) NOT NULL,
  `exchange`   VARCHAR(60),
  `sector`     VARCHAR(80),
  `asset_type` ENUM('stock','crypto','forex','etf','index') DEFAULT 'stock',
  `is_active`  TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- TABLE 6: news_cache
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `news_cache` (
  `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `symbol`    VARCHAR(20),
  `headline`  VARCHAR(500),
  `source`    VARCHAR(100),
  `url`       VARCHAR(500),
  `sentiment` ENUM('BULLISH','BEARISH','NEUTRAL') DEFAULT 'NEUTRAL',
  `published` DATETIME,
  `cached_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================================
--  SEED: Verified Symbol Whitelist
--  These are the ONLY symbols the app will accept.
--  Any symbol NOT in this table will be rejected.
-- ================================================================

INSERT IGNORE INTO `symbols` (`symbol`, `name`, `exchange`, `sector`, `asset_type`) VALUES

-- ── US Blue Chip Stocks ──────────────────────────────────────────
('AAPL',  'Apple Inc.',                  'NASDAQ', 'Technology',           'stock'),
('MSFT',  'Microsoft Corporation',       'NASDAQ', 'Technology',           'stock'),
('GOOGL', 'Alphabet Inc. Class A',       'NASDAQ', 'Technology',           'stock'),
('GOOG',  'Alphabet Inc. Class C',       'NASDAQ', 'Technology',           'stock'),
('AMZN',  'Amazon.com Inc.',             'NASDAQ', 'Consumer Discretionary','stock'),
('META',  'Meta Platforms Inc.',         'NASDAQ', 'Technology',           'stock'),
('TSLA',  'Tesla Inc.',                  'NASDAQ', 'Automotive',           'stock'),
('NVDA',  'NVIDIA Corporation',          'NASDAQ', 'Semiconductors',       'stock'),
('NFLX',  'Netflix Inc.',               'NASDAQ', 'Entertainment',         'stock'),
('AMD',   'Advanced Micro Devices',      'NASDAQ', 'Semiconductors',       'stock'),
('INTC',  'Intel Corporation',           'NASDAQ', 'Semiconductors',       'stock'),
('ORCL',  'Oracle Corporation',          'NYSE',   'Technology',           'stock'),
('CRM',   'Salesforce Inc.',             'NYSE',   'Software',             'stock'),
('ADBE',  'Adobe Inc.',                  'NASDAQ', 'Software',             'stock'),
('SNOW',  'Snowflake Inc.',              'NYSE',   'Cloud',                'stock'),
('UBER',  'Uber Technologies',           'NYSE',   'Transportation',       'stock'),
('LYFT',  'Lyft Inc.',                   'NASDAQ', 'Transportation',       'stock'),
('SPOT',  'Spotify Technology',          'NYSE',   'Media',                'stock'),
('SHOP',  'Shopify Inc.',                'NYSE',   'E-Commerce',           'stock'),
('BABA',  'Alibaba Group',               'NYSE',   'E-Commerce',           'stock'),
('TSM',   'Taiwan Semiconductor Mfg',    'NYSE',   'Semiconductors',       'stock'),
('ASML',  'ASML Holding N.V.',           'NASDAQ', 'Semiconductors',       'stock'),
('JPM',   'JPMorgan Chase & Co.',        'NYSE',   'Financials',           'stock'),
('BAC',   'Bank of America Corp.',       'NYSE',   'Financials',           'stock'),
('GS',    'Goldman Sachs Group',         'NYSE',   'Financials',           'stock'),
('MS',    'Morgan Stanley',              'NYSE',   'Financials',           'stock'),
('WFC',   'Wells Fargo & Co.',           'NYSE',   'Financials',           'stock'),
('V',     'Visa Inc.',                   'NYSE',   'Financials',           'stock'),
('MA',    'Mastercard Inc.',             'NYSE',   'Financials',           'stock'),
('AXP',   'American Express',            'NYSE',   'Financials',           'stock'),
('JNJ',   'Johnson & Johnson',           'NYSE',   'Healthcare',           'stock'),
('PFE',   'Pfizer Inc.',                 'NYSE',   'Healthcare',           'stock'),
('UNH',   'UnitedHealth Group',          'NYSE',   'Healthcare',           'stock'),
('ABBV',  'AbbVie Inc.',                 'NYSE',   'Healthcare',           'stock'),
('MRK',   'Merck & Co.',                 'NYSE',   'Healthcare',           'stock'),
('LLY',   'Eli Lilly and Company',       'NYSE',   'Healthcare',           'stock'),
('XOM',   'Exxon Mobil Corp.',           'NYSE',   'Energy',               'stock'),
('CVX',   'Chevron Corporation',         'NYSE',   'Energy',               'stock'),
('WMT',   'Walmart Inc.',                'NYSE',   'Consumer Staples',     'stock'),
('COST',  'Costco Wholesale',            'NASDAQ', 'Consumer Staples',     'stock'),
('PG',    'Procter & Gamble',            'NYSE',   'Consumer Staples',     'stock'),
('KO',    'Coca-Cola Company',           'NYSE',   'Consumer Staples',     'stock'),
('PEP',   'PepsiCo Inc.',                'NASDAQ', 'Consumer Staples',     'stock'),
('DIS',   'Walt Disney Company',         'NYSE',   'Entertainment',        'stock'),
('PYPL',  'PayPal Holdings',             'NASDAQ', 'Fintech',              'stock'),
('SQ',    'Block Inc.',                  'NYSE',   'Fintech',              'stock'),
('PLTR',  'Palantir Technologies',       'NYSE',   'Technology',           'stock'),
('RIVN',  'Rivian Automotive',           'NASDAQ', 'Automotive',           'stock'),
('SOFI',  'SoFi Technologies',           'NASDAQ', 'Fintech',              'stock'),
('COIN',  'Coinbase Global',             'NASDAQ', 'Crypto Exchange',      'stock'),
('MSTR',  'MicroStrategy Inc.',          'NASDAQ', 'Software',             'stock'),
('SMCI',  'Super Micro Computer',        'NASDAQ', 'Hardware',             'stock'),
('HOOD',  'Robinhood Markets',           'NASDAQ', 'Fintech',              'stock'),
('RBLX',  'Roblox Corporation',          'NYSE',   'Gaming',               'stock'),
('SNAP',  'Snap Inc.',                   'NYSE',   'Social Media',         'stock'),
('TWTR',  'X Corp (formerly Twitter)',   'NYSE',   'Social Media',         'stock'),
('ABNB',  'Airbnb Inc.',                 'NASDAQ', 'Travel',               'stock'),
('DASH',  'DoorDash Inc.',               'NYSE',   'Food Delivery',        'stock'),
('ZM',    'Zoom Video Communications',   'NASDAQ', 'Software',             'stock'),
('DKNG',  'DraftKings Inc.',             'NASDAQ', 'Gaming',               'stock'),
('ROKU',  'Roku Inc.',                   'NASDAQ', 'Streaming',            'stock'),
('PINS',  'Pinterest Inc.',              'NYSE',   'Social Media',         'stock'),
('U',     'Unity Software',              'NYSE',   'Software',             'stock'),
('NET',   'Cloudflare Inc.',             'NYSE',   'Cloud',                'stock'),
('ZS',    'Zscaler Inc.',                'NASDAQ', 'Cybersecurity',        'stock'),
('CRWD',  'CrowdStrike Holdings',        'NASDAQ', 'Cybersecurity',        'stock'),
('OKTA',  'Okta Inc.',                   'NASDAQ', 'Cybersecurity',        'stock'),
('DDOG',  'Datadog Inc.',                'NASDAQ', 'Cloud',                'stock'),
('MDB',   'MongoDB Inc.',                'NASDAQ', 'Database',             'stock'),
('TTD',   'The Trade Desk',              'NASDAQ', 'AdTech',               'stock'),
('PANW',  'Palo Alto Networks',          'NASDAQ', 'Cybersecurity',        'stock'),
('FTNT',  'Fortinet Inc.',               'NASDAQ', 'Cybersecurity',        'stock'),
('NOW',   'ServiceNow Inc.',             'NYSE',   'Cloud',                'stock'),
('WDAY',  'Workday Inc.',                'NASDAQ', 'Cloud',                'stock'),
('HUBS',  'HubSpot Inc.',                'NYSE',   'Software',             'stock'),
('BILL',  'Bill Holdings Inc.',          'NYSE',   'Fintech',              'stock'),
('NKE',   'Nike Inc.',                   'NYSE',   'Consumer',             'stock'),
('SBUX',  'Starbucks Corp.',             'NASDAQ', 'Consumer',             'stock'),
('MCD',   'McDonald''s Corp.',           'NYSE',   'Consumer',             'stock'),
('BA',    'Boeing Company',              'NYSE',   'Aerospace',            'stock'),
('CAT',   'Caterpillar Inc.',            'NYSE',   'Industrials',          'stock'),
('GE',    'GE Vernova',                  'NYSE',   'Industrials',          'stock'),
('HON',   'Honeywell International',     'NASDAQ', 'Industrials',          'stock'),
('MMM',   '3M Company',                  'NYSE',   'Industrials',          'stock'),
('DE',    'Deere & Company',             'NYSE',   'Industrials',          'stock'),
('LMT',   'Lockheed Martin',             'NYSE',   'Defense',              'stock'),
('RTX',   'RTX Corporation',             'NYSE',   'Defense',              'stock'),
('NOC',   'Northrop Grumman',            'NYSE',   'Defense',              'stock'),
('AMGN',  'Amgen Inc.',                  'NASDAQ', 'Biotechnology',        'stock'),
('GILD',  'Gilead Sciences',             'NASDAQ', 'Biotechnology',        'stock'),
('BIIB',  'Biogen Inc.',                 'NASDAQ', 'Biotechnology',        'stock'),
('REGN',  'Regeneron Pharmaceuticals',   'NASDAQ', 'Biotechnology',        'stock'),
('MRNA',  'Moderna Inc.',                'NASDAQ', 'Biotechnology',        'stock'),
('CVS',   'CVS Health Corp.',            'NYSE',   'Healthcare',           'stock'),
('CI',    'Cigna Group',                 'NYSE',   'Healthcare',           'stock'),
('HCA',   'HCA Healthcare',              'NYSE',   'Healthcare',           'stock'),
('AMT',   'American Tower Corp.',        'NYSE',   'Real Estate',          'stock'),
('PLD',   'Prologis Inc.',               'NYSE',   'Real Estate',          'stock'),
('O',     'Realty Income Corp.',         'NYSE',   'Real Estate',          'stock'),
('NEE',   'NextEra Energy',              'NYSE',   'Utilities',            'stock'),
('DUK',   'Duke Energy',                 'NYSE',   'Utilities',            'stock'),
('SO',    'Southern Company',            'NYSE',   'Utilities',            'stock'),

-- ── ETFs ─────────────────────────────────────────────────────────
('SPY',  'SPDR S&P 500 ETF Trust',          'NYSE Arca', 'Index ETF',     'etf'),
('QQQ',  'Invesco QQQ NASDAQ-100 ETF',      'NASDAQ',    'Index ETF',     'etf'),
('DIA',  'SPDR Dow Jones Industrial ETF',   'NYSE Arca', 'Index ETF',     'etf'),
('IWM',  'iShares Russell 2000 ETF',        'NYSE Arca', 'Index ETF',     'etf'),
('GLD',  'SPDR Gold Shares',                'NYSE Arca', 'Commodity ETF', 'etf'),
('SLV',  'iShares Silver Trust',            'NYSE Arca', 'Commodity ETF', 'etf'),
('VTI',  'Vanguard Total Stock Market ETF', 'NYSE Arca', 'Index ETF',     'etf'),
('VOO',  'Vanguard S&P 500 ETF',            'NYSE Arca', 'Index ETF',     'etf'),
('TLT',  'iShares 20+ Year Treasury ETF',   'NYSE Arca', 'Bond ETF',      'etf'),
('ARKK', 'ARK Innovation ETF',              'NYSE Arca', 'Active ETF',    'etf'),
('VNQ',  'Vanguard Real Estate ETF',        'NYSE Arca', 'Sector ETF',    'etf'),
('XLF',  'Financial Select Sector SPDR',    'NYSE Arca', 'Sector ETF',    'etf'),
('XLK',  'Technology Select Sector SPDR',   'NYSE Arca', 'Sector ETF',    'etf'),
('XLE',  'Energy Select Sector SPDR',       'NYSE Arca', 'Sector ETF',    'etf'),
('XLV',  'Health Care Select Sector SPDR',  'NYSE Arca', 'Sector ETF',    'etf'),
('USO',  'United States Oil Fund',          'NYSE Arca', 'Commodity ETF', 'etf'),
('HYG',  'iShares iBoxx High Yield Corp',   'NYSE Arca', 'Bond ETF',      'etf'),
('EEM',  'iShares MSCI Emerging Markets',   'NYSE Arca', 'Index ETF',     'etf'),

-- ── Crypto ───────────────────────────────────────────────────────
('BTC/USD',   'Bitcoin',          'Crypto', 'Cryptocurrency', 'crypto'),
('ETH/USD',   'Ethereum',         'Crypto', 'Cryptocurrency', 'crypto'),
('SOL/USD',   'Solana',           'Crypto', 'Cryptocurrency', 'crypto'),
('BNB/USD',   'BNB (Binance)',     'Crypto', 'Cryptocurrency', 'crypto'),
('XRP/USD',   'XRP (Ripple)',      'Crypto', 'Cryptocurrency', 'crypto'),
('ADA/USD',   'Cardano',          'Crypto', 'Cryptocurrency', 'crypto'),
('DOGE/USD',  'Dogecoin',         'Crypto', 'Cryptocurrency', 'crypto'),
('AVAX/USD',  'Avalanche',        'Crypto', 'Cryptocurrency', 'crypto'),
('MATIC/USD', 'Polygon',          'Crypto', 'Cryptocurrency', 'crypto'),
('DOT/USD',   'Polkadot',         'Crypto', 'Cryptocurrency', 'crypto'),
('LINK/USD',  'Chainlink',        'Crypto', 'Cryptocurrency', 'crypto'),
('UNI/USD',   'Uniswap',          'Crypto', 'Cryptocurrency', 'crypto'),
('LTC/USD',   'Litecoin',         'Crypto', 'Cryptocurrency', 'crypto'),
('ATOM/USD',  'Cosmos',           'Crypto', 'Cryptocurrency', 'crypto'),
('NEAR/USD',  'NEAR Protocol',    'Crypto', 'Cryptocurrency', 'crypto'),
('FTM/USD',   'Fantom',           'Crypto', 'Cryptocurrency', 'crypto'),
('ALGO/USD',  'Algorand',         'Crypto', 'Cryptocurrency', 'crypto'),
('VET/USD',   'VeChain',          'Crypto', 'Cryptocurrency', 'crypto'),

-- ── Forex ────────────────────────────────────────────────────────
('EUR/USD', 'Euro / US Dollar',           'FX', 'Forex', 'forex'),
('GBP/USD', 'British Pound / US Dollar',  'FX', 'Forex', 'forex'),
('USD/JPY', 'US Dollar / Japanese Yen',   'FX', 'Forex', 'forex'),
('USD/INR', 'US Dollar / Indian Rupee',   'FX', 'Forex', 'forex'),
('USD/CAD', 'US Dollar / Canadian Dollar','FX', 'Forex', 'forex'),
('AUD/USD', 'Australian Dollar / USD',    'FX', 'Forex', 'forex'),
('USD/CHF', 'US Dollar / Swiss Franc',    'FX', 'Forex', 'forex'),
('USD/CNY', 'US Dollar / Chinese Yuan',   'FX', 'Forex', 'forex'),
('USD/SGD', 'US Dollar / Singapore Dollar','FX','Forex', 'forex'),
('USD/MXN', 'US Dollar / Mexican Peso',   'FX', 'Forex', 'forex'),
('USD/BRL', 'US Dollar / Brazilian Real', 'FX', 'Forex', 'forex'),
('USD/ZAR', 'US Dollar / South African Rand','FX','Forex','forex'),

-- ── Indices ──────────────────────────────────────────────────────
('SPX',  'S&P 500 Index',         'NYSE',   'US Index',     'index'),
('NDX',  'NASDAQ 100 Index',      'NASDAQ', 'US Index',     'index'),
('DJI',  'Dow Jones Industrial',  'NYSE',   'US Index',     'index'),
('RUT',  'Russell 2000 Index',    'NYSE',   'US Index',     'index'),
('VIX',  'CBOE Volatility Index', 'CBOE',   'Volatility',   'index'),
('FTSE', 'FTSE 100',              'LSE',    'UK Index',     'index'),
('DAX',  'DAX Index',             'XETRA',  'German Index', 'index'),
('N225', 'Nikkei 225',            'TSE',    'Japan Index',  'index');

-- ================================================================
--  VERIFY: Check all tables were created
-- ================================================================
SHOW TABLES;

-- ================================================================
--  VERIFY: Check symbol count
-- ================================================================
SELECT asset_type, COUNT(*) as count FROM symbols GROUP BY asset_type;
