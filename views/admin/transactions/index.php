<?php
$page       = max(1,(int)($_GET['page']??1));
$limit      = 25;
$offset     = ($page-1)*$limit;
$search     = trim($_GET['q']??'');
$status     = $_GET['status']??'';
$channel    = $_GET['channel']??'';
$merchantId = $_GET['merchant']??'';
$dateFrom   = $_GET['date_from']??'';
$dateTo     = $_GET['date_to']??'';

$where  = "WHERE 1=1";
$params = [];
if ($search)     { $where .= " AND (t.reference LIKE ? OR u.business_name LIKE ? OR t.phone LIKE ?)"; $like="%$search%"; $params=array_merge($params,[$like,$like,$like]); }
if ($status)     { $where .= " AND t.status = ?";          $params[] = $status; }
if ($channel)    { $where .= " AND t.channel = ?";         $params[] = $channel; }
if ($merchantId) { $where .= " AND t.user_id = ?";         $params[] = $merchantId; }
if ($dateFrom)   { $where .= " AND DATE(t.created_at) >= ?"; $params[] = $dateFrom; }
if ($dateTo)     { $where .= " AND DATE(t.created_at) <= ?"; $params[] = $dateTo; }

$totalRow = DB::fetch("SELECT COUNT(*) as c FROM transactions t JOIN users u ON t.user_id=u.id $where", $params);
$total    = $totalRow['c'] ?? 0;
$pages    = (int)ceil($total / $limit);
$txns     = DB::fetchAll(
    "SELECT t.*, u.business_name FROM transactions t JOIN users u ON t.user_id=u.id
     $where ORDER BY t.created_at DESC LIMIT ? OFFSET ?",
    array_merge($params,[$limit,$offset])
);

$summary = DB::fetch(
    "SELECT COALESCE(SUM(t.amount),0) as vol, COALESCE(SUM(t.fee),0) as fees, COUNT(*) as cnt
     FROM transactions t JOIN users u ON t.user_id=u.id $where",
    $params
);
?>

<div class="section-hd">
  <div>
    <h2>All Transactions</h2>
    <p>System-wide view of all <?= number_format($total) ?> transaction<?= $total!==1?'s':'' ?></p>
  </div>
  <a href="?<?= http_build_query(array_filter(compact('search','status','channel','merchantId','dateFrom','dateTo'))) ?>&export=csv"
     class="btn btn-outline btn-sm">
    <i class="fas fa-download"></i> Export CSV
  </a>
</div>

