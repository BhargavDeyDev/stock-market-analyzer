<?php
require_once __DIR__ . '/includes/config.php';
$page = clean($_GET['page'] ?? 'markets');
$validPages = ['markets','watchlist','predictions','news','economy','signin','signup','profile','settings'];
if (!in_array($page, $validPages)) $page = 'markets';

// Country codes for signup form
$countryCodes = [
  ['+1','US/CA'],  ['+44','UK'],   ['+91','IN'],   ['+61','AU'],
  ['+49','DE'],    ['+33','FR'],   ['+81','JP'],    ['+86','CN'],
  ['+55','BR'],    ['+7','RU'],    ['+82','KR'],    ['+65','SG'],
  ['+971','UAE'],  ['+966','SA'],  ['+20','EG'],    ['+234','NG'],
  ['+27','ZA'],    ['+52','MX'],   ['+39','IT'],    ['+34','ES'],
  ['+31','NL'],    ['+46','SE'],   ['+47','NO'],    ['+45','DK'],
  ['+358','FI'],   ['+41','CH'],   ['+43','AT'],    ['+48','PL'],
  ['+90','TR'],    ['+62','ID'],   ['+60','MY'],    ['+66','TH'],
  ['+84','VN'],    ['+63','PH'],   ['+880','BD'],   ['+92','PK'],
  ['+94','LK'],    ['+977','NP'],  ['+64','NZ'],    ['+353','IE'],
  ['+32','BE'],    ['+351','PT'],  ['+30','GR'],    ['+36','HU'],
  ['+420','CZ'],   ['+380','UA'],  ['+40','RO'],    ['+972','IL'],
  ['+98','IR'],    ['+964','IQ'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stockry — AI Market Intelligence</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='7' fill='%2300d478'/><polyline points='5,22 10,12 15,17 21,9 27,14' stroke='%23051a0e' stroke-width='3' fill='none' stroke-linecap='round' stroke-linejoin='round'/></svg>"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
<style>
<?php include __DIR__ . '/assets/css/main.css'; ?>
</style>
</head>
<body data-page="<?= $page ?>">

<!-- NAV -->
<nav id="mainNav">
  <a href="/" class="logo">
    <div class="logo-mark">
      <svg viewBox="0 0 24 24" fill="none" stroke="#051a0e" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="3,17 7,9 11,13 15,7 19,11 21,9"/>
      </svg>
    </div>
    <div class="logo-text"><span>Stock</span><em>ry</em></div>
  </a>
  <div class="nav-links">
    <a href="/?page=markets"     class="<?= $page==='markets'?'active':'' ?>">Markets</a>
    <a href="/?page=watchlist"   class="<?= $page==='watchlist'?'active':'' ?>">Watchlist</a>
    <a href="/?page=predictions" class="<?= $page==='predictions'?'active':'' ?>">Predictions</a>
    <a href="/?page=news"        class="<?= $page==='news'?'active':'' ?>">News</a>
    <a href="/?page=economy"     class="<?= $page==='economy'?'active':'' ?>">Economy</a>
  </div>
  <div class="nav-end">
    <div class="mkt-pill"><div class="pulse" id="mktDot"></div><span id="mktTxt">CHECKING</span></div>
    <?php if (isLoggedIn()): ?>
      <div class="user-menu">
        <div class="user-btn" onclick="toggleUserMenu()">
          <div class="user-avatar"><?= strtoupper(substr(currentUser()['name'],0,1)) ?></div>
          <span class="user-name"><?= clean(currentUser()['name']) ?></span>
          <span>▾</span>
        </div>
        <div class="user-dropdown" id="userDropdown">
          <a href="/?page=profile">My Profile</a>
          <a href="/?page=predictions">My Predictions</a>
          <a href="/?page=watchlist">My Watchlist</a>
          <a href="/?page=settings">Settings</a>
          <div class="dropdown-divider"></div>
          <a href="#" onclick="doSignOut()">Sign Out</a>
        </div>
      </div>
    <?php else: ?>
      <button class="nbtn nbtn-o" onclick="showModal('signinModal')">Sign In</button>
      <button class="nbtn nbtn-p" onclick="showModal('signupModal')">Get Started</button>
    <?php endif; ?>
  </div>
</nav>

<!-- MODALS -->
<?php if (!isLoggedIn()): ?>

<!-- SIGN IN MODAL -->
<div class="modal-overlay" id="signinModal" onclick="hideModal('signinModal')">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-header">
      <div class="modal-logo">📈</div>
      <h2>Welcome back</h2>
      <p>Sign in with your email or phone number</p>
      <button class="modal-close" onclick="hideModal('signinModal')">×</button>
    </div>
    <div class="modal-body">
      <div class="form-err" id="signinErr"></div>
      <div class="form-group">
        <label>Email or Phone Number</label>
        <input type="text" id="siLogin" placeholder="you@example.com or +91 9876543210" autocomplete="username"/>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="pass-wrap">
          <input type="password" id="siPass" placeholder="Your password" autocomplete="current-password"/>
          <button class="pass-eye" onclick="togglePass('siPass')">👁</button>
        </div>
      </div>
      <button class="form-btn" onclick="doSignIn()">Sign In</button>
      <div class="form-alt">
        Don't have an account? <a href="#" onclick="switchModal('signinModal','signupModal')">Create one free</a>
      </div>
    </div>
  </div>
</div>

<!-- SIGN UP MODAL -->
<div class="modal-overlay" id="signupModal" onclick="hideModal('signupModal')">
  <div class="modal modal-wide" onclick="event.stopPropagation()">
    <div class="modal-header">
      <div class="modal-logo">🚀</div>
      <h2>Create your account</h2>
      <p>Start predicting markets with AI-powered insights</p>
      <button class="modal-close" onclick="hideModal('signupModal')">×</button>
    </div>
    <div class="modal-body">
      <div class="form-err" id="signupErr"></div>
      <div class="form-row">
        <div class="form-group">
          <label>Full Name <span class="req">*</span></label>
          <input type="text" id="suName" placeholder="John Smith" autocomplete="name"/>
        </div>
        <div class="form-group">
          <label>Email Address <span class="req">*</span></label>
          <input type="email" id="suEmail" placeholder="you@example.com" autocomplete="email"/>
        </div>
      </div>
      <div class="form-group">
        <label>Contact Number <span class="req">*</span></label>
        <div class="phone-wrap">
          <select id="suCC" class="cc-select">
            <?php foreach($countryCodes as [$code,$country]): ?>
            <option value="<?= $code ?>" <?= $code==='+91'?'selected':'' ?>><?= $code ?> (<?= $country ?>)</option>
            <?php endforeach; ?>
          </select>
          <input type="tel" id="suPhone" placeholder="9876543210" autocomplete="tel-national"/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Password <span class="req">*</span></label>
          <div class="pass-wrap">
            <input type="password" id="suPass" placeholder="Min 8 characters" autocomplete="new-password"/>
            <button class="pass-eye" onclick="togglePass('suPass')">👁</button>
          </div>
        </div>
        <div class="form-group">
          <label>Confirm Password <span class="req">*</span></label>
          <div class="pass-wrap">
            <input type="password" id="suPass2" placeholder="Repeat password" autocomplete="new-password"/>
            <button class="pass-eye" onclick="togglePass('suPass2')">👁</button>
          </div>
        </div>
      </div>
      <div class="form-terms">
        <input type="checkbox" id="suTerms"/>
        <label for="suTerms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
      </div>
      <button class="form-btn" onclick="doSignUp()">Create Account</button>
      <div class="form-alt">
        Already have an account? <a href="#" onclick="switchModal('signupModal','signinModal')">Sign in</a>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>

<!-- MAIN LAYOUT -->
<div class="layout" id="appLayout">

  <!-- LEFT SIDEBAR -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-inner">
      <!-- Search -->
      <div class="sb-search">
        <input type="text" id="sbSearchIn" placeholder="Search symbol…" autocomplete="off"/>
        <div class="sb-results" id="sbResults"></div>
      </div>
      <!-- Sections built by JS -->
      <div id="sbUS">    <div class="sb-head">US Stocks</div>    <div class="sb-rows" id="rowsUS"></div></div>
      <div id="sbTech">  <div class="sb-head">Tech & Growth</div><div class="sb-rows" id="rowsTech"></div></div>
      <div id="sbETF">   <div class="sb-head">ETFs</div>         <div class="sb-rows" id="rowsETF"></div></div>
      <div id="sbCrypto"><div class="sb-head">Crypto</div>       <div class="sb-rows" id="rowsCrypto"></div></div>
      <div id="sbForex"> <div class="sb-head">Forex</div>        <div class="sb-rows" id="rowsForex"></div></div>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="content" id="mainContent">
    <?php
    $pageFile = __DIR__ . '/pages/' . $page . '.php';
    if (file_exists($pageFile)) include $pageFile;
    else include __DIR__ . '/pages/markets.php';
    ?>
  </div>

  <!-- RIGHT PANEL -->
  <div class="rpanel" id="rpanel">
    <div class="rp-inner">
      <div class="rp-head">Global Indices</div>
      <div id="rpIndices"><div class="rp-skeleton"></div></div>
      <div class="rp-head" style="margin-top:1rem">Sector Heatmap</div>
      <div class="heatmap" id="rpSectors"></div>
      <div class="rp-head">Top Movers</div>
      <div id="rpMovers"><div class="rp-skeleton"></div></div>
      <div class="rp-head">Most Active</div>
      <div id="rpActive"><div class="rp-skeleton"></div></div>
    </div>
  </div>

</div>

<!-- TICKER TAPE -->
<div class="tape-bar">
  <div class="tape-scroll" id="tapeScroll">
    <span class="tape-loading">Loading live market data…</span>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script>
// ════════════════════════════════════════════════════════════════
//  STOCKRY — Main Application JS
// ════════════════════════════════════════════════════════════════
const CSRF = '#';
const IS_LOGGED_IN = <?= isLoggedIn() ? 'true' : 'false' ?>;
const SITE_URL = '<?= SITE_URL ?>';
const CURRENT_PAGE = '<?= $page ?>';

// ── Verified asset universe (mirrors DB seeds) ──
const ASSETS = {
  US: [
    {s:'AAPL',n:'Apple Inc.',sector:'Technology'},
    {s:'MSFT',n:'Microsoft',sector:'Technology'},
    {s:'GOOGL',n:'Alphabet',sector:'Technology'},
    {s:'AMZN',n:'Amazon',sector:'Consumer'},
    {s:'TSLA',n:'Tesla',sector:'Automotive'},
    {s:'NVDA',n:'NVIDIA',sector:'Semiconductors'},
    {s:'META',n:'Meta Platforms',sector:'Technology'},
    {s:'JPM',n:'JPMorgan Chase',sector:'Financials'},
    {s:'JNJ',n:'Johnson & Johnson',sector:'Healthcare'},
    {s:'V',n:'Visa Inc.',sector:'Financials'},
    {s:'WMT',n:'Walmart',sector:'Consumer'},
    {s:'XOM',n:'Exxon Mobil',sector:'Energy'},
  ],
  Tech: [
    {s:'NFLX',n:'Netflix',sector:'Media'},
    {s:'AMD',n:'Advanced Micro Devices',sector:'Semiconductors'},
    {s:'INTC',n:'Intel',sector:'Semiconductors'},
    {s:'CRM',n:'Salesforce',sector:'Cloud'},
    {s:'ADBE',n:'Adobe',sector:'Software'},
    {s:'ORCL',n:'Oracle',sector:'Cloud'},
    {s:'SNOW',n:'Snowflake',sector:'Cloud'},
    {s:'SHOP',n:'Shopify',sector:'E-Commerce'},
    {s:'BABA',n:'Alibaba',sector:'E-Commerce'},
    {s:'TSM',n:'Taiwan Semiconductor',sector:'Semiconductors'},
    {s:'PLTR',n:'Palantir',sector:'Technology'},
    {s:'COIN',n:'Coinbase',sector:'Fintech'},
  ],
  ETF: [
    {s:'SPY',n:'S&P 500 ETF'},
    {s:'QQQ',n:'NASDAQ 100 ETF'},
    {s:'GLD',n:'Gold ETF'},
    {s:'TLT',n:'20+ Yr Treasury ETF'},
    {s:'ARKK',n:'ARK Innovation ETF'},
    {s:'VTI',n:'Vanguard Total Market'},
  ],
  Crypto: [
    {s:'BTC/USD',n:'Bitcoin',c:'#f7931a',ic:'₿'},
    {s:'ETH/USD',n:'Ethereum',c:'#627eea',ic:'Ξ'},
    {s:'SOL/USD',n:'Solana',c:'#9945ff',ic:'◎'},
    {s:'BNB/USD',n:'BNB',c:'#f0b90b',ic:'B'},
    {s:'XRP/USD',n:'XRP',c:'#00aae4',ic:'✕'},
    {s:'DOGE/USD',n:'Dogecoin',c:'#c3a634',ic:'Ð'},
  ],
  Forex: [
    {s:'EUR/USD',n:'Euro / USD'},
    {s:'GBP/USD',n:'Pound / USD'},
    {s:'USD/JPY',n:'USD / Yen'},
    {s:'USD/INR',n:'USD / Rupee'},
    {s:'AUD/USD',n:'AUD / USD'},
  ],
};
// NEW JS STARTS HERE

// ════════════════════════════════════════════════════════════════
//  STOCKRY — Client-side market data (bypasses server for quotes)
//  Yahoo Finance is fetched DIRECTLY from the browser
// ════════════════════════════════════════════════════════════════
let curSym = 'AAPL', curTF = '1D', chartInst = null, liveQuote = {};
const sleep = ms => new Promise(r => setTimeout(r, ms));

const SYM_INFO = {
  'AAPL':{n:'Apple Inc.',e:'NASDAQ',s:'Technology'},'MSFT':{n:'Microsoft',e:'NASDAQ',s:'Technology'},
  'GOOGL':{n:'Alphabet',e:'NASDAQ',s:'Technology'},'AMZN':{n:'Amazon',e:'NASDAQ',s:'Consumer'},
  'META':{n:'Meta Platforms',e:'NASDAQ',s:'Technology'},'TSLA':{n:'Tesla',e:'NASDAQ',s:'Automotive'},
  'NVDA':{n:'NVIDIA',e:'NASDAQ',s:'Semiconductors'},'NFLX':{n:'Netflix',e:'NASDAQ',s:'Entertainment'},
  'AMD':{n:'AMD',e:'NASDAQ',s:'Semiconductors'},'INTC':{n:'Intel',e:'NASDAQ',s:'Semiconductors'},
  'ORCL':{n:'Oracle',e:'NYSE',s:'Technology'},'CRM':{n:'Salesforce',e:'NYSE',s:'Software'},
  'ADBE':{n:'Adobe',e:'NASDAQ',s:'Software'},'SNOW':{n:'Snowflake',e:'NYSE',s:'Cloud'},
  'UBER':{n:'Uber',e:'NYSE',s:'Transport'},'SHOP':{n:'Shopify',e:'NYSE',s:'E-Commerce'},
  'BABA':{n:'Alibaba',e:'NYSE',s:'E-Commerce'},'TSM':{n:'TSMC',e:'NYSE',s:'Semiconductors'},
  'ASML':{n:'ASML',e:'NASDAQ',s:'Semiconductors'},'JPM':{n:'JPMorgan',e:'NYSE',s:'Financials'},
  'BAC':{n:'Bank of America',e:'NYSE',s:'Financials'},'GS':{n:'Goldman Sachs',e:'NYSE',s:'Financials'},
  'MS':{n:'Morgan Stanley',e:'NYSE',s:'Financials'},'V':{n:'Visa',e:'NYSE',s:'Financials'},
  'MA':{n:'Mastercard',e:'NYSE',s:'Financials'},'JNJ':{n:'J&J',e:'NYSE',s:'Healthcare'},
  'PFE':{n:'Pfizer',e:'NYSE',s:'Healthcare'},'UNH':{n:'UnitedHealth',e:'NYSE',s:'Healthcare'},
  'XOM':{n:'Exxon Mobil',e:'NYSE',s:'Energy'},'CVX':{n:'Chevron',e:'NYSE',s:'Energy'},
  'WMT':{n:'Walmart',e:'NYSE',s:'Consumer'},'COST':{n:'Costco',e:'NASDAQ',s:'Consumer'},
  'PG':{n:'P&G',e:'NYSE',s:'Consumer'},'KO':{n:'Coca-Cola',e:'NYSE',s:'Consumer'},
  'DIS':{n:'Disney',e:'NYSE',s:'Entertainment'},'PYPL':{n:'PayPal',e:'NASDAQ',s:'Fintech'},
  'PLTR':{n:'Palantir',e:'NYSE',s:'Technology'},'COIN':{n:'Coinbase',e:'NASDAQ',s:'Crypto'},
  'MSTR':{n:'MicroStrategy',e:'NASDAQ',s:'Technology'},
  'SPY':{n:'S&P 500 ETF',e:'NYSE',s:'ETF'},'QQQ':{n:'QQQ ETF',e:'NASDAQ',s:'ETF'},
  'GLD':{n:'Gold ETF',e:'NYSE',s:'ETF'},'TLT':{n:'Treasury ETF',e:'NYSE',s:'ETF'},
  'ARKK':{n:'ARK Innovation',e:'NYSE',s:'ETF'},'VTI':{n:'Vanguard Total',e:'NYSE',s:'ETF'},
  'IWM':{n:'Russell 2000',e:'NYSE',s:'ETF'},
  'BTC/USD':{n:'Bitcoin',e:'Crypto',s:'Crypto'},'ETH/USD':{n:'Ethereum',e:'Crypto',s:'Crypto'},
  'SOL/USD':{n:'Solana',e:'Crypto',s:'Crypto'},'BNB/USD':{n:'BNB',e:'Crypto',s:'Crypto'},
  'XRP/USD':{n:'XRP',e:'Crypto',s:'Crypto'},'DOGE/USD':{n:'Dogecoin',e:'Crypto',s:'Crypto'},
  'EUR/USD':{n:'EUR/USD',e:'FX',s:'Forex'},'GBP/USD':{n:'GBP/USD',e:'FX',s:'Forex'},
  'USD/JPY':{n:'USD/JPY',e:'FX',s:'Forex'},'USD/INR':{n:'USD/INR',e:'FX',s:'Forex'},
  'AUD/USD':{n:'AUD/USD',e:'FX',s:'Forex'},'USD/CAD':{n:'USD/CAD',e:'FX',s:'Forex'},
};

function toYSym(s){ return s.replace('/','-'); }

// ── Direct Yahoo Finance from browser ──
async function yahooQuote(sym) {
  const ySym = toYSym(sym);
  const fields = 'regularMarketPrice,regularMarketOpen,regularMarketDayHigh,regularMarketDayLow,regularMarketVolume,regularMarketPreviousClose,marketCap,trailingPE,shortName,regularMarketChangePercent';
  const urls = [
    `https://query1.finance.yahoo.com/v7/finance/quote?symbols=${encodeURIComponent(ySym)}&fields=${fields}`,
    `https://query2.finance.yahoo.com/v7/finance/quote?symbols=${encodeURIComponent(ySym)}&fields=${fields}`,
    `https://query1.finance.yahoo.com/v8/finance/chart/${encodeURIComponent(ySym)}?interval=1d&range=1d`,
  ];
  for (const url of urls) {
    try {
      const r = await fetch(url, {signal: AbortSignal.timeout(8000)});
      if (!r.ok) continue;
      const d = await r.json();
      if (d?.quoteResponse?.result?.[0]?.regularMarketPrice > 0) {
        const q = d.quoteResponse.result[0];
        const p = q.regularMarketPrice, o = q.regularMarketOpen ?? p;
        return { symbol:sym, close:String(p), open:String(o),
          high:String(q.regularMarketDayHigh??p), low:String(q.regularMarketDayLow??p),
          volume:String(q.regularMarketVolume??0), previous_close:String(q.regularMarketPreviousClose??o),
          market_cap:String(q.marketCap??0), pe_ratio:String(q.trailingPE??''),
          name:q.shortName??SYM_INFO[sym]?.n??sym,
          change_percent:String(q.regularMarketChangePercent??0), _source:'yahoo_direct' };
      }
      if (d?.chart?.result?.[0]?.meta?.regularMarketPrice > 0) {
        const m = d.chart.result[0].meta;
        const p = m.regularMarketPrice, o = m.chartPreviousClose ?? p;
        return { symbol:sym, close:String(p), open:String(o),
          high:String(m.regularMarketDayHigh??p), low:String(m.regularMarketDayLow??p),
          volume:String(m.regularMarketVolume??0), previous_close:String(o),
          market_cap:'0', pe_ratio:'', name:m.shortName??SYM_INFO[sym]?.n??sym,
          change_percent:o>0?String(((p-o)/o*100).toFixed(4)):'0', _source:'yahoo_direct_v8' };
      }
    } catch(e) { continue; }
  }
  return null;
}

async function yahooTimeSeries(sym, yi, yr) {
  const ySym = toYSym(sym);
  const urls = [
    `https://query1.finance.yahoo.com/v8/finance/chart/${encodeURIComponent(ySym)}?interval=${yi}&range=${yr}`,
    `https://query2.finance.yahoo.com/v8/finance/chart/${encodeURIComponent(ySym)}?interval=${yi}&range=${yr}`,
  ];
  for (const url of urls) {
    try {
      const r = await fetch(url, {signal: AbortSignal.timeout(10000)});
      if (!r.ok) continue;
      const d = await r.json();
      const res = d?.chart?.result?.[0];
      if (!res?.timestamp?.length) continue;
      const ts=res.timestamp, q=res.indicators.quote[0]??{};
      const cl=q.close??[],op=q.open??[],hi=q.high??[],lo=q.low??[],vo=q.volume??[];
      const vals=[];
      for(let i=0;i<ts.length;i++){const c=cl[i];if(!c||c===0)continue;
        const dt=new Date(ts[i]*1000);
        vals.push({datetime:dt.toISOString().slice(0,16).replace('T',' '),
          open:(op[i]??c).toFixed(6),high:(hi[i]??c).toFixed(6),
          low:(lo[i]??c).toFixed(6),close:c.toFixed(6),volume:String(vo[i]??0)});}
      if(vals.length>=2) return vals.reverse();
    } catch(e) { continue; }
  }
  return null;
}

// ── apiFetch: proxy to server, returns null on failure/block ──
async function apiFetch(params) {
  const qs = new URLSearchParams(params).toString();
  try {
    const r = await fetch('/api.php?' + qs, {signal: AbortSignal.timeout(10000)});
    const d = await r.json();
    if (d?.error === 'server_blocked' || d?._direct_fetch) return null;
    return d;
  } catch(e) { return null; }
}

async function apiPost(data) {
  data.csrf = CSRF;
  try {
    const r = await fetch('/api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams(data), signal:AbortSignal.timeout(15000)});
    return r.json();
  } catch(e) { return {error: e.message}; }
}

// ── Smart quote: server first, direct Yahoo fallback ──
async function smartQuote(sym) {
  const srv = await apiFetch({action:'quote', sym});
  if (srv && !srv.error && parseFloat(srv.close) > 0) return srv;
  return await yahooQuote(sym);
}

async function smartTimeSeries(sym, cfg) {
  const srv = await apiFetch({action:'timeseries', sym, interval:cfg.interval, size:cfg.size});
  if (srv?.values?.length >= 2) return srv.values;
  const direct = await yahooTimeSeries(sym, cfg.yi, cfg.yr);
  return direct ? direct.slice(0, cfg.size) : null;
}

// ════════════════════════════════════════════════════════════════
//  SIDEBAR
// ════════════════════════════════════════════════════════════════
function buildSidebar() {
  renderSbSection(ASSETS.US,     'rowsUS',     false);
  renderSbSection(ASSETS.Tech,   'rowsTech',   false);
  renderSbSection(ASSETS.ETF,    'rowsETF',    false);
  renderSbSection(ASSETS.Crypto, 'rowsCrypto', true);
  renderSbSection(ASSETS.Forex,  'rowsForex',  false);
}
function renderSbSection(assets, containerId, isCrypto) {
  const el = document.getElementById(containerId); if (!el) return;
  el.innerHTML = assets.map(a => {
    const k=a.s.replace('/','_');
    const icon=isCrypto?`<div class="arow-icon" style="background:${a.c}22;color:${a.c}">${a.ic}</div>`:`<div class="arow-icon">${a.s.slice(0,2)}</div>`;
    return `<div class="arow${a.s===curSym?' active':''}" id="ar-${k}" onclick="selectSymbol('${a.s}',this)">${icon}<div class="arow-body"><div class="arow-sym">${a.s}</div><div class="arow-name">${a.n}</div></div><div class="arow-right"><div id="sbp-${k}">—</div><div id="sbc-${k}" class="arow-chg">—</div></div></div>`;
  }).join('');
}
function updateSbRow(sym, price, pct, isUp) {
  const k=sym.replace('/','_');
  const pe=document.getElementById('sbp-'+k),ce=document.getElementById('sbc-'+k);
  if(pe)pe.textContent='$'+fmtP(price);
  if(ce){ce.textContent=(isUp?'+':'')+parseFloat(pct).toFixed(2)+'%';ce.className='arow-chg '+(isUp?'up':'dn');}
}

// ════════════════════════════════════════════════════════════════
//  SIDEBAR SEARCH
// ════════════════════════════════════════════════════════════════
async function sbSearch(q) {
  const box=document.getElementById('sbResults');if(!box)return;
  if(!q){box.innerHTML='';return;}
  const ql=q.toLowerCase();
  const matches=Object.entries(SYM_INFO).filter(([s,i])=>s.toLowerCase().includes(ql)||i.n.toLowerCase().includes(ql)).slice(0,8);
  box.innerHTML=matches.map(([s,i])=>`<div class="sb-result-item" onclick="selectSymbol('${s}',null);document.getElementById('sbSearchIn').value='';document.getElementById('sbResults').innerHTML=''"><span class="sri-sym">${s}</span><span class="sri-name">${i.n}</span></div>`).join('');
}

// ════════════════════════════════════════════════════════════════
//  HERO
// ════════════════════════════════════════════════════════════════
async function updateHero(sym) {
  const heroEl=document.getElementById('heroSym');if(!heroEl)return;
  heroEl.textContent=sym;
  document.getElementById('heroPrice').textContent='…';
  document.getElementById('chartLabel').textContent=sym+' — Price Chart';
  const info=SYM_INFO[sym]??{};
  setEl('hCompany',info.n??sym);setEl('hExch',info.e??'');setEl('hSector',info.s??'');

  const data=await smartQuote(sym);
  if(!data||!parseFloat(data.close)){
    document.getElementById('heroPrice').textContent='—';
    showToast('No price for '+sym+'. Market may be closed.','error'); return;
  }
  liveQuote=data;
  const p=parseFloat(data.close),o=parseFloat(data.open||data.previous_close||p);
  const chg=p-o,pct=o>0?((chg/o)*100).toFixed(2):'0.00',isUp=chg>=0;
  document.getElementById('heroPrice').textContent='$'+fmtP(p);
  document.getElementById('heroPrice').className='h-price '+(isUp?'up':'dn');
  const pill=document.getElementById('heroChgPill');
  if(pill){pill.textContent=(isUp?'▲ +':'▼ ')+Math.abs(chg).toFixed(2)+' ('+(isUp?'+':'')+pct+'%)';pill.className='chg-pill '+(isUp?'pill-up':'pill-dn');}
  const si=data._symbol_info??{};
  if(si.name)setEl('hCompany',si.name);if(si.exchange)setEl('hExch',si.exchange);if(si.sector)setEl('hSector',si.sector);
  setEl('hOpen','$'+fmtP(parseFloat(data.open||o)));
  setEl('hHigh','$'+fmtP(parseFloat(data.high||p)));
  setEl('hLow', '$'+fmtP(parseFloat(data.low||p)));
  setEl('hVol',fmtVol(data.volume));
  setEl('hMktCap',data.market_cap&&parseFloat(data.market_cap)>0?fmtBig(parseFloat(data.market_cap)):'—');
  setEl('hPE',data.pe_ratio&&parseFloat(data.pe_ratio)>0?parseFloat(data.pe_ratio).toFixed(1)+'x':'—');
  updateSbRow(sym,p,pct,isUp);
}

// ════════════════════════════════════════════════════════════════
//  CHART
// ════════════════════════════════════════════════════════════════
const TF_CONFIG = {
  '1D': {interval:'5min', yi:'5m',  yr:'1d',  size:78,  label:'5 min',   intra:true},
  '5D': {interval:'30min',yi:'30m', yr:'5d',  size:56,  label:'30 min',  intra:true},
  '1M': {interval:'1day', yi:'1d',  yr:'1mo', size:30,  label:'Daily',   intra:false},
  '3M': {interval:'1day', yi:'1d',  yr:'3mo', size:91,  label:'Daily',   intra:false},
  '1Y': {interval:'1week',yi:'1wk', yr:'1y',  size:52,  label:'Weekly',  intra:false},
  '5Y': {interval:'1month',yi:'1mo',yr:'5y',  size:60,  label:'Monthly', intra:false},
};

async function loadChart(sym, tf) {
  const cfg=TF_CONFIG[tf]??TF_CONFIG['1D'];
  const footEl=document.getElementById('chartFoot');
  if(footEl)footEl.textContent='Loading chart…';
  const values=await smartTimeSeries(sym,cfg);
  if(!values||values.length<2){if(footEl)footEl.textContent='No chart data. Market may be closed.';renderEmptyChart('No live data — market closed or unavailable.');return;}
  const vals=[...values].reverse();
  if(footEl)footEl.textContent=vals.length+' candles · '+cfg.label+' · '+new Date().toLocaleTimeString();
  renderLineChart(sym,tf,vals);
}

function renderEmptyChart(msg){
  const ctx=document.getElementById('mainChart')?.getContext('2d');if(!ctx)return;
  if(chartInst){chartInst.destroy();chartInst=null;}
  chartInst=new Chart(ctx,{type:'line',data:{labels:[],datasets:[{data:[]}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},title:{display:true,text:'⚠ '+msg,color:'#7a9bb0',font:{size:13}}},scales:{x:{display:false},y:{display:false}}}});
}

function renderLineChart(sym,tf,vals){
  const ctx=document.getElementById('mainChart')?.getContext('2d');if(!ctx)return;
  if(chartInst){chartInst.destroy();chartInst=null;}
  const cfg=TF_CONFIG[tf]??TF_CONFIG['1D'];
  const closes=vals.map(v=>parseFloat(v.close));
  const isUp=closes[closes.length-1]>=closes[0];
  const lc=isUp?'#00d478':'#ff4060';
  const gr=ctx.createLinearGradient(0,0,0,280);
  gr.addColorStop(0,isUp?'rgba(0,212,120,.18)':'rgba(255,64,96,.18)');gr.addColorStop(1,'rgba(0,0,0,0)');
  const labels=vals.map(v=>{const d=new Date(v.datetime);if(cfg.intra)return d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});if(tf==='1Y'||tf==='5Y')return d.toLocaleDateString([],{month:'short',year:'2-digit'});return d.toLocaleDateString([],{month:'short',day:'numeric'});});
  const prec=closes[0]<1?6:closes[0]<100?2:0;
  chartInst=new Chart(ctx,{type:'line',data:{labels,datasets:[{label:'Price',data:closes,borderColor:lc,backgroundColor:gr,fill:true,tension:.35,pointRadius:0,pointHoverRadius:5,borderWidth:2.5}]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{display:false},tooltip:{backgroundColor:'rgba(8,12,18,.97)',borderColor:'rgba(0,212,120,.2)',borderWidth:1,titleColor:'#ddeef5',bodyColor:'#7a9bb0',padding:10,callbacks:{label:c=>' $'+parseFloat(c.parsed.y).toFixed(prec)}}},scales:{x:{grid:{color:'rgba(255,255,255,.025)',drawBorder:false},ticks:{color:'#3d5568',font:{size:9,family:"'JetBrains Mono'"},maxTicksLimit:9}},y:{position:'right',grid:{color:'rgba(255,255,255,.04)',drawBorder:false},ticks:{color:'#3d5568',font:{size:9,family:"'JetBrains Mono'"},callback:v=>'$'+(v>=1000?(v/1000).toFixed(1)+'k':parseFloat(v).toFixed(prec))}}}}});
}

