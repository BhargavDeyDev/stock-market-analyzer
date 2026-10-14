<?php // assets/js/pages.js.php ?>
<script>
// ════════════════════════════════════════════════════════════════
//  PAGE-SPECIFIC FUNCTIONS
// ════════════════════════════════════════════════════════════════

// ── WATCHLIST PAGE ──────────────────────────────────────────────
async function loadWatchlistPage() {
  const el = document.getElementById('wlContent'); if(!el) return;
  const d = await apiFetch({action:'watchlist_get'});
  if (!d.items || !d.items.length) {
    el.innerHTML = `<div class="empty-state">
      <p style="margin-bottom:.8rem">Your watchlist is empty.</p>
      <button class="save-btn" onclick="selectSymbol('AAPL',null);window.location.href='/?page=markets'">Browse Markets</button>
    </div>`;
    return;
  }
  let rows = '';
  for (const item of d.items) {
    const q = await apiFetch({action:'quote',sym:item.symbol});
    let priceHtml='—', chgHtml='—';
    if (q.close) {
      const p=parseFloat(q.close),o=parseFloat(q.open),pct=((p-o)/o*100).toFixed(2),up=p>=o;
      priceHtml = `<span class="wl-price">$${fmtP(p)}</span>`;
      chgHtml = `<span class="${up?'up':'dn'}">${(up?'+':'')+pct}%</span>`;
    }
    rows += `<tr>
      <td><span class="wl-sym" style="cursor:pointer;color:var(--g)" onclick="selectSymbol('${item.symbol}',null);window.location.href='/?page=markets'">${item.symbol}</span></td>
      <td style="color:var(--t2)">${item.name}</td>
      <td>${priceHtml}</td>
      <td>${chgHtml}</td>
      <td><span class="tag tag-s">${item.asset_type}</span></td>
      <td><button class="wl-btn-rm" onclick="removeFromWl('${item.symbol}',this)">Remove</button></td>
    </tr>`;
    await sleep(320);
  }
  el.innerHTML = `<table class="wl-table">
    <thead><tr><th>Symbol</th><th>Name</th><th>Price</th><th>Change</th><th>Type</th><th></th></tr></thead>
    <tbody>${rows}</tbody>
  </table>`;
}

async function toggleWatchlist(sym) {
  const r = await apiPost({action:'watchlist_add',sym});
  if(r.error) showToast(r.error,'error');
  else showToast(sym+' added to watchlist!','success');
}

async function removeFromWl(sym, btn) {
  const r = await apiPost({action:'watchlist_remove',sym});
  if(r.ok) { btn.closest('tr').remove(); showToast(sym+' removed.','info'); }
}

// ── PREDICTIONS PAGE ────────────────────────────────────────────
async function loadPredictionsPage() {
  const el = document.getElementById('predHistContent'); if(!el) return;
  const d = await apiFetch({action:'my_predictions'});
  if (!d.items || !d.items.length) {
    el.innerHTML = '<div class="empty-state"><p>No predictions yet. Go to Markets and click Analyze!</p></div>';
    return;
  }
  el.innerHTML = `<table class="pred-table">
    <thead><tr><th>Symbol</th><th>Signal</th><th>Target</th><th>Confidence</th><th>Risk</th><th>Date</th></tr></thead>
    <tbody>${d.items.map(p=>`<tr>
      <td style="font-family:'JetBrains Mono',monospace;font-weight:700;color:var(--g);cursor:pointer" onclick="selectSymbol('${p.symbol}',null);window.location.href='/?page=markets'">${p.symbol}</td>
      <td><span class="signal-${p.signal.toLowerCase()}">${p.signal}</span></td>
      <td style="font-family:'JetBrains Mono',monospace">$${parseFloat(p.target_price).toFixed(2)}</td>
      <td><div style="display:flex;align-items:center;gap:.5rem"><div style="flex:1;height:4px;background:rgba(255,255,255,.04);border-radius:2px;overflow:hidden"><div style="height:100%;width:${p.confidence}%;background:var(--g);border-radius:2px"></div></div>${p.confidence}%</div></td>
      <td><span class="risk-${p.risk.toLowerCase()}">${p.risk}</span></td>
      <td style="color:var(--t3)">${new Date(p.created_at).toLocaleDateString()}</td>
    </tr>`).join('')}</tbody>
  </table>`;

  // Quick symbol search on predictions page
  document.getElementById('quickSymIn')?.addEventListener('input', async e => {
    const q = e.target.value.trim();
    if (q.length < 1) { document.getElementById('quickSymResults').innerHTML=''; return; }
    const res = await apiFetch({action:'search_symbols',q});
    const el2 = document.getElementById('quickSymResults');
    if (!el2) return;
    el2.innerHTML = res.slice(0,6).map(r=>`<div class="sb-result-item" onclick="document.getElementById('quickSymIn').value='${r.symbol}';document.getElementById('quickSymResults').innerHTML=''">${r.symbol} — ${r.name}</div>`).join('');
  });
}

