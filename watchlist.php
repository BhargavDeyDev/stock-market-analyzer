<?php
// pages/watchlist.php
// This file is included inside the 3-col layout
?>
<div class="page-title">My Watchlist</div>
<div class="page-sub">Your saved symbols — prices update automatically.</div>
<?php if(!isLoggedIn()): ?>
  <div class="page-card">
    <div class="empty-state">
      <p>Please <a href="#" onclick="showModal('signinModal')" style="color:var(--g)">sign in</a> to manage your watchlist.</p>
    </div>
  </div>
<?php else: ?>
<div class="page-card">
  <div class="page-card-title">
    Saved Symbols
    <button class="nbtn nbtn-p" style="font-size:.68rem;padding:.32rem .8rem" onclick="showModal('addWlModal')">+ Add Symbol</button>
  </div>
  <div id="wlContent"><div class="loading-box"><div class="spinner"></div> Loading watchlist…</div></div>
</div>
<?php endif; ?>
