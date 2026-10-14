<?php
// pages/profile.php
if(!isLoggedIn()) { echo '<div class="empty-state"><p>Please <a href="#" onclick="showModal(\'signinModal\')" style="color:var(--g)">sign in</a> to view your profile.</p></div>'; return; }
$user = currentUser();
?>
<div class="page-title">My Profile</div>
<div class="page-sub">Manage your account details and preferences.</div>

<div class="settings-grid">
  <div class="page-card" style="height:fit-content">
    <div style="text-align:center;padding:.5rem 0 1rem">
      <div style="width:64px;height:64px;border-radius:50%;background:var(--g);color:#04120a;display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:800;margin:0 auto .8rem"><?= strtoupper(substr($user['name'],0,1)) ?></div>
      <div style="font-size:.9rem;font-weight:700"><?= clean($user['name']) ?></div>
      <div style="font-size:.72rem;color:var(--t2);margin-top:.2rem"><?= clean($user['email']) ?></div>
    </div>
    <div class="settings-nav">
      <a href="/?page=profile" class="active">👤 Profile</a>
      <a href="/?page=predictions">📊 My Predictions</a>
      <a href="/?page=watchlist">⭐ Watchlist</a>
      <a href="/?page=settings">⚙ Settings</a>
    </div>
  </div>
  <div>
    <div class="page-card">
      <div class="form-section">
        <h3>Account Information</h3>
        <label class="inp-label">Full Name</label>
        <input class="inp" type="text" value="<?= clean($user['name']) ?>" id="profName"/>
        <label class="inp-label">Email Address</label>
        <input class="inp" type="email" value="<?= clean($user['email']) ?>" id="profEmail" readonly style="opacity:.6"/>
        <div style="font-size:.62rem;color:var(--t3);margin-top:-.4rem;margin-bottom:.6rem">Email cannot be changed.</div>
        <button class="save-btn" onclick="saveProfile()">Save Changes</button>
      </div>
    </div>
    <div class="page-card" style="margin-top:.8rem">
      <div class="form-section">
        <h3>Change Password</h3>
        <label class="inp-label">Current Password</label>
        <input class="inp" type="password" id="profOldPass" placeholder="Current password"/>
        <label class="inp-label">New Password</label>
        <input class="inp" type="password" id="profNewPass" placeholder="New password (min 8 chars)"/>
        <label class="inp-label">Confirm New Password</label>
        <input class="inp" type="password" id="profNewPass2" placeholder="Repeat new password"/>
        <button class="save-btn" onclick="changePassword()">Update Password</button>
      </div>
    </div>
  </div>
</div>
