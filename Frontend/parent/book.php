<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
$u = require_role('parent');
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $sid = (int)post('schedule'); $hid = (int)post('hospital_id'); $date = post('appt_date'); $slot = post('slot');
    $s = q("SELECT s.*,c.name child,v.name vaccine FROM schedule s JOIN children c ON c.id=s.child_id JOIN vaccines v ON v.id=s.vaccine_id
        WHERE s.id=? AND c.parent_id=? AND s.status='pending' AND v.available=1", [$sid, $u['id']])->fetch();
    if (!$s) $errors[] = 'Choose a vaccine that is due and available.';
    $h = $s ? q("SELECT h.* FROM hospitals h JOIN hospital_vaccines hv ON hv.hospital_id=h.id WHERE h.id=? AND h.status='approved' AND hv.vaccine_id=? AND hv.stock>0", [$hid, $s['vaccine_id']])->fetch() : null;
    if ($s && !$h) $errors[] = 'Choose a hospital that has this vaccine in stock.';
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date || $d < new DateTime('today') || $d > new DateTime('+6 months')) $errors[] = 'Choose a date from today up to six months ahead.';
    if (!in_array($slot, SLOTS, true)) $errors[] = 'Choose a time slot.';
    if (!$errors && (int)q("SELECT COUNT(*) FROM appointments WHERE hospital_id=? AND appt_date=? AND slot=? AND admin_status<>'rejected'", [$hid, $date, $slot])->fetchColumn() >= SLOT_LIMIT)
        $errors[] = 'That time slot is full. Choose another slot or date.';
    if (!$errors) {
        $pdo = db(); $pdo->beginTransaction();
        q('INSERT INTO appointments(schedule_id,child_id,hospital_id,vaccine_id,appt_date,slot) VALUES(?,?,?,?,?,?)', [$sid, $s['child_id'], $hid, $s['vaccine_id'], $date, $slot]);
        q("UPDATE schedule SET status='booked' WHERE id=?", [$sid]);
        $pdo->commit();
        notify($u['id'], "Request sent: {$s['child']} · {$s['vaccine']} at {$h['name']} on " . fmt_date($date) . '. Waiting for admin approval.');
        flash('Request sent. You will be notified when the admin approves it.'); redirect('parent/appointments.php');
    }
}
$sched = q("SELECT s.id,s.due_date,c.name child,v.id vid,v.name vaccine FROM schedule s JOIN children c ON c.id=s.child_id JOIN vaccines v ON v.id=s.vaccine_id
    WHERE c.parent_id=? AND s.status='pending' AND v.available=1 ORDER BY c.name,s.due_date", [$u['id']])->fetchAll();
$hosp = q("SELECT h.*,GROUP_CONCAT(CONCAT(hv.vaccine_id,':',hv.stock,':',hv.price_pkr) SEPARATOR ',') inventory FROM hospitals h LEFT JOIN hospital_vaccines hv ON hv.hospital_id=h.id AND hv.stock>0 WHERE h.status='approved' GROUP BY h.id ORDER BY h.name")->fetchAll();
$cities = array_values(array_unique(array_column($hosp, 'city'))); sort($cities);
$sel = (int)($_POST['schedule'] ?? $_GET['schedule'] ?? 0);
page_start('Book a vaccine', 'parent');
if (!$sched): ?><div class="card"><?php empty_state('No vaccines are due for booking right now.'); ?><a class="btn btn-p" href="children.php">Add a child</a></div>
<?php else: foreach ($errors as $er): ?><div class="alert bad"><?= e($er) ?></div><?php endforeach; ?>
<form method="post"><?= csrf_field() ?>
  <div class="card"><h2>1. Which vaccine?</h2><div class="f"><label for="schedule">Child and vaccine</label>
    <select id="schedule" name="schedule" required><option value="">Choose…</option>
    <?php foreach ($sched as $s): ?><option value="<?= $s['id'] ?>" data-vaccine="<?= $s['vid'] ?>" <?= $sel === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['child'] . ' · ' . $s['vaccine'] . ' (due ' . fmt_date($s['due_date']) . ')') ?></option><?php endforeach; ?></select></div></div>
  <div class="card">
    <h2>2. Which hospital?</h2>
    <div class="fgrid">
      <div class="f">
        <label for="hcity">Filter by city (optional)</label>
        <select id="hcity">
          <option value="">All cities in Pakistan</option>
          <?php foreach ($cities as $c): ?><option value="<?= e($c) ?>"><?= e($c) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="f">
        <label for="hospital_id">Hospital name</label>
        <select id="hospital_id" name="hospital_id" required>
          <option value="">Choose a hospital…</option>
          <?php foreach ($hosp as $h): ?>
            <option value="<?= $h['id'] ?>"
              data-city="<?= e($h['city']) ?>"
              data-hname="<?= e($h['name']) ?>"
              data-address="<?= e($h['address']) ?>"
              data-phone="<?= e($h['phone']) ?>"
              data-inventory="<?= e($h['inventory'] ?? '') ?>"
              <?= (int)($_POST['hospital_id'] ?? 0) === (int)$h['id'] ? 'selected' : '' ?>>
              <?= e($h['name'] . ' — ' . $h['city']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Live Selected Hospital Details Card -->
    <div id="hosp-preview" style="display:none;margin-top:1.2rem;padding:16px 20px;border-radius:14px;background:var(--tint);border:1px solid var(--glass-b)">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap">
        <div>
          <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:var(--teal);font-weight:700">Selected Hospital</div>
          <strong id="prev-name" style="font-size:1.15rem;color:var(--ink);display:block;margin-top:2px"></strong>
          <div id="prev-addr" class="muted" style="font-size:.92rem;margin-top:3px"></div>
          <div id="prev-phone" class="muted" style="font-size:.88rem;margin-top:3px"></div>
        </div>
        <div style="text-align:right">
          <div id="prev-stock-badge" class="badge ok" style="font-size:.88rem;padding:5px 12px;font-weight:600"></div>
          <div id="prev-price" class="muted" style="font-size:.82rem;margin-top:5px"></div>
        </div>
      </div>
    </div>
    <p id="hnone" class="muted" hidden style="margin-top:.8rem">No hospital in this city currently has stock for the selected vaccine. Try choosing another city or vaccine.</p>
  </div>
  <div class="card"><h2>3. When?</h2><div class="fgrid">
    <?php field('Date', 'appt_date', 'date', ['attr' => 'min="' . date('Y-m-d') . '" max="' . date('Y-m-d', strtotime('+6 months')) . '"']);
    field('Time slot', 'slot', 'select', ['options' => ['' => 'Choose…'] + array_combine(SLOTS, SLOTS)]); ?></div>
    <p class="muted">Prices and stock are entered by each hospital and may change. Time slots are configured by VaxCare; live hospital slot feeds are not connected.</p>
    <p style="margin-top:1rem"><button class="btn btn-p">Send booking request</button></p></div>
</form><?php endif; ?>
<div class="card"><h2>Find hospitals near a city</h2><p class="muted">Search the OpenStreetMap community directory. These places are not VaxCare partners and cannot be booked here. Verify the facility details directly.</p>
  <div class="tools"><div class="f"><label for="directory-city">City in Pakistan</label><input id="directory-city" maxlength="80" placeholder="e.g. Karachi"></div><button type="button" id="directory-search" class="btn btn-p">Search public directory</button></div>
  <p id="directory-status" class="muted" aria-live="polite"></p><div id="directory-results"></div><small>Map data © <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap contributors</a>.</small>
</div><?php page_end('<script>
const sched = document.querySelector("#schedule");
const hospSelect = document.querySelector("#hospital_id");
const citySelect = document.querySelector("#hcity");
const hospPreview = document.querySelector("#hosp-preview");
const prevName = document.querySelector("#prev-name");
const prevAddr = document.querySelector("#prev-addr");
const prevPhone = document.querySelector("#prev-phone");
const prevStock = document.querySelector("#prev-stock-badge");
const prevPrice = document.querySelector("#prev-price");
const hnone = document.querySelector("#hnone");

function updateHospitalPreview() {
  if (!hospSelect) return;
  const opt = hospSelect.selectedOptions[0];
  if (!opt || !opt.value) {
    if (hospPreview) hospPreview.style.display = "none";
    return;
  }
  const vaccine = sched ? (sched.selectedOptions[0]?.dataset.vaccine || "") : "";
  const row = (opt.dataset.inventory || "").split(",").map(x => x.split(":")).find(x => x[0] === vaccine);

  if (prevName) prevName.textContent = opt.dataset.hname || opt.text;
  if (prevAddr) prevAddr.textContent = (opt.dataset.address ? opt.dataset.address + ", " : "") + (opt.dataset.city || "");
  if (prevPhone) prevPhone.textContent = opt.dataset.phone ? "📞 " + opt.dataset.phone : "";

  if (row) {
    const stockQty = row[1] || "0";
    const priceVal = Number(row[2] || 0);
    if (prevStock) {
      prevStock.textContent = stockQty + " doses available";
      prevStock.className = Number(stockQty) > 0 ? "badge ok" : "badge bad";
    }
    if (prevPrice) {
      prevPrice.textContent = priceVal > 0 ? "Fee: PKR " + priceVal.toFixed(2) : "Free / Government Covered";
    }
  } else {
    if (prevStock) {
      prevStock.textContent = "Vaccine in stock";
      prevStock.className = "badge ok";
    }
    if (prevPrice) prevPrice.textContent = "";
  }
  if (hospPreview) hospPreview.style.display = "block";
}

function filterHospitals() {
  if (!sched || !hospSelect) return;
  const vaccine = sched.selectedOptions[0]?.dataset.vaccine || "";
  const city = citySelect?.value || "";
  const currentVal = hospSelect.value;
  let visibleCount = 0;
  let keepCurrent = false;

  const options = hospSelect.querySelectorAll("option");
  options.forEach(opt => {
    if (!opt.value) return; // skip placeholder
    const row = (opt.dataset.inventory || "").split(",").map(x => x.split(":")).find(x => x[0] === vaccine);
    // If vaccine is selected, ensure stock > 0; otherwise show all hospitals matching the city filter
    const hasStock = !vaccine || (!!row && +row[1] > 0);
    const matchesCity = !city || opt.dataset.city === city;
    const ok = hasStock && matchesCity;

    opt.hidden = !ok;
    opt.disabled = !ok;

    if (ok) {
      visibleCount++;
      if (opt.value === currentVal) keepCurrent = true;
    }
  });

  if (!keepCurrent) {
    const firstValid = Array.from(options).find(o => o.value && !o.hidden);
    hospSelect.value = firstValid ? firstValid.value : "";
  }
  if (hnone) hnone.hidden = !vaccine || visibleCount > 0;
  updateHospitalPreview();
}

sched?.addEventListener("change", filterHospitals);
citySelect?.addEventListener("change", filterHospitals);
hospSelect?.addEventListener("change", updateHospitalPreview);

filterHospitals();

const searchBtn=document.querySelector("#directory-search"), cityInput=document.querySelector("#directory-city"), directoryStatus=document.querySelector("#directory-status"), directoryResults=document.querySelector("#directory-results"), directoryApi=new URL("../api/hospital-directory.php",window.location.href);
searchBtn?.addEventListener("click",async()=>{const city=cityInput.value.trim();if(city.length<2){directoryStatus.textContent="Enter a city name.";return;}searchBtn.disabled=true;directoryStatus.textContent="Searching OpenStreetMap…";directoryResults.replaceChildren();try{directoryApi.search="?city="+encodeURIComponent(city);const response=await fetch(directoryApi);const data=await response.json();if(!response.ok)throw new Error(data.error||"Search failed.");if(!data.results.length){directoryStatus.textContent="No mapped hospitals found near that city.";return;}directoryStatus.textContent=`${data.results.length} mapped places near ${city}.`;const list=document.createElement("div");list.className="tbl";const table=document.createElement("table");for(const place of data.results){const tr=document.createElement("tr"),td=document.createElement("td"),link=document.createElement("a"),strong=document.createElement("strong"),small=document.createElement("small");link.href=place.url;link.target="_blank";link.rel="noopener";strong.textContent=place.name;link.append(strong);small.textContent=[place.type,place.address,place.phone].filter(Boolean).join(" · ");td.append(link,document.createElement("br"),small);tr.append(td);table.append(tr);}list.append(table);directoryResults.append(list);directoryStatus.textContent+=` ${data.notice}`;}catch(error){directoryStatus.textContent=error.message||"Directory search is temporarily unavailable.";}finally{searchBtn.disabled=false;}});
</script>');