function setTF(el,tf){curTF=tf;document.querySelectorAll('.tf-btn').forEach(b=>b.classList.remove('act'));el.classList.add('act');loadChart(curSym,tf);}

// ════════════════════════════════════════════════════════════════
//  SYMBOL SELECTION
// ════════════════════════════════════════════════════════════════
function selectSymbol(sym,rowEl){
  document.querySelectorAll('.arow').forEach(r=>r.classList.remove('active'));
  if(rowEl)rowEl.classList.add('active');
  else{const k=sym.replace('/','_');document.getElementById('ar-'+k)?.classList.add('active');}
  curSym=sym;
  if(CURRENT_PAGE==='markets'||!CURRENT_PAGE){updateHero(sym);loadChart(sym,curTF);loadNewsTab(sym);}
  setEl('predStatus','Click Analyze to run AI prediction for '+sym);setElClass('predStatus','pred-status');
}

// ════════════════════════════════════════════════════════════════
//  PREDICTION
// ════════════════════════════════════════════════════════════════
async function runPrediction(){
  if(!IS_LOGGED_IN){showModal('signinModal');return;}
  const sym=curSym;
  setEl('predStatus','');setElClass('predStatus','pred-status loading');
  document.getElementById('predText').style.display='none';
  document.getElementById('predMetrics').style.display='none';
  document.getElementById('confSection').style.display='none';
  const [qData,indData]=await Promise.all([smartQuote(sym),apiFetch({action:'indicators',sym})]);
  if(!qData||!parseFloat(qData.close)){setEl('predStatus','⚠ Cannot load market data for '+sym);setElClass('predStatus','pred-status');return;}
  const marketData={symbol:sym,currentPrice:parseFloat(qData.close),openPrice:parseFloat(qData.open||0),high:parseFloat(qData.high||0),low:parseFloat(qData.low||0),volume:parseInt(qData.volume||0),marketCap:parseFloat(qData.market_cap||0),changePercent:parseFloat(qData.change_percent||0),rsi:indData?.rsi?.rsi??null,macd:indData?.macd?.macd??null,ema20:indData?.ema20?.ema??null,ema50:indData?.ema50?.ema??null,sma200:indData?.sma200?.sma??null};
  const result=await apiPost({action:'predict',sym,market_data:JSON.stringify(marketData)});
  if(result.error){setEl('predStatus','⚠ '+result.error);setElClass('predStatus','pred-status');return;}
  setElClass('predStatus','pred-status');setEl('predStatus','');
  const pt=document.getElementById('predText');if(pt){pt.innerHTML=result.text.replace(/\n/g,'<br>');pt.style.display='block';}
  const sc={BUY:'#00d478',SELL:'#ff4060',HOLD:'#f0b429'};
  setEl('pmTarget',result.target?'$'+fmtP(result.target):'—');
  const ps=document.getElementById('pmSignal');if(ps){ps.textContent=result.signal;ps.style.color=sc[result.signal]||'#fff';}
  setEl('pmRisk',result.risk||'—');setEl('pmHorizon','1–7 Days');
  document.getElementById('predMetrics').style.display='';
  const cp=document.getElementById('confPct'),cf=document.getElementById('confFill');
  if(cp)cp.textContent=result.confidence+'%';if(cf)cf.style.width=result.confidence+'%';
  document.getElementById('confSection').style.display='';
}

