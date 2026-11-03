<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
require_role('hospital');
$h = my_hospital();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ((array)($_POST['stock'] ?? []) as $vid => $qty) {
        $price = filter_var($_POST['price'][$vid] ?? null, FILTER_VALIDATE_FLOAT);
        if ($price === false || $price < 0 || $price > 10000000) continue;
        q('INSERT INTO hospital_vaccines(hospital_id,vaccine_id,stock,price_pkr) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE stock=VALUES(stock),price_pkr=VALUES(price_pkr)', [$h['id'], (int)$vid, max(0, min(100000, (int)$qty)), $price]);
    }
    flash('Vaccine stock saved.'); redirect('hospital/stock.php');
}
$rows = q('SELECT v.id,v.name,v.audience,v.available,COALESCE(hv.stock,0) stock,COALESCE(hv.price_pkr,0) price_pkr FROM vaccines v LEFT JOIN hospital_vaccines hv ON hv.vaccine_id=v.id AND hv.hospital_id=? ORDER BY v.audience,v.age_days,v.id', [$h['id']])->fetchAll();
page_start('Vaccine stock', 'hospital'); ?>
<div class="card"><p class="muted">Keep stock and PKR prices current. These figures are supplied by your hospital; VaxCare does not receive a live feed from other hospitals. Stock drops by one each time you mark a vaccination complete.</p>
<form method="post"><div class="tbl"><table><tr><th>Vaccine</th><th>For</th><th>Availability</th><th style="width:140px">Doses in stock</th><th style="width:160px">Price (PKR)</th></tr>
<?php foreach ($rows as $r): ?><tr><td><b><?= e($r['name']) ?></b></td><td><?= type_badge($r['audience']) ?></td><td><?= badge($r['available'] && $r['stock'] > 0 ? 'available' : 'unavailable') ?></td>
  <td><input type="number" min="0" name="stock[<?= $r['id'] ?>]" value="<?= (int)$r['stock'] ?>" aria-label="Stock for <?= e($r['name']) ?>"></td><td><input type="number" min="0" step="0.01" name="price[<?= $r['id'] ?>]" value="<?= e($r['price_pkr']) ?>" aria-label="Price in PKR for <?= e($r['name']) ?>"></td></tr><?php endforeach; ?></table></div>
<?= csrf_field() ?><p style="margin-top:1rem"><button class="btn btn-p">Save stock</button></p></form></div>
<?php page_end();
