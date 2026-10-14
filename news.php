<?php // pages/news.php ?>
<div class="page-title">Market News</div>
<div class="page-sub">Real-time financial news — click a category or search by symbol.</div>

<div style="display:flex;gap:.5rem;margin-bottom:1.1rem;flex-wrap:wrap">
  <?php $topics = ['AAPL'=>'Apple','MSFT'=>'Microsoft','NVDA'=>'NVIDIA','TSLA'=>'Tesla','BTC/USD'=>'Bitcoin','SPY'=>'S&P 500','GOOGL'=>'Alphabet','META'=>'Meta'];
  foreach($topics as $sym=>$label): ?>
    <button class="tf-btn" onclick="loadNewsPage('<?= $sym ?>')"><?= $label ?></button>
  <?php endforeach; ?>
</div>

<div style="display:flex;gap:.5rem;margin-bottom:1.2rem">
  <input type="text" class="inp" id="newsSymIn" placeholder="Filter by symbol (e.g. JPM, ETH/USD)…" style="margin:0"/>
  <button class="save-btn" onclick="loadNewsPage(document.getElementById('newsSymIn').value.toUpperCase())">Search</button>
</div>

<div id="newsPageContent"><div class="loading-box"><div class="spinner"></div> Loading news…</div></div>
