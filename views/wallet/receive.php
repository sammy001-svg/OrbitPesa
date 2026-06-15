<?php
$pageTitle = 'Receive Money';
$backUrl   = APP_URL . '/wallet/home';
?>
<div style="padding-top:16px">

  <div class="receive-card">
    <div style="font-size:.75rem;color:#94a3b8;font-weight:600;margin-bottom:16px;text-transform:uppercase;letter-spacing:.5px">Your OrbitPesa Wallet ID</div>

    <div class="receive-qr">
      <div class="receive-qr-inner">
        <i class="fas fa-qrcode"></i>
        <span>QR Code</span>
      </div>
    </div>

    <div class="receive-id"><?= htmlspecialchars($walletUser['wallet_id']) ?></div>
    <div class="receive-id-label">Share this ID to receive money from anyone</div>

    <button class="copy-btn" id="copyBtn" onclick="copyId()">
      <i class="fas fa-copy"></i> Copy Wallet ID
    </button>

    <div style="margin-top:24px;padding-top:20px;border-top:1px solid #f1f5f9">
      <div style="font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px">Also share via</div>
      <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
        <a href="https://wa.me/?text=<?= urlencode('Send me money on OrbitPesa Wallet. My wallet ID: ' . $walletUser['wallet_id']) ?>"
           target="_blank" class="copy-btn" style="text-decoration:none;color:#25d366;border-color:#25d366">
          <i class="fab fa-whatsapp"></i> WhatsApp
        </a>
        <a href="sms:?body=<?= urlencode('My OrbitPesa wallet ID: ' . $walletUser['wallet_id']) ?>"
           class="copy-btn" style="text-decoration:none;color:#3b82f6;border-color:#3b82f6">
          <i class="fas fa-sms"></i> SMS
        </a>
      </div>
    </div>
  </div>

  <div class="wform-card" style="margin-top:14px">
    <div style="font-size:.82rem;font-weight:700;color:#0f172a;margin-bottom:14px">How to receive money</div>
    <div style="display:flex;flex-direction:column;gap:12px">
      <div style="display:flex;gap:12px;align-items:flex-start">
        <div style="width:28px;height:28px;border-radius:50%;background:#dcfce7;color:#158347;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;flex-shrink:0">1</div>
        <div style="font-size:.82rem;color:#64748b">Share your Wallet ID <strong style="color:#0f172a"><?= htmlspecialchars($walletUser['wallet_id']) ?></strong> with the sender</div>
      </div>
      <div style="display:flex;gap:12px;align-items:flex-start">
        <div style="width:28px;height:28px;border-radius:50%;background:#dcfce7;color:#158347;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;flex-shrink:0">2</div>
        <div style="font-size:.82rem;color:#64748b">They enter your ID in their Send Money screen</div>
      </div>
      <div style="display:flex;gap:12px;align-items:flex-start">
        <div style="width:28px;height:28px;border-radius:50%;background:#dcfce7;color:#158347;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;flex-shrink:0">3</div>
        <div style="font-size:.82rem;color:#64748b">Money arrives in your wallet instantly — <strong style="color:#158347">no fees</strong></div>
      </div>
    </div>
  </div>

</div>

<script>
function copyId() {
  const id  = '<?= htmlspecialchars($walletUser['wallet_id']) ?>';
  const btn = document.getElementById('copyBtn');
  navigator.clipboard.writeText(id).then(() => {
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    btn.classList.add('copied');
    setTimeout(() => {
      btn.innerHTML = '<i class="fas fa-copy"></i> Copy Wallet ID';
      btn.classList.remove('copied');
    }, 2500);
  }).catch(() => {
    // Fallback
    const ta = document.createElement('textarea');
    ta.value = id;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    btn.classList.add('copied');
    setTimeout(() => {
      btn.innerHTML = '<i class="fas fa-copy"></i> Copy Wallet ID';
      btn.classList.remove('copied');
    }, 2500);
  });
}
</script>
