<?php
/* =========================================================
   db.php - database connection (the ONLY place credentials live)

   Default values match a fresh XAMPP installation.
   If your MySQL has a password, either edit the values below or
   (better for GitHub) create a file named db.local.php next to
   this file that returns your own values, for example:

       <?php
       return ['pass' => 'mySecretPassword'];

   db.local.php is listed in .gitignore so it is never uploaded.
   ========================================================= */

$dbConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'student_portal',
];

// Optional local override (not committed to Git)
if (file_exists(__DIR__ . '/db.local.php')) {
    $dbConfig = array_merge($dbConfig, require __DIR__ . '/db.local.php');
}

// ---------- Error display ----------
// XAMPP shows raw PHP errors in the browser by default. Those include full
// file paths and stack traces, so we hide them and log them instead
// (on XAMPP they appear in C:\xampp\apache\logs\error.log).
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Any error that is not caught elsewhere (e.g. a missing table) shows this
// short, clear message instead of a stack trace. The database message itself
// (e.g. "Table 'student_portal.students' doesn't exist") contains no file paths.
set_exception_handler(function (Throwable $e) {
    error_log('Unhandled ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo '<h2 style="font-family:sans-serif;color:#dc2626">Something went wrong</h2>';
    echo '<p style="font-family:sans-serif">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p style="font-family:sans-serif">If this is a database error, check that <b>student_portal.sql</b> was imported.</p>';
});

// Make mysqli throw exceptions on errors instead of failing silently
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($dbConfig['host'], $dbConfig['user'], $dbConfig['pass'], $dbConfig['name']);
    $conn->set_charset('utf8mb4');  // support all characters (names, symbols)
} catch (mysqli_sql_exception $e) {
    // Clear message during development. In a real deployment you would
    // log the error instead of showing it to visitors.
    http_response_code(500);
    echo '<h2 style="font-family:sans-serif;color:#dc2626">Database connection failed</h2>';
    echo '<p style="font-family:sans-serif">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p style="font-family:sans-serif">Check that MySQL is running in XAMPP and that the '
       . '<b>student_portal</b> database was imported from database/student_portal.sql.</p>';
    exit;
}