// ════════════════════════════════════════════════════════════════
//  TABS
// ════════════════════════════════════════════════════════════════
async function loadNewsTab(sym){
  const box=document.getElementById('tab-news');if(!box)return;
  box.innerHTML='<div class="loading-box"><div class="spinner"></div> Loading news…</div>';
  const d=await apiFetch({action:'news',sym});
  const arts=d?.articles??[];
  if(!arts.length){box.innerHTML='<div class="empty-msg">No recent news.</div>';return;}
  box.innerHTML=arts.map(a=>{const src=a.source?.name??'News';const s=sentiment(a.title||'');return`<div class="ncard"><div class="ncard-top"><span class="nsrc">${src}</span><span class="ntago">${tago(a.publishedAt)}</span><span class="sent ${s.c}">${s.l}</span></div><a class="ncard-title" href="${a.url||'#'}" target="_blank" rel="noopener">${a.title||'No title'}</a>${a.description?`<div class="ncard-desc">${a.description.slice(0,200)}…</div>`:''}</div>`;}).join('');
}
async function loadTechTab(sym){
  const box=document.getElementById('tab-tech');if(!box)return;
  box.innerHTML='<div class="loading-box"><div class="spinner"></div> Computing indicators…</div>';
  const d=await apiFetch({action:'indicators',sym});
  if(!d||d.error){box.innerHTML='<div class="empty-msg">Indicator data unavailable.</div>';return;}
  const rsi=d.rsi?.rsi,macd=d.macd?.macd,e20=d.ema20?.ema,e50=d.ema50?.ema,s200=d.sma200?.sma,bbu=d.bbands?.upper_band,bbl=d.bbands?.lower_band;
  const p=parseFloat(liveQuote.close||0);
  const rc=rsi?(rsi>70?'#ff4060':rsi<30?'#00d478':'#f0b429'):'#7a9bb0';
  box.innerHTML=`<div class="ind-grid">${rsi!=null?`<div class="ind-card"><div class="ind-l">RSI (14)</div><div class="ind-v" style="color:${rc}">${rsi}</div><div class="ind-s">${rsi>70?'Overbought':rsi<30?'Oversold':'Neutral'}</div></div>`:''}`+
  `${macd!=null?`<div class="ind-card"><div class="ind-l">MACD</div><div class="ind-v" style="color:${macd>0?'#00d478':'#ff4060'}">${macd.toFixed(4)}</div><div class="ind-s">${macd>0?'Bullish':'Bearish'}</div></div>`:''}`+
  `${e20!=null?`<div class="ind-card"><div class="ind-l">EMA 20</div><div class="ind-v">$${fmtP(e20)}</div><div class="ind-s">${p&&p>e20?'Above':'Below'}</div></div>`:''}`+
  `${e50!=null?`<div class="ind-card"><div class="ind-l">EMA 50</div><div class="ind-v">$${fmtP(e50)}</div><div class="ind-s">${p&&p>e50?'Above':'Below'}</div></div>`:''}`+
  `${s200!=null?`<div class="ind-card"><div class="ind-l">SMA 200</div><div class="ind-v">$${fmtP(s200)}</div><div class="ind-s">${p&&p>s200?'Bullish':'Bearish'}</div></div>`:''}`+
  `${bbu!=null?`<div class="ind-card"><div class="ind-l">BB Upper</div><div class="ind-v">$${fmtP(bbu)}</div></div>`:''}`+
  `${bbl!=null?`<div class="ind-card"><div class="ind-l">BB Lower</div><div class="ind-v">$${fmtP(bbl)}</div></div>`:''}</div>`;
}
async function loadProfileTab(sym){
  const box=document.getElementById('tab-profile');if(!box)return;
  if(sym.includes('/')){box.innerHTML='<div class="empty-msg">Profile not available for forex/crypto.</div>';return;}
  box.innerHTML='<div class="loading-box"><div class="spinner"></div> Loading…</div>';
  const d=await apiFetch({action:'profile',sym});
  const p=d?.profile??{},m=d?.metrics??{};
  if(!p.name&&!m.marketCapitalization){box.innerHTML='<div class="empty-msg">Profile not available.</div>';return;}
  box.innerHTML=`<div class="prof-wrap">${p.name?`<div class="prof-name">${p.name}</div>`:''}`+
  `${p.finnhubIndustry?`<div class="prof-ind">${p.finnhubIndustry}</div>`:''}`+
  `<div class="prof-stats">${m.marketCapitalization?`<div class="ps"><span>Mkt Cap</span><b>$${fmtBig(m.marketCapitalization*1e6)}</b></div>`:''}`+
  `${m.peNormalizedAnnual?`<div class="ps"><span>P/E</span><b>${parseFloat(m.peNormalizedAnnual).toFixed(1)}x</b></div>`:''}`+
  `${m['52WeekHigh']?`<div class="ps"><span>52W High</span><b>$${fmtP(m['52WeekHigh'])}</b></div>`:''}`+
  `${m['52WeekLow']?`<div class="ps"><span>52W Low</span><b>$${fmtP(m['52WeekLow'])}</b></div>`:''}</div>`+
  `${p.weburl?`<a class="prof-link" href="${p.weburl}" target="_blank">${p.weburl}</a>`:''}</div>`;
}
async function loadEconTab(){
  const box=document.getElementById('tab-econ');if(!box)return;
  box.innerHTML='<div class="loading-box"><div class="spinner"></div> Loading…</div>';
  const d=await apiFetch({action:'fred'});
  if(!d){box.innerHTML='<div class="empty-msg">Economic data unavailable.</div>';return;}
  const lbls={FEDFUNDS:'Fed Funds Rate',CPIAUCSL:'CPI',UNRATE:'Unemployment',GDP:'GDP Growth',DGS10:'10Y Treasury',DGS2:'2Y Treasury',DGS30:'30Y Treasury',VIXCLS:'VIX',DEXUSEU:'EUR/USD',DEXJPUS:'USD/JPY',DCOILWTICO:'Oil (WTI)',GOLDAMGBD228NLBM:'Gold $/oz'};
  box.innerHTML='<div class="econ-grid">'+Object.entries(d).filter(([k,v])=>v?.value).map(([k,v])=>`<div class="econ-card"><div class="econ-l">${lbls[k]||k}</div><div class="econ-v">${v.value}</div><div class="econ-d">${v.date}</div></div>`).join('')+'</div>';
}
function switchTab(el,tab){
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('act'));el.classList.add('act');
  ['news','tech','profile','econ'].forEach(t=>{const b=document.getElementById('tab-'+t);if(b)b.style.display=t===tab?'':'none';});
  if(tab==='news')loadNewsTab(curSym);else if(tab==='tech')loadTechTab(curSym);else if(tab==='profile')loadProfileTab(curSym);else if(tab==='econ')loadEconTab();
}