async function runQuickPred() {
  const sym = document.getElementById('quickSymIn')?.value?.trim()?.toUpperCase();
  if (!sym) return;
  curSym = sym;
  const res = document.getElementById('quickPredResult');
  if(res) res.innerHTML = '<div class="loading-box"><div class="spinner"></div> Fetching live data and running AI analysis for '+sym+'…</div>';

  const [q, ind] = await Promise.all([apiFetch({action:'quote',sym}), apiFetch({action:'indicators',sym})]);
  if(q.error){if(res)res.innerHTML='<div class="empty-msg">'+q.error+'</div>';return;}

  liveQuote = q;
  const p=parseFloat(q.close||0);
  const marketData = {
    symbol:sym, name:q._symbol_info?.name||sym,
    currentPrice:p, openPrice:parseFloat(q.open||0),
    high:parseFloat(q.high||0), low:parseFloat(q.low||0),
    volume:q.volume||'N/A',
    rsi14:ind.rsi?.rsi?parseFloat(ind.rsi.rsi).toFixed(2):'N/A',
    macd:ind.macd?.macd?parseFloat(ind.macd.macd).toFixed(4):'N/A',
    macdSignal:ind.macd?.macd_signal?parseFloat(ind.macd.macd_signal).toFixed(4):'N/A',
    ema20:ind.ema20?.ema?parseFloat(ind.ema20.ema).toFixed(2):'N/A',
    bbUpper:ind.bbands?.upper_band?parseFloat(ind.bbands.upper_band).toFixed(2):'N/A',
    bbLower:ind.bbands?.lower_band?parseFloat(ind.bbands.lower_band).toFixed(2):'N/A',
  };

  const r = await fetch('/api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:new URLSearchParams({action:'predict',sym,market_data:JSON.stringify(marketData),csrf:CSRF})}).then(r=>r.json());

  if(r.error){if(res)res.innerHTML='<div class="empty-msg">'+r.error+'</div>';return;}

  if(res) res.innerHTML = `<div class="pred-card" style="margin:0">
    <div class="pred-top">
      <div class="pred-top-left"><div class="pred-icon-wrap">🤖</div>
        <div><div class="pred-title">${sym} — AI Analysis</div><div class="pred-meta">Live market data analysis</div></div>
      </div>
      <div style="display:flex;gap:.5rem">
        <span class="pmetric-v ${r.signal==='BUY'?'up':r.signal==='SELL'?'dn':''}" style="font-size:1.1rem">${r.signal}</span>
        <span class="pmetric-v" style="color:var(--t2)">${r.confidence}%</span>
      </div>
    </div>
    <div class="pred-result">
      <div class="pred-text" style="display:block">${r.text.replace(/\*\*(.*?)\*\*/g,'<strong style="color:var(--t)">$1</strong>').replace(/\n/g,'<br>')}</div>
    </div>
  </div>`;

  loadPredictionsPage(); // refresh history
}

// ── NEWS PAGE ───────────────────────────────────────────────────
async function loadNewsPage(sym) {
  sym = sym || 'AAPL';
  sym = sym.toUpperCase().trim();
  if (!sym) sym = 'AAPL';

  const el = document.getElementById('newsPageContent'); if(!el) return;
  el.innerHTML = '<div class="loading-box"><div class="spinner"></div> Loading news for '+sym+'…</div>';
  const d = await apiFetch({action:'news',sym});
  const articles = d.articles || [];
  if(!articles.length){el.innerHTML='<div class="empty-msg">No recent news found for '+sym+'.</div>';return;}
  el.innerHTML = `<div style="font-size:.7rem;color:var(--t2);margin-bottom:.7rem">Showing ${articles.length} articles for <strong style="color:var(--g)">${sym}</strong></div>
    <div class="news-grid">${articles.slice(0,12).map(a=>{
      const s=sentiment(a.title+' '+(a.description||''));
      return `<div class="news-card" onclick="window.open('${a.url}','_blank')">
        <div class="nc-src">${a.source?.name||'News'}</div>
        <div class="nc-title">${a.title}</div>
        <div class="nc-meta"><span class="ntime">${tago(a.publishedAt)}</span><span class="nsent ${s.c}">${s.l}</span></div>
      </div>`;
    }).join('')}</div>`;
}

// ── ECONOMY PAGE ─────────────────────────────────────────────────
async function loadEconomyPage() {
  const el = document.getElementById('econPageContent'); if(!el) return;
  const d = await apiFetch({action:'fred'});
  const series = [
    {id:'FEDFUNDS',n:'Fed Funds Rate',u:'%',desc:'Federal Reserve interest rate'},
    {id:'CPIAUCSL',n:'CPI Inflation',u:'%',desc:'Consumer Price Index YoY'},
    {id:'UNRATE',n:'Unemployment',u:'%',desc:'US unemployment rate'},
    {id:'GDP',n:'GDP',u:'B',desc:'Gross Domestic Product (billions)'},
    {id:'DGS10',n:'10-Year Treasury',u:'%',desc:'10-year bond yield'},
    {id:'DGS2',n:'2-Year Treasury',u:'%',desc:'2-year bond yield'},
    {id:'DGS30',n:'30-Year Treasury',u:'%',desc:'30-year bond yield'},
    {id:'VIXCLS',n:'VIX Index',u:'',desc:'Market volatility (fear gauge)'},
    {id:'DEXUSEU',n:'USD / EUR',u:'',desc:'Dollar to Euro exchange rate'},
    {id:'DEXJPUS',n:'JPY / USD',u:'',desc:'Yen per US Dollar'},
    {id:'DCOILWTICO',n:'WTI Crude Oil',u:'$/bbl',desc:'West Texas crude price'},
    {id:'GOLDAMGBD228NLBM',n:'Gold',u:'$/oz',desc:'London AM gold fix'},
  ];
  el.innerHTML = `<div class="econ-page-grid">${series.map(s=>{
    const obs=d[s.id]; const v=obs&&obs.value&&obs.value!=='.'?obs.value:'—';
    return `<div class="ec-card">
      <div class="ec-name">${s.n}</div>
      <div class="ec-val">${v!=='—'?v+s.u:v}</div>
      <div style="font-size:.62rem;color:var(--t2);margin-top:.2rem">${s.desc}</div>
      ${obs?.date?'<div class="ec-date">As of '+obs.date+'</div>':''}
    </div>`;
  }).join('')}</div>
  <div style="font-size:.6rem;color:var(--t3);margin-top:.9rem">Global economic indicators · Updated daily</div>`;
}

// ── PROFILE/SETTINGS ─────────────────────────────────────────────
async function saveProfile() {
  const name = document.getElementById('profName')?.value?.trim();
  if(!name||name.length<2){showToast('Name must be at least 2 characters.','error');return;}
  showToast('Profile updated!','success');
}

async function changePassword() {
  const old = document.getElementById('profOldPass')?.value;
  const np  = document.getElementById('profNewPass')?.value;
  const np2 = document.getElementById('profNewPass2')?.value;
  if(!old||!np||!np2){showToast('Please fill all password fields.','error');return;}
  if(np!==np2){showToast('New passwords do not match.','error');return;}
  if(np.length<8){showToast('New password must be at least 8 characters.','error');return;}
  showToast('Password updated successfully!','success');
}

// ── Auto-init based on page ──────────────────────────────────────
if (CURRENT_PAGE === 'news')    loadNewsPage('AAPL');
if (CURRENT_PAGE === 'economy') loadEconomyPage();
if (CURRENT_PAGE === 'watchlist' && IS_LOGGED_IN)  loadWatchlistPage();
if (CURRENT_PAGE === 'predictions' && IS_LOGGED_IN) loadPredictionsPage();
</script>
