<?php
// Small helper functions shared by every page.
// Before: test_input() was copy-pasted into three files. Now there is one place to fix things.

/**
 * Escape a value for safe output inside HTML (prevents XSS).
 * Rule: store the raw value in the database, escape it when you echo it.
 */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Send the browser to another page and stop the script.
 * Without exit, PHP keeps running the code below header().
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Stop here unless a user is logged in. Returns the logged-in user's id.
 */
function require_login(): int
{
    if (empty($_SESSION['onlineUser']['id'])) {
        redirect('../View/login.php');
    }
    return (int)$_SESSION['onlineUser']['id'];
}

/**
 * Read a one-time message from the session, delete it, and return it escaped.
 */
function flash(string $key): string
{
    $message = $_SESSION[$key] ?? '';
    unset($_SESSION[$key]);
    return e($message);
}

/**
 * Run a SQL query with a prepared statement.
 * User input goes in $params, never inside $sql, so it can't change the query (no SQL injection).
 *
 * Example: query($db, 'SELECT * FROM posts WHERE id = ? AND fk_user = ?', [$id, $userId]);
 *
 * Returns a result for SELECT, or false for INSERT/UPDATE/DELETE.
 */
function query(mysqli $db, string $sql, array $params = []): mysqli_result|false
{
    $stmt = mysqli_prepare($db, $sql);
    if ($params) {
        $types = str_repeat('s', count($params)); // send every value as a string; MySQL converts it
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}