// ════════════════════════════════════════════════════════════════
//  RIGHT PANEL + TAPE + SIDEBAR
// ════════════════════════════════════════════════════════════════
async function buildRightPanel(){
  const idxs=[{s:'SPY',l:'S&P 500'},{s:'QQQ',l:'NASDAQ'},{s:'IWM',l:'Russell 2K'},{s:'GLD',l:'Gold'},{s:'BTC/USD',l:'Bitcoin'}];
  const rpi=document.getElementById('rpIndices');
  if(rpi){rpi.innerHTML=idxs.map(i=>`<div class="irow" onclick="selectSymbol('${i.s}',null)"><span class="idx-l">${i.l}</span><span id="ip-${i.s.replace('/','_')}">…</span></div>`).join('');
    for(const idx of idxs){const q=await smartQuote(idx.s);if(q&&parseFloat(q.close)>0){const p=parseFloat(q.close),o=parseFloat(q.open||q.previous_close||p),up=p>=o;const el=document.getElementById('ip-'+idx.s.replace('/','_'));if(el){el.textContent='$'+fmtP(p);el.className=up?'up':'dn';}}}}
}
async function buildTape(){
  const el=document.getElementById('tapeScroll');if(!el)return;
  const syms=['AAPL','MSFT','GOOGL','TSLA','NVDA','META','AMZN','BTC/USD','ETH/USD','SPY','QQQ','GLD'];
  el.innerHTML=syms.map(s=>`<span class="titem"><span class="ts">${s}</span>&nbsp;<span class="tv" id="ti-${s.replace('/','_')}">…</span></span>`).join('')+syms.map(s=>`<span class="titem"><span class="ts">${s}</span>&nbsp;<span class="tv" id="ti2-${s.replace('/','_')}">…</span></span>`).join('');
  for(const s of syms){const q=await smartQuote(s);if(q&&parseFloat(q.close)>0){const p=parseFloat(q.close),o=parseFloat(q.open||q.previous_close||p),pct=((p-o)/Math.max(o,.0001)*100).toFixed(2),up=p>=o;const txt='$'+fmtP(p)+' '+(up?'▲':'▼')+Math.abs(pct)+'%';[`ti-${s.replace('/','_')}`,`ti2-${s.replace('/','_')}`].forEach(id=>{const e=document.getElementById(id);if(e){e.textContent=txt;e.className='tv '+(up?'up':'dn');}});}await sleep(200);}
}
async function loadSidebarPrices(){
  const all=[...ASSETS.US,...ASSETS.Tech,...ASSETS.ETF,...ASSETS.Crypto.slice(0,4),...ASSETS.Forex.slice(0,3)];
  for(const a of all){if(a.s===curSym)continue;const q=await smartQuote(a.s);if(q&&parseFloat(q.close)>0){const p=parseFloat(q.close),o=parseFloat(q.open||q.previous_close||p),pct=((p-o)/Math.max(o,.0001)*100).toFixed(2),up=p>=o;updateSbRow(a.s,p,pct,up);}await sleep(150);}
}

