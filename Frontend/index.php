<?php
require __DIR__ . '/../Backend/config/config.php';
require __DIR__ . '/../Backend/includes/layout.php';
$s = [
  'people' => (int)q('SELECT COUNT(*) FROM children')->fetchColumn(),
  'doses' => (int)q("SELECT COUNT(*) FROM schedule WHERE status='vaccinated'")->fetchColumn(),
  'hospitals' => (int)q("SELECT COUNT(*) FROM hospitals WHERE status='approved'")->fetchColumn(),
];
public_start('Never miss a vaccination');
?>
<div class="wrap hero">
  <div>
    <h1>Vaccines for the whole family, on time and on record.</h1>
    <p class="lead">Register your child or yourself once. VaxCare builds the vaccination schedule, reminds you when a dose is due, books it at a nearby hospital and tells you if you are cleared to travel abroad.</p>
    <div class="actions">
      <a class="btn btn-p" href="<?= url('register.php') ?>"><i class="bi bi-person-plus"></i>Register as a parent or adult</a>
      <a class="btn btn-g" href="<?= url('register.php?role=hospital') ?>"><i class="bi bi-hospital"></i>Register a hospital</a>
    </div>
    <svg class="ecg" viewBox="0 0 420 70" preserveAspectRatio="none" aria-hidden="true"><path d="M0 38h110l14-26 18 52 16-36 12 10h40l12-22 14 40 10-18h174"/></svg>
    <p class="muted"><b data-count="<?= $s['people'] ?>"><?= $s['people'] ?></b> people registered · <b data-count="<?= $s['doses'] ?>"><?= $s['doses'] ?></b> doses recorded · <b data-count="<?= $s['hospitals'] ?>"><?= $s['hospitals'] ?></b> partner hospitals</p>
  </div>
  <div class="vcard" aria-label="Interactive sample vaccination card" data-reveal>
    <header><b>Immunization card</b><small>Zain, 3 months</small></header>
    <p class="vcard-hint">Demo preview · Select a vaccine to update its status</p>
    <button type="button" class="vrow done" data-vaccine-toggle data-state="given" data-pending-label="At birth" aria-pressed="true" aria-label="BCG, given. Select to mark not given">
      <span class="tick" aria-hidden="true"><i class="bi bi-check"></i></span><span>BCG</span><small data-vaccine-status>Given · At birth</small>
    </button>
    <button type="button" class="vrow done" data-vaccine-toggle data-state="given" data-pending-label="At birth" aria-pressed="true" aria-label="Hepatitis B, given. Select to mark not given">
      <span class="tick" aria-hidden="true"><i class="bi bi-check"></i></span><span>Hepatitis B</span><small data-vaccine-status>Given · At birth</small>
    </button>
    <button type="button" class="vrow done" data-vaccine-toggle data-state="given" data-pending-label="6 weeks" aria-pressed="true" aria-label="Pentavalent-1, given. Select to mark not given">
      <span class="tick" aria-hidden="true"><i class="bi bi-check"></i></span><span>Pentavalent-1</span><small data-vaccine-status>Given · 6 weeks</small>
    </button>
    <button type="button" class="vrow next" data-vaccine-toggle data-state="pending" data-pending-label="Due in 5 days" aria-pressed="false" aria-label="Pentavalent-2, due in 5 days. Select to mark given">
      <span class="tick" aria-hidden="true"><i class="bi bi-bell"></i></span><span>Pentavalent-2</span><small data-vaccine-status>Due in 5 days</small>
    </button>
    <button type="button" class="vrow" data-vaccine-toggle data-state="pending" data-pending-label="14 weeks" aria-pressed="false" aria-label="Pentavalent-3, not given. Select to mark given">
      <span class="tick" aria-hidden="true"></span><span>Pentavalent-3</span><small data-vaccine-status>Not given · 14 weeks</small>
    </button>
    <div class="vcard-progress" aria-live="polite"><strong data-vaccine-count>3 of 5 given</strong><span class="vcard-progress-track"><span data-vaccine-progress style="width:60%"></span></span></div>
  </div>
</div>
<div class="wrap travel glass" data-reveal>
  <div>
    <h2><span class="plane"><i class="bi bi-airplane"></i></span> Adults need vaccines too</h2>
    <p class="muted" style="margin-top:.6rem">Flying abroad? Required travel vaccines must be on record first. Without them you are not cleared to travel. Book, get vaccinated and print your travel certificate in one place.</p>
    <a class="btn btn-s" href="<?= url('register.php') ?>">Check my travel clearance</a>
    <button class="btn btn-g btn-sm checklist-toggle" type="button" data-toggle-target="sampleTravelVaccines" aria-controls="sampleTravelVaccines" aria-expanded="true">Hide sample checklist</button>
  </div>
  <ul class="vlist" id="sampleTravelVaccines" aria-label="Sample travel clearance">
    <li class="done"><div class="tick"><i class="bi bi-check"></i></div><span>Polio (IPV/OPV)</span><small>Given</small></li>
    <li class="done"><div class="tick"><i class="bi bi-check"></i></div><span>Meningococcal ACWY</span><small>Given</small></li>
    <li><div class="tick"><i class="bi bi-x"></i></div><span>COVID-19 vaccine</span><span class="badge warn" style="flex:none">Book now</span></li>
  </ul>
</div>
<section class="band"><div class="wrap">
  <h2>How a vaccination gets booked</h2>
  <ol class="steps">
    <li class="step" data-reveal><b>Add a child or an adult</b><span>The right schedule is created automatically.</span></li>
    <li class="step" data-reveal><b>Book a hospital</b><span>Pick a due vaccine, a hospital that has it, a date and a time slot.</span></li>
    <li class="step" data-reveal><b>Admin approves</b><span>Requests are reviewed and you are notified.</span></li>
    <li class="step" data-reveal><b>Hospital vaccinates</b><span>The dose is recorded and travel clearance updates.</span></li>
  </ol>
</div></section>
<div class="wrap roles">
  <div class="glass" data-reveal><i class="bi bi-people"></i><h3>Parents and adults</h3><ul><li>Manage children and adults in one account</li><li>See due and overdue vaccines</li><li>Travel clearance and certificate</li></ul></div>
  <div class="glass" data-reveal><i class="bi bi-shield-check"></i><h3>Admin</h3><ul><li>Approve or reject requests</li><li>Live charts and date-wise reports</li><li>Choose which vaccines are required for travel</li></ul></div>
  <div class="glass" data-reveal><i class="bi bi-hospital"></i><h3>Hospitals</h3><ul><li>Receive approved appointments</li><li>Record vaccinated or not vaccinated</li><li>Live vaccine stock</li></ul></div>
</div>
<footer class="pf">© <?= date('Y') ?> <?= APP_NAME ?> · E-Vaccination Management System</footer>
<?php public_end();
