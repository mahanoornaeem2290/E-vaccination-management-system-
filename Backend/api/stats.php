<?php
// Read-only JSON feed for the live dashboards. Each role only receives its own scope.
require __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$u = me();
$scope = $_GET['scope'] ?? '';
if (!$u || $u['role'] !== $scope || !in_array($scope, ['admin', 'parent', 'hospital'], true)) { http_response_code(403); exit('{"error":"forbidden"}'); }
session_write_close(); // do not block other requests while polling

function series_days(int $n, array $byDate): array {
    $labels = $vals = [];
    for ($i = $n - 1; $i >= 0; $i--) { $d = date('Y-m-d', strtotime("-$i day")); $labels[] = date('d M', strtotime($d)); $vals[] = (int)($byDate[$d] ?? 0); }
    return ['labels' => $labels, 'values' => $vals];
}
$n = fn(string $sql, array $a = []) => (int)q($sql, $a)->fetchColumn();

if ($scope === 'admin') {
    $st = q('SELECT status,COUNT(*) FROM schedule GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
    $given = q("SELECT given_on d,COUNT(*) c FROM schedule WHERE status='vaccinated' AND given_on>=DATE_SUB(CURDATE(),INTERVAL 13 DAY) GROUP BY given_on")->fetchAll(PDO::FETCH_KEY_PAIR);
    $mo = q("SELECT DATE_FORMAT(appt_date,'%Y-%m') ym, COUNT(*) c FROM appointments WHERE appt_date>=DATE_SUB(CURDATE(),INTERVAL 5 MONTH) GROUP BY ym ORDER BY ym")->fetchAll();
    $adults = $n("SELECT COUNT(*) FROM children WHERE member_type='adult'");
    $cleared = $n("SELECT COUNT(*) FROM children c WHERE c.member_type='adult' AND " . CLEARED_SQL);
    echo json_encode([
        'kpi' => ['people' => $n('SELECT COUNT(*) FROM children'), 'parents' => $n("SELECT COUNT(*) FROM users WHERE role='parent'"),
                  'hospitals' => $n("SELECT COUNT(*) FROM hospitals WHERE status='approved'"), 'pending' => $n("SELECT COUNT(*) FROM appointments WHERE admin_status='pending'"),
                  'doses' => (int)($st['vaccinated'] ?? 0), 'adults' => $adults],
        'status' => ['vaccinated' => (int)($st['vaccinated'] ?? 0), 'booked' => (int)($st['booked'] ?? 0), 'pending' => (int)($st['pending'] ?? 0)],
        'daily' => series_days(14, $given),
        'monthly' => ['labels' => array_map(fn($m) => date('M Y', strtotime($m['ym'] . '-01')), $mo), 'values' => array_map(fn($m) => (int)$m['c'], $mo)],
        'audience' => ['child' => $n("SELECT COUNT(*) FROM children WHERE member_type='child'"), 'adult' => $adults],
        'travel' => ['cleared' => $cleared, 'blocked' => $adults - $cleared],
    ]);
} elseif ($scope === 'parent') {
    $rows = q("SELECT c.name, SUM(s.status='vaccinated') done, SUM(s.status='booked') booked, SUM(s.status='pending') pending
               FROM children c JOIN schedule s ON s.child_id=c.id WHERE c.parent_id=? GROUP BY c.id,c.name ORDER BY c.name", [$u['id']])->fetchAll();
    echo json_encode([
        'kpi' => ['members' => count($rows), 'given' => array_sum(array_column($rows, 'done')), 'booked' => array_sum(array_column($rows, 'booked')), 'due' => array_sum(array_column($rows, 'pending'))],
        'members' => ['labels' => array_column($rows, 'name'), 'done' => array_map('intval', array_column($rows, 'done')),
                      'booked' => array_map('intval', array_column($rows, 'booked')), 'pending' => array_map('intval', array_column($rows, 'pending'))],
    ]);
} else {
    $h = q('SELECT id FROM hospitals WHERE user_id=?', [$u['id']])->fetch();
    if (!$h) { http_response_code(403); exit('{"error":"forbidden"}'); }
    $stock = q('SELECT v.name,COALESCE(hv.stock,0) stock FROM vaccines v LEFT JOIN hospital_vaccines hv ON hv.vaccine_id=v.id AND hv.hospital_id=? WHERE v.available=1 ORDER BY v.audience,v.age_days,v.id', [$h['id']])->fetchAll();
    $wk = q("SELECT appt_date d,COUNT(*) c FROM appointments WHERE hospital_id=? AND admin_status='approved' AND appt_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 6 DAY) GROUP BY appt_date", [$h['id']])->fetchAll(PDO::FETCH_KEY_PAIR);
    $labels = $vals = [];
    for ($i = 0; $i < 7; $i++) { $d = date('Y-m-d', strtotime("+$i day")); $labels[] = date('D d', strtotime($d)); $vals[] = (int)($wk[$d] ?? 0); }
    $hid = [$h['id']];
    echo json_encode([
        'kpi' => ['today' => $n("SELECT COUNT(*) FROM appointments WHERE hospital_id=? AND appt_date=CURDATE() AND admin_status='approved'", $hid),
                  'upcoming' => $n("SELECT COUNT(*) FROM appointments WHERE hospital_id=? AND admin_status='approved' AND vax_status='pending' AND appt_date>=CURDATE()", $hid),
                  'overdue' => $n("SELECT COUNT(*) FROM appointments WHERE hospital_id=? AND admin_status='approved' AND vax_status='pending' AND appt_date<CURDATE()", $hid),
                  'given' => $n("SELECT COUNT(*) FROM appointments WHERE hospital_id=? AND vax_status='vaccinated'", $hid)],
        'stock' => ['labels' => array_column($stock, 'name'), 'values' => array_map('intval', array_column($stock, 'stock'))],
        'week' => ['labels' => $labels, 'values' => $vals],
    ]);
}
