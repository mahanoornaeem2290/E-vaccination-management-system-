<?php
function logo_svg(): string {
    return '<svg class="mark" viewBox="0 0 32 32" aria-hidden="true"><defs><linearGradient id="lg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#19c4b4"/><stop offset="1" stop-color="#38b6f0"/></linearGradient></defs><path d="M16 2 4 7v8c0 7.5 5 13 12 15 7-2 12-7.5 12-15V7z" fill="url(#lg)"/><path d="M7 16h5l2-4 3 8 2-4h6" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}
function theme_toggle(): string {
    return '<button type="button" class="theme-toggle" id="themeToggle" aria-label="Switch between day and night mode" title="Day / night">'
         . '<svg class="ic-sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4.2" fill="currentColor"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2.5v2.4M12 19.1v2.4M2.5 12h2.4M19.1 12h2.4M5.3 5.3l1.7 1.7M17 17l1.7 1.7M5.3 18.7 7 17M17 7l1.7-1.7"/></g></svg>'
         . '<svg class="ic-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5a8.5 8.5 0 1 0 10.7 10.7z" fill="currentColor"/></svg></button>';
}
function head(string $title): void {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
       . '<meta name="color-scheme" content="light dark">'
       // Apply the saved day/night choice before the page paints (no flash)
       . '<script>try{var t=localStorage.getItem("vaxTheme");if(!t)t=matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light";document.documentElement.dataset.theme=t}catch(e){}</script>'
       . '<title>' . e($title) . ' · ' . APP_NAME . '</title>'
       . '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
       . '<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">'
       . '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">'
       . '<link rel="stylesheet" href="' . url('assets/css/style.css') . '"></head><body>'
       . '<div class="bg" aria-hidden="true"><i></i><i></i><i></i></div>';
}
function menu(string $role): array {
    return [
        'admin' => [['admin/dashboard.php', 'Dashboard', 'bi-grid'], ['admin/children.php', 'People', 'bi-people'], ['admin/travel.php', 'Travel', 'bi-airplane'],
            ['admin/vaccines.php', 'Vaccines', 'bi-capsule'], ['admin/requests.php', 'Requests', 'bi-inbox'],
            ['admin/hospitals.php', 'Hospitals', 'bi-hospital'], ['admin/bookings.php', 'Bookings', 'bi-calendar-check'],
            ['admin/reports.php', 'Reports', 'bi-graph-up']],
        'parent' => [['parent/dashboard.php', 'Dashboard', 'bi-grid'], ['parent/children.php', 'My family', 'bi-people'], ['parent/travel.php', 'Travel clearance', 'bi-airplane'],
            ['parent/book.php', 'Book vaccine', 'bi-calendar-plus'], ['parent/appointments.php', 'Appointments', 'bi-calendar-check'],
            ['parent/report.php', 'Vaccination report', 'bi-journal-medical'], ['parent/profile.php', 'My profile', 'bi-person']],
        'hospital' => [['hospital/dashboard.php', 'Dashboard', 'bi-grid'], ['hospital/appointments.php', 'Appointments', 'bi-calendar-check'],
            ['hospital/stock.php', 'Vaccine stock', 'bi-box-seam']],
    ][$role];
}
function page_start(string $title, string $role): void {
    $u = me();
    head($title);
    $pending = $role === 'admin' ? (int)q("SELECT COUNT(*) FROM appointments WHERE admin_status='pending'")->fetchColumn() : 0;
    echo '<div class="app"><aside class="side" id="side"><a class="logo" href="' . url() . '">' . logo_svg() . APP_NAME . '</a><nav>';
    foreach (menu($role) as [$path, $label, $icon]) {
        $on = str_ends_with($_SERVER['SCRIPT_NAME'], $path) ? ' class="on"' : '';
        $b = ($path === 'admin/requests.php' && $pending) ? '<span class="count">' . $pending . '</span>' : '';
        echo '<a' . $on . ' href="' . url($path) . '"><i class="bi ' . $icon . '"></i>' . e($label) . $b . '</a>';
    }
    echo '</nav><a class="out" href="' . url('logout.php') . '"><i class="bi bi-box-arrow-left"></i>Log out</a></aside>'
       . '<div class="main"><header class="top"><button id="burger" class="btn btn-g btn-sm" aria-label="Menu"><i class="bi bi-list"></i></button>'
       . '<h1>' . e($title) . '</h1>' . theme_toggle() . '<div class="who"><span>' . e($u['name']) . '</span><small>' . e(ucfirst($role)) . '</small></div></header><main class="content">';
    if ($f = flash()) echo '<div class="alert ' . e($f[1]) . '">' . e($f[0]) . '</div>';
}
function page_end(string $extra = ''): void {
    echo '</main></div></div><script src="' . url('assets/js/app.js') . '"></script>' . $extra . '</body></html>';
}
function public_start(string $title): void {
    head($title);
    $u = me();
    echo '<header class="pnav"><a class="logo dark" href="' . url() . '">' . logo_svg() . APP_NAME . '</a><nav>' . theme_toggle();
    if ($u) echo '<a class="btn btn-p btn-sm" href="' . url(home_for($u['role'])) . '">Open dashboard</a>';
    else echo '<a href="' . url('login.php') . '">Log in</a><a class="btn btn-p btn-sm" href="' . url('register.php') . '">Create account</a>';
    echo '</nav></header>';
    if ($f = flash()) echo '<div class="wrap"><div class="alert ' . e($f[1]) . '">' . e($f[0]) . '</div></div>';
}
function public_end(): void { echo '<script src="' . url('assets/js/app.js') . '"></script></body></html>'; }
function field(string $label, string $name, string $type = 'text', array $o = []): void {
    $v = $type === 'password' ? '' : ($o['value'] ?? ($_POST[$name] ?? ''));
    $id = e($name);
    $a = (($o['required'] ?? true) ? ' required' : '') . ' ' . ($o['attr'] ?? '');
    echo '<div class="f ' . e($o['class'] ?? '') . '"><label for="' . $id . '">' . e($label) . '</label>';
    if ($type === 'select') {
        echo '<select id="' . $id . '" name="' . $id . '"' . $a . '>';
        foreach ($o['options'] as $k => $t) echo '<option value="' . e($k) . '"' . ((string)$k === (string)$v ? ' selected' : '') . '>' . e($t) . '</option>';
        echo '</select>';
    } elseif ($type === 'textarea') {
        echo '<textarea id="' . $id . '" name="' . $id . '" rows="3"' . $a . '>' . e($v) . '</textarea>';
    } else {
        echo '<input id="' . $id . '" name="' . $id . '" type="' . $type . '" value="' . e($v) . '"' . $a . '>';
    }
    echo '</div>';
}
function empty_state(string $msg): void { echo '<div class="empty"><i class="bi bi-inbox"></i><p>' . e($msg) . '</p></div>'; }

/** Chart.js + live-polling charts for a dashboard (scope: admin | parent | hospital). */
function live_scripts(string $scope): string {
    return '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>'
         . '<script src="' . url('assets/js/charts.js') . '"></script>'
         . '<script>Live.start(' . json_encode($scope) . ',' . json_encode(url('api/stats.php')) . ');</script>';
}
function live_pill(): void { echo '<div class="live" id="livePill"><i></i><span>Live</span><small id="liveTime">connecting…</small></div>'; }