// ════════════════════════════════════════════════════════════════
//  AUTH
// ════════════════════════════════════════════════════════════════
function showModal(id){const m=document.getElementById(id);if(m)m.style.display='flex';document.body.style.overflow='hidden';}
function hideModal(id){const m=document.getElementById(id);if(m)m.style.display='none';document.body.style.overflow='';}
function switchModal(h,s){hideModal(h);showModal(s);}
function togglePass(id){const e=document.getElementById(id);if(e)e.type=e.type==='password'?'text':'password';}
function toggleUserMenu(){document.getElementById('userDropdown')?.classList.toggle('open');}
async function doSignIn(){const login=document.getElementById('siLogin')?.value?.trim(),pass=document.getElementById('siPass')?.value,errEl=document.getElementById('signinErr');if(!login||!pass){if(errEl)errEl.textContent='Please fill in all fields.';return;}const btn=document.querySelector('#signinModal .form-btn');btn.disabled=true;btn.textContent='Signing in…';const r=await apiPost({action:'signin',login,password:pass});btn.disabled=false;btn.textContent='Sign In';if(r.error){if(errEl){errEl.textContent=r.error;errEl.style.display='block';}return;}showToast('Welcome back, '+r.name+'!','success');setTimeout(()=>window.location.href=r.redirect||'/',1000);}
async function doSignUp(){if(!document.getElementById('suTerms')?.checked){showToast('Please accept terms.','error');return;}const errEl=document.getElementById('signupErr');const body={action:'signup',name:document.getElementById('suName')?.value||'',email:document.getElementById('suEmail')?.value||'',country_code:document.getElementById('suCC')?.value||'+1',phone:document.getElementById('suPhone')?.value||'',password:document.getElementById('suPass')?.value||'',password2:document.getElementById('suPass2')?.value||''};const btn=document.querySelector('#signupModal .form-btn');btn.disabled=true;btn.textContent='Creating…';const r=await apiPost(body);btn.disabled=false;btn.textContent='Create Account';if(r.error){if(errEl){errEl.textContent=r.error;errEl.style.display='block';}return;}showToast('Welcome, '+r.name+'!','success');setTimeout(()=>window.location.href=r.redirect||'/',1000);}
async function doSignOut(){await fetch('/auth.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'signout',csrf:CSRF})});window.location.href='/';}
async function toggleWatchlist(sym){if(!IS_LOGGED_IN){showModal('signinModal');return;}const r=await apiPost({action:'watchlist_add',sym});showToast(r.ok?sym+' added to watchlist':r.error||'Failed',r.ok?'success':'error');}

// ════════════════════════════════════════════════════════════════
//  UTILS + MARKET STATUS
// ════════════════════════════════════════════════════════════════
function updateMarketStatus(){const d=new Date(),day=d.getDay(),h=d.getUTCHours(),m=d.getUTCMinutes(),tot=h*60+m;const open=day>=1&&day<=5&&tot>=870&&tot<1200;const t=document.getElementById('mktTxt'),dot=document.getElementById('mktDot');if(t){t.textContent=open?'MARKET OPEN':'MARKET CLOSED';t.style.color=open?'var(--g)':'var(--t2)';}if(dot)dot.style.background=open?'var(--g)':'#555';}
function fmtP(n){if(n===null||n===undefined||n===''||n==='—')return'—';const v=parseFloat(n);if(isNaN(v)||v===0)return'—';if(v>=1000)return v.toLocaleString('en',{minimumFractionDigits:2,maximumFractionDigits:2});if(v>=1)return v.toFixed(2);if(v>=.01)return v.toFixed(4);return v.toFixed(6);}
function fmtVol(v){if(!v)return'—';const n=parseInt(v);if(n>=1e9)return(n/1e9).toFixed(1)+'B';if(n>=1e6)return(n/1e6).toFixed(1)+'M';if(n>=1e3)return(n/1e3).toFixed(0)+'K';return n.toString();}
function fmtBig(n){if(!n)return'—';if(n>=1e12)return(n/1e12).toFixed(2)+'T';if(n>=1e9)return(n/1e9).toFixed(2)+'B';if(n>=1e6)return(n/1e6).toFixed(2)+'M';return n.toFixed(0);}
function tago(ds){if(!ds)return'';const d=Date.now()-new Date(ds).getTime(),h=Math.floor(d/3600000);if(h<1)return Math.floor(d/60000)+'m ago';if(h<24)return h+'h ago';return Math.floor(h/24)+'d ago';}
function sentiment(t){const b=/beat|surge|rally|gain|bull|strong|rise|up|profit|growth|buy|upgrade/i.test(t),s=/drop|fall|loss|bear|weak|down|decline|miss|sell|cut|risk|layoff|crash/i.test(t);if(b&&!s)return{c:'sb',l:'BULLISH'};if(s&&!b)return{c:'ss',l:'BEARISH'};return{c:'sn',l:'NEUTRAL'};}
function setEl(id,v){const e=document.getElementById(id);if(e)e.textContent=v;}
function setElClass(id,c){const e=document.getElementById(id);if(e)e.className=c;}
function showToast(msg,type='info'){const t=document.getElementById('toast');if(!t)return;t.textContent=msg;t.className='toast toast-'+type+' show';setTimeout(()=>t.classList.remove('show'),3500);}

// ════════════════════════════════════════════════════════════════
//  INIT
// ════════════════════════════════════════════════════════════════
(async()=>{
  updateMarketStatus();
  buildSidebar();
  if(CURRENT_PAGE==='markets'){
    await Promise.all([updateHero(curSym),loadChart(curSym,'1D'),loadNewsTab(curSym)]);
    buildRightPanel();buildTape();loadSidebarPrices();
  } else if(CURRENT_PAGE==='economy'){loadEconTab();}
})();
setInterval(()=>{if(CURRENT_PAGE==='markets'){updateHero(curSym);updateMarketStatus();}},90000);
if(window.location.search.includes('page=signin'))showModal('signinModal');
if(window.location.search.includes('page=signup'))showModal('signupModal');
</script>
<?php include __DIR__ . '/assets/js/pages.js.php'; ?>
</body>
</html>
