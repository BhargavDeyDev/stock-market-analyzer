<?php
// pages/settings.php
if(!isLoggedIn()) { echo '<div class="empty-state"><p>Please sign in to access settings.</p></div>'; return; }
?>
<div class="page-title">Settings</div>
<div class="page-sub">Configure your Stockry preferences.</div>
<div class="page-card">
  <div class="form-section">
    <h3>Display Preferences</h3>
    <label class="inp-label">Default Currency</label>
    <select class="inp" style="margin-bottom:.6rem">
      <option selected>USD — US Dollar</option>
      <option>EUR — Euro</option>
      <option>GBP — British Pound</option>
      <option>INR — Indian Rupee</option>
    </select>
    <label class="inp-label">Default Chart Timeframe</label>
    <select class="inp" style="margin-bottom:.6rem">
      <option>1D — 1 Day</option>
      <option>1W — 1 Week</option>
      <option selected>1M — 1 Month</option>
    </select>
  </div>
  <div class="form-section">
    <h3>Notification Preferences</h3>
    <label style="display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:var(--t2);margin-bottom:.6rem;cursor:pointer">
      <input type="checkbox" checked style="accent-color:var(--g)"/> Email alerts for BUY signals
    </label>
    <label style="display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:var(--t2);margin-bottom:.6rem;cursor:pointer">
      <input type="checkbox" checked style="accent-color:var(--g)"/> Watchlist price alerts
    </label>
    <label style="display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:var(--t2);margin-bottom:.6rem;cursor:pointer">
      <input type="checkbox" style="accent-color:var(--g)"/> Weekly market summary
    </label>
  </div>
  <button class="save-btn" onclick="showToast('Settings saved!','success')">Save Settings</button>
</div>
<div class="page-card" style="margin-top:.8rem;border-color:rgba(255,64,96,.2)">
  <div class="form-section">
    <h3 style="color:var(--r)">Danger Zone</h3>
    <p style="font-size:.75rem;color:var(--t2);margin-bottom:.8rem">Deleting your account is permanent and cannot be undone.</p>
    <button style="padding:.5rem 1.2rem;border-radius:8px;background:transparent;border:1px solid rgba(255,64,96,.4);color:var(--r);font-size:.75rem;font-weight:700;cursor:pointer;" onclick="if(confirm('Are you sure? This cannot be undone.'))showToast('Account deletion requested. Our team will process this.','error')">Delete My Account</button>
  </div>
</div>
