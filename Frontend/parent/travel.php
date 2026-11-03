<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
$u = require_role('parent');
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $mid = (int)post('member');
    $m = q("SELECT * FROM children WHERE id=? AND parent_id=? AND member_type='adult'", [$mid, $u['id']])->fetch();
    if ($m && post('action') === 'plan') {
        $dest = post('destination'); $date = post('travel_date'); $d = DateTime::createFromFormat('Y-m-d', $date);
        if (mb_strlen($dest) < 2) $errors[] = 'Enter the country or city you are travelling to.';
        if (!$d || $d->format('Y-m-d') !== $date || $d < new DateTime('today') || $d > new DateTime('+2 years')) $errors[] = 'Choose a travel date from today up to two years ahead.';
        if (!$errors) { q('INSERT INTO travel_plans(member_id,destination,travel_date) VALUES(?,?,?)', [$mid, mb_substr($dest, 0, 100), $date]); flash('Trip added.'); redirect('parent/travel.php?id=' . $mid); }
    } elseif ($m && post('action') === 'drop') {
        q('DELETE FROM travel_plans WHERE id=? AND member_id=?', [(int)post('plan'), $mid]); flash('Trip removed.'); redirect('parent/travel.php?id=' . $mid);
    }
}
$adults = q("SELECT * FROM children WHERE parent_id=? AND member_type='adult' ORDER BY name", [$u['id']])->fetchAll();
$cid = (int)($_GET['id'] ?? $_POST['member'] ?? ($adults[0]['id'] ?? 0));
$c = null; foreach ($adults as $a) if ((int)$a['id'] === $cid) $c = $a;
page_start('Travel clearance', 'parent');
if (!$adults) { echo '<div class="card">'; empty_state('Add an adult to your family to check travel clearance.'); echo '<a class="btn btn-p" href="children.php">Add an adult</a></div>'; page_end(); exit; }
if (!$c) $c = $adults[0];
$t = travel_status((int)$c['id']);
$pct = $t['total'] ? (int)round($t['done'] / $t['total'] * 100) : 100;
$plans = q('SELECT * FROM travel_plans WHERE member_id=? ORDER BY travel_date', [$c['id']])->fetchAll();
?>
<?php if (count($adults) > 1): ?><form class="tools noprint" method="get"><?php field('Adult', 'id', 'select', ['value' => $c['id'], 'options' => array_column($adults, 'name', 'id')]); ?><button class="btn btn-g">Show</button></form><?php endif; ?>
<?php foreach ($errors as $er): ?><div class="alert bad"><?= e($er) ?></div><?php endforeach; ?>
<div class="tpass <?= $t['cleared'] ? 'go' : 'stop' ?>">
  <div class="ring"><svg viewBox="0 0 120 120"><defs><linearGradient id="rg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#19c4b4"/><stop offset="1" stop-color="#43e0b0"/></linearGradient></defs><circle class="trk" cx="60" cy="60" r="52"/><circle class="val" cx="60" cy="60" r="52" style="--p:<?= $pct ?>"/></svg><b><?= $t['done'] ?>/<?= $t['total'] ?></b></div>
  <div>
    <div class="verdict"><i class="bi <?= $t['cleared'] ? 'bi-patch-check-fill' : 'bi-slash-circle-fill' ?>"></i><?= $t['cleared'] ? 'Cleared to travel abroad' : 'Not cleared to travel yet' ?></div>
    <p class="muted"><?= e($c['name']) ?> · <?= $t['cleared'] ? 'all required travel vaccines are on record.' : 'you cannot travel abroad until the required vaccines below are given.' ?></p>
    <ul class="vlist">
      <?php foreach ($t['required'] as $r): $ok = $r['status'] === 'vaccinated'; ?>
      <li class="<?= $ok ? 'done' : '' ?>"><div class="tick"><i class="bi <?= $ok ? 'bi-check' : 'bi-x' ?>"></i></div><span><?= e($r['name']) ?><br><small><?= $ok ? 'Given ' . fmt_date($r['given_on']) : e($r['description']) ?></small></span>
        <?= $ok ? badge('vaccinated') : ($r['status'] === 'booked' ? badge('booked') : '<a class="btn btn-p btn-sm" href="book.php?schedule=' . (int)$r['sid'] . '">Book now</a>') ?></li>
      <?php endforeach; ?>
      <?php if (!$t['required']): ?><li><span>No travel vaccines are required right now.</span></li><?php endif; ?>
    </ul>
    <div class="actions noprint" style="margin-top:1rem">
      <?php if ($t['cleared']): ?><button type="button" class="btn btn-s" onclick="print()"><i class="bi bi-printer"></i>Print travel certificate</button>
      <?php else: ?><span class="btn btn-g off"><i class="bi bi-lock"></i>Travel certificate locked</span><?php endif; ?>
    </div>
  </div>
