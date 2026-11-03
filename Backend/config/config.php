<?php
declare(strict_types=1);

/**
 * VaxCare - Core Application Configuration & Global Helper Library
 * 
 * Provides database connection abstractions, session management, CSRF protection,
 * role-based authentication middleware, schedule calculation algorithms, and input validation.
 */

// Initialize secure HTTP-only session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// Set national standard timezone for scheduling & appointment timestamps
date_default_timezone_set('Asia/Karachi');

// Database Connection Constants
const DB_HOST = 'localhost';
const DB_NAME = 'vaccination_db';
const DB_USER = 'root';
const DB_PASS = '';

// Application Constants
const APP_NAME   = 'VaxCare';
const SLOTS      = ['09:00 - 11:00', '11:00 - 13:00', '14:00 - 16:00', '16:00 - 18:00'];
const SLOT_LIMIT = 5; // Maximum allowable patient bookings per hospital per time slot

// Resolve Application Base URL for absolute asset & route resolution
$__root = str_replace('\\', '/', (string)realpath(__DIR__ . '/../../Frontend'));
$__doc  = str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
define('BASE_URL', rtrim(str_replace($__doc, '', $__root), '/'));

// Reusable SQL Fragment for Appointment Details
const APPT_SQL = "SELECT a.*, c.name child, v.name vaccine, h.name hospital, u.name parent
  FROM appointments a JOIN children c ON c.id=a.child_id JOIN vaccines v ON v.id=a.vaccine_id
  JOIN hospitals h ON h.id=a.hospital_id JOIN users u ON u.id=c.parent_id ";

/**
 * Returns a shared PDO database connection singleton.
 * Uses prepared statements with emulated prepares disabled for maximum SQL injection immunity.
 *
 * @return PDO Active PDO instance
 */
function db(): PDO {
    static $pdo = null;
    if (!$pdo) {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $ex) {
            http_response_code(500);
            exit('Database connection failed. Import database.sql and check config/config.php.');
        }
    }
    return $pdo;
}

/**
 * Executes a prepared SQL statement with bound positional parameters.
 *
 * @param string $sql  Prepared SQL query
 * @param array  $args Positional parameter bindings
 * @return PDOStatement Executed statement cursor
 */
function q(string $sql, array $args = []): PDOStatement {
    $s = db()->prepare($sql);
    $s->execute($args);
    return $s;
}

/**
 * Sanitizes output for secure HTML rendering, preventing Cross-Site Scripting (XSS).
 *
 * @param mixed $v Raw input string or number
 * @return string Safely escaped HTML entity string
 */
function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/**
 * Generates an absolute web application URL.
 *
 * @param string $p Relative path (e.g. 'parent/book.php')
 * @return string Full web accessible URL
 */
function url(string $p = ''): string {
    return BASE_URL . '/' . ltrim($p, '/');
}

/**
 * Performs a standard HTTP 302 redirect and terminates script execution.
 *
 * @param string $p Target route path
 */
function redirect(string $p) {
    header('Location: ' . url($p));
    exit;
}

/**
 * Safely fetches and trims a string from $_POST.
 *
 * @param string $k Form field key
 * @return string Trimmed POST value
 */
function post(string $k): string {
    return trim((string)($_POST[$k] ?? ''));
}

/**
 * Retrieves or initializes the cryptographically secure CSRF session token.
 *
 * @return string 32-character hexadecimal token
 */
function csrf(): string {
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}

/**
 * Generates a hidden HTML form input containing the active CSRF token.
 *
 * @return string HTML hidden input element
 */
function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . csrf() . '">';
}

/**
 * Middleware: Enforces CSRF token validation on incoming POST requests.
 * Terminates with HTTP 419 on failure to prevent Cross-Site Request Forgery.
 */
function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['_csrf'] ?? ''))) {
        http_response_code(419);
        exit('Your session expired. Go back, refresh the page and try again.');
    }
}

/**
 * Sets or retrieves one-time flash notification messages across redirects.
 *
 * @param string|null $msg Flash message text (null to fetch and clear)
 * @param string      $type Notification style ('ok', 'warn', 'bad')
 * @return array|null [message, type] or null
 */
