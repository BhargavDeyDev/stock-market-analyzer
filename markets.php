<?php // pages/markets.php ?>
<!-- Hero -->
<div class="hero">
  <div class="hero-row1">
    <div class="h-left">
      <div class="h-sym" id="heroSym">AAPL</div>
      <div class="h-company" id="hCompany">Apple Inc.</div>
      <div class="h-tags">
        <span class="tag tag-e" id="hExch">NASDAQ</span>
        <span class="tag tag-s" id="hSector">Technology</span>
      </div>
    </div>
    <div class="h-right">
      <div class="h-price" id="heroPrice">—</div>
      <div><span class="chg-pill pill-up" id="heroChgPill">—</span></div>
      <?php if(isLoggedIn()): ?>
      <div style="margin-top:.5rem">
        <button class="nbtn nbtn-o" style="font-size:.62rem;padding:.25rem .6rem" onclick="toggleWatchlist('<?= curSym() ?>')">+ Watchlist</button>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="hero-stats">
    <div class="hstat"><div class="hstat-l">Open</div>   <div class="hstat-v" id="hOpen">—</div></div>
    <div class="hstat"><div class="hstat-l">High</div>   <div class="hstat-v up" id="hHigh">—</div></div>
    <div class="hstat"><div class="hstat-l">Low</div>    <div class="hstat-v dn" id="hLow">—</div></div>
    <div class="hstat"><div class="hstat-l">Volume</div> <div class="hstat-v" id="hVol">—</div></div>
    <div class="hstat"><div class="hstat-l">Mkt Cap</div><div class="hstat-v" id="hMktCap">—</div></div>
    <div class="hstat"><div class="hstat-l">P/E</div>    <div class="hstat-v" id="hPE">—</div></div>
  </div>
</div>

<!-- Chart -->
<div class="chart-card">
  <div class="chart-hdr">
    <div class="chart-label-wrap">
      <div class="chart-label" id="chartLabel">AAPL — Price Chart</div>
      <div class="chart-sub" id="chartSub">Live market data</div>
    </div>
    <div class="tf-group">
      <button class="tf-btn act" onclick="setTF(this,'1D')">1D</button>
      <button class="tf-btn"     onclick="setTF(this,'1H')">1H</button>
      <button class="tf-btn"     onclick="setTF(this,'1M')">1M</button>
      <button class="tf-btn"     onclick="setTF(this,'5D')">5D</button>
      <button class="tf-btn"     onclick="setTF(this,'1Mo')">1Mo</button>
      <button class="tf-btn"     onclick="setTF(this,'3Mo')">3Mo</button>
      <button class="tf-btn"     onclick="setTF(this,'1Y')">1Y</button>
      <button class="tf-btn"     onclick="setTF(this,'5Y')">5Y</button>
    </div>
  </div>
  <div class="chart-body"><canvas id="mainChart"></canvas></div>
  <div class="chart-foot" id="chartFoot">Fetching live market data…</div>
</div>

<!-- AI Prediction -->
<div class="pred-card">
  <div class="pred-top">
    <div class="pred-top-left">
      <div class="pred-icon-wrap">🤖</div>
      <div>
        <div class="pred-title">AI Market Prediction</div>
        <div class="pred-meta" id="predMeta">
          Stockry AI analyzes live market data, technicals, and fundamentals.<br>
          No simulated data. Only verified symbols. <?php if(!isLoggedIn()): ?><strong>Sign in to analyze.</strong><?php endif; ?>
        </div>
      </div>
    </div>
    <button class="analyze-btn" id="analyzeBtn" onclick="runPrediction()">Analyze</button>
  </div>
  <div class="pred-result">
    <div class="pred-status" id="predStatus">Select a symbol from the sidebar and click Analyze.</div>
    <div class="pred-text" id="predText" style="display:none"></div>
    <div class="pred-metrics" id="predMetrics" style="display:none">
      <div class="pmetric"><div class="pmetric-l">7-Day Target</div><div class="pmetric-v" id="pmTarget">—</div></div>
      <div class="pmetric"><div class="pmetric-l">Signal</div>      <div class="pmetric-v" id="pmSignal">—</div></div>
      <div class="pmetric"><div class="pmetric-l">Risk Level</div>  <div class="pmetric-v" id="pmRisk">—</div></div>
      <div class="pmetric"><div class="pmetric-l">Horizon</div>     <div class="pmetric-v" id="pmHorizon">—</div></div>
    </div>
    <div class="conf-section" id="confSection" style="display:none">
      <div class="conf-row"><span>AI Confidence Score</span><span id="confPct">—</span></div>
      <div class="conf-track"><div class="conf-fill" id="confFill" style="width:0%"></div></div>
    </div>
  </div>
</div>

<!-- Tabs -->
<div class="tabs-bar">
  <button class="tab-btn act" data-tab="news"    onclick="switchTab(this,'news')">📰 News</button>
  <button class="tab-btn"     data-tab="tech"    onclick="switchTab(this,'tech')">📊 Technicals</button>
  <button class="tab-btn"     data-tab="profile" onclick="switchTab(this,'profile')">🏢 Company</button>
  <button class="tab-btn"     data-tab="econ"    onclick="switchTab(this,'econ')">🏦 Economy</button>
</div>

<div id="tab-news"><div class="loading-box"><div class="spinner"></div> Loading news…</div></div>
<div id="tab-tech"    style="display:none"></div>
<div id="tab-profile" style="display:none"></div>
<div id="tab-econ"    style="display:none"></div>

<?php
function curSym() { return isset($_SESSION['last_sym']) ? htmlspecialchars($_SESSION['last_sym']) : 'AAPL'; }
?>
