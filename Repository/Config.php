<?php
// Database settings.
// Real values come from environment variables, so passwords never end up in git.
// The second value after ?: is only a local-development default.
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'password');
define('DB_NAME', getenv('DB_NAME') ?: 'crud');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Throw an exception on any database error instead of failing silently.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
mysqli_set_charset($db, 'utf8mb4'); // needed for Persian text