</div>

<?php if ($t['cleared']): ?>
<div class="cert" style="margin-bottom:22px"><h2><?= APP_NAME ?> travel vaccination certificate</h2><p class="sub">Generated on <?= date('d M Y') ?></p>
  <p><b><?= e($c['name']) ?></b> · <?= e($c['gender']) ?> · Born <?= fmt_date($c['dob']) ?><?= $c['passport_no'] ? ' · Passport ' . e($c['passport_no']) : '' ?></p>
  <div class="tbl"><table><tr><th>Required vaccine</th><th>Given on</th></tr>
  <?php foreach ($t['required'] as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= fmt_date($r['given_on']) ?></td></tr><?php endforeach; ?></table></div>
  <p style="margin-top:1rem"><span class="stamp"><i class="bi bi-patch-check-fill"></i>Cleared to travel</span></p>
</div>
<?php endif; ?>

<div class="cols">
  <div class="card"><h2><span class="plane"><i class="bi bi-airplane"></i></span> My trips</h2>
    <?php if (!$plans): empty_state('No trips added yet.'); else: ?><div class="tbl"><table><tr><th>Destination</th><th>Date</th><th>Status</th><th></th></tr>
    <?php foreach ($plans as $pl): $days = (int)((strtotime($pl['travel_date']) - strtotime('today')) / 86400); ?>
      <tr><td><b><?= e($pl['destination']) ?></b></td><td><?= fmt_date($pl['travel_date']) ?><br><small><?= $days < 0 ? 'Past' : ($days === 0 ? 'Today' : 'In ' . $days . ' days') ?></small></td>
        <td><?= $t['cleared'] ? badge('cleared') : badge('not_cleared') ?></td>
        <td><form method="post" class="inline" data-confirm="Remove this trip?"><?= csrf_field() ?><input type="hidden" name="action" value="drop"><input type="hidden" name="member" value="<?= (int)$c['id'] ?>"><input type="hidden" name="plan" value="<?= (int)$pl['id'] ?>"><button class="btn btn-g btn-sm">Remove</button></form></td></tr>
    <?php endforeach; ?></table></div><?php endif; ?>
    <?php if (!$t['cleared'] && $plans): ?><div class="hint"><i class="bi bi-exclamation-triangle"></i> Missing: <?= e(implode(', ', $t['missing'])) ?>. Book these before you fly.</div><?php endif; ?>
  </div>
  <div class="card"><h2>Add a trip</h2><form method="post" class="fgrid"><?= csrf_field() ?><input type="hidden" name="action" value="plan"><input type="hidden" name="member" value="<?= (int)$c['id'] ?>">
    <?php field('Destination', 'destination', 'text', ['attr' => 'maxlength="100" placeholder="e.g. Dubai, UAE"']); field('Travel date', 'travel_date', 'date', ['attr' => 'min="' . date('Y-m-d') . '"']); ?>
    <button class="btn btn-p wide">Add trip</button></form></div>
</div>

<?php $rec = $t['recommended']; if ($rec): ?>
<div class="card"><h2>Also recommended for travellers</h2><div class="tbl"><table><tr><th>Vaccine</th><th>Status</th><th></th></tr>
<?php foreach ($rec as $r): ?><tr><td><b><?= e($r['name']) ?></b><br><small><?= e($r['description']) ?></small></td><td><?= badge($r['status']) ?></td>
  <td><?= $r['status'] === 'pending' ? '<a class="btn btn-g btn-sm" href="book.php?schedule=' . (int)$r['sid'] . '">Book</a>' : '' ?></td></tr><?php endforeach; ?></table></div></div>
<?php endif; page_end();
