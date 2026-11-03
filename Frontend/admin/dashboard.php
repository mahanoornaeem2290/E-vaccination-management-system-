<?php
require __DIR__ . '/../../Backend/config/config.php';
require __DIR__ . '/../../Backend/includes/layout.php';
require_role('admin');
$upcoming = q(APPT_SQL . "WHERE a.admin_status='approved' AND a.vax_status='pending' AND a.appt_date>=CURDATE() ORDER BY a.appt_date,a.slot LIMIT 6")->fetchAll();
$pend = q(APPT_SQL . "WHERE a.admin_status='pending' ORDER BY a.created_at DESC LIMIT 5")->fetchAll();
page_start('Dashboard', 'admin');
live_pill();
?>
<div class="strip">
  <div><b data-kpi="people" data-count="0">0</b><span><i class="bi bi-people"></i>People registered</span></div>
  <div><b data-kpi="adults" data-count="0">0</b><span><i class="bi bi-person-badge"></i>Adults</span></div>
  <div><b data-kpi="hospitals" data-count="0">0</b><span><i class="bi bi-hospital"></i>Hospitals</span></div>
  <div><b data-kpi="pending" data-count="0">0</b><span><i class="bi bi-inbox"></i>Pending requests</span></div>
  <div><b data-kpi="doses" data-count="0">0</b><span><i class="bi bi-shield-check"></i>Doses given</span></div>
</div>
<div class="cols">
  <section class="card dose-status-card" aria-labelledby="doseStatusTitle">
    <div class="dose-status-heading"><div><h2 id="doseStatusTitle">Dose status</h2><p class="muted">Live breakdown of vaccination progress</p></div><span class="dose-live-tag"><i></i>Live</span></div>
    <div class="dose-status-layout">
      <div class="chart dose-status-chart"><canvas id="cStatus" role="img" aria-label="Doughnut chart showing vaccinated, booked, and pending doses"></canvas></div>
      <div class="dose-status-legend" role="group" aria-label="Show or hide dose statuses">
        <button type="button" class="dose-status-item" data-status-toggle="0" aria-pressed="true">
          <span class="dose-status-label"><i class="dose-dot given"></i>Vaccinated</span><strong data-status-count="0">0</strong><small data-status-percent="0">0%</small>
        </button>
        <button type="button" class="dose-status-item" data-status-toggle="1" aria-pressed="true">
          <span class="dose-status-label"><i class="dose-dot booked"></i>Booked</span><strong data-status-count="1">0</strong><small data-status-percent="1">0%</small>
        </button>
        <button type="button" class="dose-status-item" data-status-toggle="2" aria-pressed="true">
          <span class="dose-status-label"><i class="dose-dot pending"></i>Pending</span><strong data-status-count="2">0</strong><small data-status-percent="2">0%</small>
        </button>
      </div>
    </div>
    <p class="dose-status-hint"><i class="bi bi-info-circle"></i> Select a status to show or hide it in the chart.</p>
  </section>
  <div class="card"><h2>Doses given, last 14 days</h2><div class="chart"><canvas id="cDaily"></canvas></div></div>
</div>
<div class="cols">
  <div class="card"><h2>Children and adults</h2><div class="chart"><canvas id="cAudience"></canvas></div></div>
  <div class="card"><h2>Adults cleared to travel</h2><div class="chart"><canvas id="cTravel"></canvas></div></div>
  <div class="card"><h2>Appointments per month</h2><div class="chart"><canvas id="cMonthly"></canvas></div></div>
</div>
<div class="cols">
  <div class="card"><h2>Requests waiting for you</h2>
    <?php if (!$pend): empty_state('No pending requests.'); else: ?><div class="tbl"><table><tr><th>Person</th><th>Vaccine</th><th>Date</th></tr>
    <?php foreach ($pend as $r): ?><tr><td><?= e($r['child']) ?></td><td><?= e($r['vaccine']) ?></td><td><?= fmt_date($r['appt_date']) ?></td></tr><?php endforeach; ?></table></div>
    <a class="btn btn-p btn-sm" href="requests.php">Review requests</a><?php endif; ?></div>
  <div class="card"><h2>Next vaccination appointments</h2>
    <?php if (!$upcoming): empty_state('No approved appointments coming up.'); else: ?><div class="tbl"><table><tr><th>Date &amp; time</th><th>Person</th><th>Hospital</th></tr>
    <?php foreach ($upcoming as $r): ?><tr><td><?= fmt_date($r['appt_date']) ?><br><small><?= e($r['slot']) ?></small></td><td><?= e($r['child']) ?><br><small><?= e($r['vaccine']) ?></small></td><td><?= e($r['hospital']) ?></td></tr><?php endforeach; ?></table></div><?php endif; ?></div>
</div>
<?php page_end(live_scripts('admin'));
