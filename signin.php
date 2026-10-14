<?php
// pages/signin.php — just shows modal
if(isLoggedIn()) { header('Location: '.SITE_URL.'/?page=markets'); exit; }
?>
<div style="display:flex;align-items:center;justify-content:center;min-height:60vh">
  <div style="text-align:center">
    <div style="font-size:.85rem;color:var(--t2)">Opening sign in…</div>
  </div>
</div>
<script>setTimeout(()=>showModal('signinModal'),100);</script>
