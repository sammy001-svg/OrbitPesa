<?php $fees = DB::fetchAll("SELECT * FROM fee_config ORDER BY channel"); ?>

<div class="section-hd">
  <div>
    <h2>Fee Configuration</h2>
    <p>Set transaction fees for each payment channel. Changes apply to all new transactions immediately.</p>
  </div>
</div>

<div class="alert alert-info mb-6">
  <i class="fas fa-info-circle"></i>
  <div>
    <strong>Fee calculation:</strong> For <em>combined</em> type, fee = max(flat_fee, percentage × amount), capped at max_fee.
    For <em>percentage</em> only, fee = percentage × amount. For <em>flat</em>, fee = flat_fee always.
  </div>
</div>

<form method="POST" action="<?= APP_URL ?>/admin/fees/update">
  <?= csrf_field() ?>
  <div class="card">
    <div class="card-header">
      <h4><i class="fas fa-percentage" style="color:var(--green);margin-right:6px"></i> Channel Fees</h4>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Save All</button>
    </div>
    <div class="card-body">
      <?php
      $channelMeta = [
        'mpesa'        => ['icon'=>'mobile-alt',  'label'=>'M-Pesa STK Push', 'color'=>'green'],
        'card'         => ['icon'=>'credit-card', 'label'=>'Card Payment',    'color'=>'navy'],
        'wallet'       => ['icon'=>'wallet',      'label'=>'Wallet Payment',  'color'=>'green'],
        'payment_link' => ['icon'=>'link',        'label'=>'Payment Link',    'color'=>'navy'],
        'bank'         => ['icon'=>'university',  'label'=>'Bank Transfer',   'color'=>'navy'],
      ];
      foreach ($fees as $fee):
        $meta = $channelMeta[$fee['channel']] ?? ['icon'=>'circle','label'=>ucfirst($fee['channel']),'color'=>'navy'];
      ?>
      <div class="fee-row">
        <div class="fee-channel">
          <div style="display:flex;align-items:center;gap:8px">
            <div style="width:32px;height:32px;background:<?=$meta['color']==='green'?'var(--green-light)':'var(--navy-lighter)'?>;border-radius:7px;display:flex;align-items:center;justify-content:center;color:<?=$meta['color']==='green'?'var(--green)':'var(--navy)'?>">
              <i class="fas fa-<?= $meta['icon'] ?>"></i>
            </div>
            <div>
              <div style="font-weight:700;font-size:.84rem;color:var(--navy)"><?= $meta['label'] ?></div>
              <div style="font-size:.72rem;color:var(--text-muted)"><?= $fee['channel'] ?></div>
            </div>
          </div>
        </div>

        <div class="fee-inputs">
          <div>
            <label class="form-label" style="font-size:.75rem">Fee Type</label>
            <select name="fees[<?= $fee['channel'] ?>][fee_type]" class="form-control form-select" style="width:130px">
              <?php foreach(['flat','percentage','combined'] as $ft): ?>
                <option value="<?=$ft?>" <?=$fee['fee_type']===$ft?'selected':''?>><?=ucfirst($ft)?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="form-label" style="font-size:.75rem">Flat Fee (KES)</label>
            <input type="number" class="form-control" name="fees[<?= $fee['channel'] ?>][flat_fee]"
                   value="<?= $fee['flat_fee'] ?>" min="0" step="0.01" style="width:100px">
          </div>
          <div>
            <label class="form-label" style="font-size:.75rem">Percentage (%)</label>
            <input type="number" class="form-control" name="fees[<?= $fee['channel'] ?>][percentage]"
                   value="<?= round($fee['percentage']*100, 3) ?>" min="0" max="100" step="0.001" style="width:100px">
            <div class="form-hint" style="font-size:.7rem">e.g. 1.5 = 1.5%</div>
          </div>
          <div>
            <label class="form-label" style="font-size:.75rem">Min Fee (KES)</label>
            <input type="number" class="form-control" name="fees[<?= $fee['channel'] ?>][min_fee]"
                   value="<?= $fee['min_fee'] ?>" min="0" step="0.01" style="width:90px">
          </div>
          <div>
            <label class="form-label" style="font-size:.75rem">Max Fee (KES)</label>
            <input type="number" class="form-control" name="fees[<?= $fee['channel'] ?>][max_fee]"
                   value="<?= $fee['max_fee'] ?? '' ?>" min="0" step="0.01" placeholder="No limit" style="width:90px">
          </div>
          <div style="display:flex;align-items:flex-end;padding-bottom:4px">
            <label class="toggle-wrap">
              <div class="toggle <?= $fee['is_active']?'on':'' ?>"></div>
              <input type="hidden" name="fees[<?= $fee['channel'] ?>][is_active]" value="<?= $fee['is_active']?'1':'0' ?>">
              <span style="font-size:.8rem;color:var(--text-muted)"><?= $fee['is_active']?'Active':'Disabled' ?></span>
            </label>
          </div>
        </div>

        <!-- Preview -->
        <div style="min-width:140px;background:var(--bg);border-radius:var(--radius);padding:10px;font-size:.78rem">
          <div style="color:var(--text-muted);margin-bottom:4px">Example (KES 1,000)</div>
          <?php
          $pct     = (float)$fee['percentage'];
          $flat    = (float)$fee['flat_fee'];
          $min     = (float)$fee['min_fee'];
          $max     = $fee['max_fee'] ? (float)$fee['max_fee'] : PHP_INT_MAX;
          $example = 1000;
          switch ($fee['fee_type']) {
            case 'flat':       $f = $flat; break;
            case 'percentage': $f = max($min, $example * $pct); $f = min($f,$max); break;
            default:           $f = max($min, $flat, $example * $pct); $f = min($f,$max);
          }
          ?>
          <div style="font-weight:700;color:var(--navy)">Fee: <?= format_amount($f) ?></div>
          <div style="color:var(--green)">Net: <?= format_amount($example - $f) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="card-footer" style="display:flex;justify-content:flex-end;gap:10px">
      <a href="<?= APP_URL ?>/admin/fees" class="btn btn-ghost btn-sm">Reset</a>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Fee Configuration</button>
    </div>
  </div>
</form>
