<?php // pages/predictions.php ?>
<div class="page-title">Prediction History</div>
<div class="page-sub">All AI predictions you've generated, powered by live market data.</div>
<?php if(!isLoggedIn()): ?>
  <div class="page-card">
    <div class="empty-state">
      <p>Please <a href="#" onclick="showModal('signinModal')" style="color:var(--g)">sign in</a> to view your prediction history.</p>
    </div>
  </div>
<?php else: ?>
<div class="page-card">
  <div class="page-card-title">Recent Predictions</div>
  <div id="predHistContent"><div class="loading-box"><div class="spinner"></div> Loading predictions…</div></div>
</div>
<!-- Quick analyze box -->
<div class="page-card">
  <div class="page-card-title">Run New Prediction</div>
  <div style="display:flex;gap:.7rem;align-items:center">
    <div style="position:relative;flex:1">
      <input type="text" class="inp" id="quickSymIn" placeholder="Enter verified symbol (e.g. AAPL, BTC/USD)" style="margin:0"/>
      <div class="sb-results" id="quickSymResults" style="position:absolute;top:calc(100%+3px);left:0;right:0;z-index:50"></div>
    </div>
    <button class="save-btn" onclick="runQuickPred()">Analyze</button>
  </div>
  <div id="quickPredResult" style="margin-top:1rem"></div>
</div>
<?php endif; ?>