function flash(?string $msg = null, string $type = 'ok') {
    if ($msg !== null) {
        $_SESSION['flash'] = [$msg, $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/**
 * Returns current authenticated user record from session.
 *
 * @return array|null User record or null if guest
 */
function me(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Returns the default dashboard URL for a given user role.
 *
 * @param string $role User role ('admin', 'hospital', 'parent')
 * @return string Relative dashboard path
 */
function home_for(string $role): string {
    return $role . '/dashboard.php';
}

/**
 * Role-Based Access Control (RBAC) Guard Middleware.
 * Redirects unauthenticated or unauthorized users to login.
 *
 * @param string $role Required role for the current page
 * @return array Authenticated user record
 */
function require_role(string $role): array {
    $u = me();
    if (!$u || $u['role'] !== $role) {
        flash('Please log in to continue.', 'bad');
        redirect('login.php');
    }
    return $u;
}
/**
 * Retrieves the hospital record linked to the current logged-in hospital user.
 *
 * @return array Hospital database row
 */
function my_hospital(): array {
    $h = q('SELECT * FROM hospitals WHERE user_id=?', [me()['id']])->fetch();
    if (!$h) { redirect('logout.php'); }
    return $h;
}

/**
 * Dispatches an in-app notification to a specified user.
 *
 * @param int    $userId Recipient user identifier
 * @param string $msg    Notification message content (truncated to 250 characters)
 */
function notify(int $userId, string $msg): void {
    q('INSERT INTO notifications(user_id,message) VALUES(?,?)', [$userId, mb_substr($msg, 0, 250)]);
}

/**
 * Formats a SQL date string into a user-friendly format (e.g. '15 Aug 2026').
 *
 * @param string|null $d SQL date string (Y-m-d)
 * @return string Formatted date or em-dash if null
 */
function fmt_date(?string $d): string {
    return $d ? date('d M Y', strtotime($d)) : '—';
}

/**
 * Calculates human-readable age label from date of birth (years, months, or days).
 *
 * @param string $dob Date of birth (Y-m-d)
 * @return string Formatted age string (e.g. '3 yrs', '6 mo', '14 days')
 */
function age_label(string $dob): string {
    $diff = (new DateTime($dob))->diff(new DateTime('today'));
    if ($diff->y >= 2) return $diff->y . ' yrs';
    $m = $diff->y * 12 + $diff->m;
    return $m >= 1 ? $m . ' mo' : $diff->d . ' days';
}

/**
 * Renders an accessible HTML badge for appointment and record statuses.
 *
 * @param string $s Raw status identifier
 * @return string HTML badge markup
 */
function badge(string $s): string {
    $m = [
        'pending' => 'warn', 'booked' => 'info', 'approved' => 'ok',
        'vaccinated' => 'ok', 'rejected' => 'bad', 'not_vaccinated' => 'bad',
        'overdue' => 'bad', 'cleared' => 'ok', 'not_cleared' => 'bad',
        'available' => 'ok', 'unavailable' => 'bad'
    ];
    return '<span class="badge ' . ($m[$s] ?? 'info') . '">' . e(ucwords(str_replace('_', ' ', $s))) . '</span>';
}

/**
 * Computes an urgent or countdown badge for pending immunization doses based on due date.
 *
 * @param string $status Current record status
 * @param string $due    Scheduled due date
 * @return string HTML badge markup
 */
function due_badge(string $status, string $due): string {
    if ($status !== 'pending') return badge($status);
    $d = (strtotime($due) - strtotime('today')) / 86400;
    if ($d < 0) return badge('overdue');
    return $d <= 14 ? '<span class="badge warn">Due soon</span>' : '<span class="badge info">Upcoming</span>';
}

/**
 * Core Algorithm: Immunization Schedule Generator.
 * Generates initial vaccination milestones when a new child or adult is registered.
 * - Children: Calculated automatically via DOB + interval days specified in vaccines table.
 * - Adults: Set to CURDATE() so mandatory vaccines can be scheduled immediately.
 *
 * @param int    $memberId Family member identifier (children table primary key)
 * @param string $dob      Date of birth (Y-m-d)
 * @param string $type     Member category ('child' or 'adult')
 */
function make_schedule(int $memberId, string $dob, string $type = 'child'): void {
    q("INSERT INTO schedule(child_id,vaccine_id,due_date)
       SELECT ?, id, IF(?='adult', CURDATE(), DATE_ADD(?, INTERVAL age_days DAY))
       FROM vaccines WHERE audience=?",
      [$memberId, $type, $dob, $type]);
}

/**
 * Core Algorithm: International Travel Clearance Assessment.
 * Evaluates whether an adult traveler has received all mandatory travel vaccines (travel_required = 1).
 *
 * @param int $memberId Family member identifier
 * @return array Clearance evaluation report including required count, completed count, and missing list
 */
function travel_status(int $memberId): array {
    $rows = q("SELECT s.id sid,s.status,s.given_on,v.name,v.description,v.travel_required req
               FROM schedule s JOIN vaccines v ON v.id=s.vaccine_id
               WHERE s.child_id=? ORDER BY v.travel_required DESC, v.name", [$memberId])->fetchAll();
    $req = array_values(array_filter($rows, fn($r) => (int)$r['req'] === 1));
    $rec = array_values(array_filter($rows, fn($r) => (int)$r['req'] === 0));
    $done = count(array_filter($req, fn($r) => $r['status'] === 'vaccinated'));
    return [
        'required'    => $req,
        'recommended' => $rec,
        'done'        => $done,
        'total'       => count($req),
        'cleared'     => $done === count($req),
        'missing'     => array_values(array_map(fn($r) => $r['name'], array_filter($req, fn($r) => $r['status'] !== 'vaccinated')))
    ];
}

// SQL condition for identifying cleared adult travelers in bulk queries
const CLEARED_SQL = "NOT EXISTS (SELECT 1 FROM schedule s JOIN vaccines v ON v.id=s.vaccine_id WHERE s.child_id=c.id AND v.travel_required=1 AND s.status<>'vaccinated')";

/**
 * Returns a styled badge for family member type.
 */
function type_badge(string $t): string {
    return '<span class="badge ' . ($t === 'adult' ? 'sky' : 'ok') . '">' . ($t === 'adult' ? 'Adult' : 'Child') . '</span>';
}

/**
 * Input Validation: Full name (letters, spaces, dots, hyphens, 3-80 chars).
 */
function valid_name(string $p): bool {
    return (bool)preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\s.\'-]{3,80}$/u', trim($p));
}

/**
 * Input Validation: City name.
 */
function valid_city(string $p): bool {
    return (bool)preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\s.\'-]{2,60}$/u', trim($p));
}

/**
 * Input Validation: Street address.
 */
function valid_address(string $p): bool {
    return (bool)preg_match('/^[A-Za-z0-9À-ÖØ-öø-ÿ\s,./#-]{5,120}$/u', trim($p));
}

/**
 * Input Validation: Strong password policy (min 8 chars, at least 1 letter and 1 number).
 */
function valid_password(string $p): bool {
    return strlen($p) >= 8 && preg_match('/[A-Za-z]/', $p) && preg_match('/\d/', $p);
}

/**
 * Input Validation: Phone number format.
 */
function valid_phone(string $p): bool {
    return (bool)preg_match('/^[0-9+\-\s()]{7,20}$/', trim($p));
}

