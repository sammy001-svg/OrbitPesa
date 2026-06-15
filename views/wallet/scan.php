<?php
$pageTitle = 'Scan QR Code';
$backUrl   = APP_URL . '/wallet/receive';

?>

<!-- jsQR for decoding camera frames -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.4.0/jsQR.min.js"></script>

<div style="padding-top:6px">

  <!-- Camera viewfinder -->
  <div id="scannerWrap" style="position:relative;background:#0D1B3E;border-radius:20px;overflow:hidden;aspect-ratio:1;margin-bottom:14px">
    <video id="camVideo" style="width:100%;height:100%;object-fit:cover" playsinline muted></video>
    <canvas id="camCanvas" style="display:none"></canvas>

    <!-- Corner guides -->
    <div style="position:absolute;inset:0;pointer-events:none">
      <?php foreach (['top:16px;left:16px;border-top:3px solid #fff;border-left:3px solid #fff;border-radius:6px 0 0 0',
                      'top:16px;right:16px;border-top:3px solid #fff;border-right:3px solid #fff;border-radius:0 6px 0 0',
                      'bottom:16px;left:16px;border-bottom:3px solid #fff;border-left:3px solid #fff;border-radius:0 0 0 6px',
                      'bottom:16px;right:16px;border-bottom:3px solid #fff;border-right:3px solid #fff;border-radius:0 0 6px 0'] as $s): ?>
      <div style="position:absolute;width:28px;height:28px;<?= $s ?>"></div>
      <?php endforeach; ?>
      <div style="position:absolute;inset-x:0;top:50%;transform:translateY(-50%);height:2px;background:rgba(21,131,71,.7)"></div>
    </div>

    <!-- Status overlay -->
    <div id="scanStatus" style="position:absolute;bottom:0;inset-x:0;background:rgba(13,27,62,.8);color:#fff;text-align:center;padding:10px 14px;font-size:.78rem;backdrop-filter:blur(4px)">
      <i class="fas fa-camera"></i> Starting camera…
    </div>
  </div>

  <!-- Error / permission denied state -->
  <div id="camError" style="display:none;background:#fef2f2;border-radius:16px;padding:20px;text-align:center;margin-bottom:14px">
    <div style="font-size:2rem;margin-bottom:8px">📷</div>
    <div style="font-weight:600;color:#dc2626;font-size:.9rem;margin-bottom:6px">Camera not available</div>
    <div style="color:#64748b;font-size:.8rem">Allow camera access, or use the manual entry below.</div>
  </div>

  <!-- Manual entry fallback -->
  <div class="wform-card">
    <div style="font-weight:600;font-size:.88rem;color:#0D1B3E;margin-bottom:12px">
      <i class="fas fa-keyboard"></i> Enter Wallet ID manually
    </div>
    <form id="manualForm" onsubmit="submitManual(event)">
      <div class="wform-group" style="margin-bottom:12px">
        <input type="text" id="manualWid" class="wform-control"
               placeholder="e.g. OP1234567890"
               autocomplete="off" autocapitalize="characters"
               style="text-align:center;font-size:1.1rem;letter-spacing:2px;font-weight:700">
      </div>
      <button type="submit" class="wbtn wbtn-primary">
        <i class="fas fa-paper-plane"></i> Continue to Send
      </button>
    </form>
  </div>

  <div style="margin-top:12px;text-align:center;font-size:.75rem;color:#94a3b8">
    Scanning a QR code will open the Send Money screen with the recipient pre-filled.
  </div>

</div>

<script>
const APP_URL = window.APP_URL || '<?= addslashes(APP_URL) ?>';

let scanning = true;

async function startCamera() {
  const status = document.getElementById('scanStatus');
  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'environment' } }
    });
    const video  = document.getElementById('camVideo');
    video.srcObject = stream;
    await video.play();
    status.innerHTML = '<i class="fas fa-qrcode"></i> Point at a wallet QR code';
    requestAnimationFrame(tick);
  } catch (err) {
    document.getElementById('scannerWrap').style.display = 'none';
    document.getElementById('camError').style.display    = 'block';
  }
}

function tick() {
  if (!scanning) return;
  const video  = document.getElementById('camVideo');
  const canvas = document.getElementById('camCanvas');
  if (video.readyState !== video.HAVE_ENOUGH_DATA) {
    requestAnimationFrame(tick);
    return;
  }
  canvas.width  = video.videoWidth;
  canvas.height = video.videoHeight;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
  const img  = ctx.getImageData(0, 0, canvas.width, canvas.height);
  const code = jsQR(img.data, img.width, img.height, { inversionAttempts: 'dontInvert' });

  if (code) {
    scanning = false;
    handleQR(code.data);
    return;
  }
  requestAnimationFrame(tick);
}

function handleQR(raw) {
  const status = document.getElementById('scanStatus');
  status.innerHTML = '<i class="fas fa-check-circle" style="color:#22c55e"></i> QR detected — loading…';

  // Accept either a full URL containing ?wid= or a bare wallet ID (OP + 10 digits)
  let wid = null;
  try {
    const url = new URL(raw);
    wid = url.searchParams.get('wid');
  } catch (_) {}

  if (!wid) {
    // Bare wallet ID?
    if (/^OP\d{10}$/i.test(raw.trim())) wid = raw.trim().toUpperCase();
  }

  if (wid) {
    // Stop camera
    const video = document.getElementById('camVideo');
    if (video.srcObject) video.srcObject.getTracks().forEach(t => t.stop());
    window.location.href = APP_URL + '/wallet/send?wid=' + encodeURIComponent(wid);
  } else {
    status.innerHTML = '<i class="fas fa-exclamation-triangle" style="color:#f59e0b"></i> Not a valid OrbitPesa QR — try again';
    setTimeout(() => {
      scanning = true;
      status.innerHTML = '<i class="fas fa-qrcode"></i> Point at a wallet QR code';
      requestAnimationFrame(tick);
    }, 2000);
  }
}

function submitManual(e) {
  e.preventDefault();
  const wid = document.getElementById('manualWid').value.trim().toUpperCase();
  if (!/^OP\d{10}$/.test(wid)) {
    alert('Please enter a valid Wallet ID (e.g. OP1234567890).');
    return;
  }
  window.location.href = APP_URL + '/wallet/send?wid=' + encodeURIComponent(wid);
}

if (typeof jsQR !== 'undefined') {
  startCamera();
} else {
  document.getElementById('camError').style.display    = 'block';
  document.getElementById('scannerWrap').style.display = 'none';
}
</script>