<!-- Summary -->
<div class="admin-stats" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
  <div class="admin-stat green">
    <div class="admin-stat-icon green"><i class="fas fa-coins"></i></div>
    <div><div class="admin-stat-val"><?= format_amount($summary['vol']) ?></div><div class="admin-stat-lbl">Total Volume</div></div>
  </div>
  <div class="admin-stat orange">
    <div class="admin-stat-icon orange"><i class="fas fa-percentage"></i></div>
    <div><div class="admin-stat-val"><?= format_amount($summary['fees']) ?></div><div class="admin-stat-lbl">Total Fees</div></div>
  </div>
  <div class="admin-stat navy">
    <div class="admin-stat-icon navy"><i class="fas fa-hashtag"></i></div>
    <div><div class="admin-stat-val"><?= number_format($summary['cnt']) ?></div><div class="admin-stat-lbl">Count</div></div>
  </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px">
  <div class="card-body" style="padding:14px 18px">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
      <div style="flex:1;min-width:180px">
        <label class="form-label" style="font-size:.78rem">Search</label>
        <input type="text" class="form-control" name="q" placeholder="Reference, merchant, phone..." value="<?= sanitize($search) ?>">
      </div>
      <div>
        <label class="form-label" style="font-size:.78rem">Status</label>
        <select name="status" class="form-control form-select" style="min-width:130px">
          <option value="">All</option>
          <?php foreach(['completed','pending','failed','processing','reversed'] as $s): ?>
            <option value="<?=$s?>" <?=$status===$s?'selected':''?>><?=ucfirst($s)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label" style="font-size:.78rem">Channel</label>
        <select name="channel" class="form-control form-select" style="min-width:130px">
          <option value="">All</option>
          <?php foreach(['mpesa','card','wallet','payment_link','bank'] as $ch): ?>
            <option value="<?=$ch?>" <?=$channel===$ch?'selected':''?>><?=ucfirst(str_replace('_',' ',$ch))?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label" style="font-size:.78rem">Date From</label>
        <input type="date" class="form-control" name="date_from" value="<?=sanitize($dateFrom)?>" style="min-width:130px">
      </div>
      <div>
        <label class="form-label" style="font-size:.78rem">Date To</label>
        <input type="date" class="form-control" name="date_to" value="<?=sanitize($dateTo)?>" style="min-width:130px">
      </div>
      <div style="display:flex;gap:8px">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
        <a href="<?= APP_URL ?>/admin/transactions" class="btn btn-ghost btn-sm">Clear</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="p-0">
    <?php if (empty($txns)): ?>
      <div class="empty-state"><i class="fas fa-search"></i><h4>No transactions found</h4></div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="orb-table">
          <thead>
            <tr>
              <th>Reference</th>
              <th>Merchant</th>
              <th>Channel</th>
              <th>Phone / Card</th>
              <th>Amount</th>
              <th>Fee</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($txns as $t): ?>
            <tr>
              <td><code style="font-size:.78rem"><?= sanitize($t['reference']) ?></code></td>
              <td>
                <a href="<?= APP_URL ?>/admin/merchants/<?= urlencode($t['user_id']) ?>" style="color:var(--green);font-weight:600;font-size:.84rem">
                  <?= sanitize($t['business_name']) ?>
                </a>
              </td>
              <td>
                <?php $icons=['mpesa'=>'mobile-alt','card'=>'credit-card','wallet'=>'wallet','payment_link'=>'link','bank'=>'university']; ?>
                <span class="chip <?= in_array($t['channel'],['mpesa','wallet','payment_link'])?'green':'navy' ?>">
                  <i class="fas fa-<?= $icons[$t['channel']]??'exchange-alt' ?>"></i>
                  <?= ucfirst(str_replace('_',' ',$t['channel'])) ?>
                </span>
              </td>
              <td style="font-size:.82rem;color:var(--text-muted)">
                <?= $t['phone']?mask_phone($t['phone']):($t['card_last4']?'**** '.$t['card_last4']:'—') ?>
              </td>
              <td style="font-weight:700"><?= format_amount($t['amount'],$t['currency']) ?></td>
              <td style="font-size:.82rem;color:var(--text-muted)"><?= format_amount($t['fee'],$t['currency']) ?></td>
              <td><?= transaction_status_badge($t['status']) ?></td>
              <td style="font-size:.78rem;color:var(--text-muted);white-space:nowrap">
                <?= date('d M Y',strtotime($t['created_at'])) ?><br>
                <span style="font-size:.72rem"><?= date('H:i',strtotime($t['created_at'])) ?></span>
              </td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-ghost btn-sm" data-toggle="dropdown" style="padding:4px 8px">
                    <i class="fas fa-ellipsis-v"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a href="#" class="dropdown-item" data-copy="<?= sanitize($t['reference']) ?>">
                      <i class="fas fa-copy"></i> Copy Reference
                    </a>
                    <?php if ($t['status'] === 'pending'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/transactions/mark-complete">
                      <?= csrf_field() ?>
                      <input type="hidden" name="reference" value="<?= sanitize($t['reference']) ?>">
                      <button type="submit" class="dropdown-item" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;color:var(--success)">
                        <i class="fas fa-check"></i> Mark Completed
                      </button>
                    </form>
                    <form method="POST" action="<?= APP_URL ?>/admin/transactions/mark-failed">
                      <?= csrf_field() ?>
                      <input type="hidden" name="reference" value="<?= sanitize($t['reference']) ?>">
                      <button type="submit" class="dropdown-item danger" style="width:100%;text-align:left;background:none;border:none;cursor:pointer">
                        <i class="fas fa-times"></i> Mark Failed
                      </button>
                    </form>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($pages > 1): ?>
      <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:.82rem;color:var(--text-muted)">
          Showing <?= $offset+1 ?>–<?= min($offset+$limit,$total) ?> of <?= number_format($total) ?>
        </span>
        <div class="pagination">
          <?php
          $base = APP_URL.'/admin/transactions?'.http_build_query(array_filter(compact('search','status','channel','merchantId','dateFrom','dateTo')));
          for ($i=max(1,$page-2); $i<=min($pages,$page+2); $i++):
          ?>
            <a href="<?=$base?>&page=<?=$i?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a>
          <?php endfor; ?>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
