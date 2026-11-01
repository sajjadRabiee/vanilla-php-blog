<?php
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // never trim or change a password
    $valid = true;

    # check name validation
    if ($name === '') {
        $_SESSION['nameErr'] = 'نام باید پر شود';
        $valid = false;
    }
    # check email validation
    if ($email === '') {
        $_SESSION['emailErr'] = 'ایمیل باید پر شود';
        $valid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['emailErr'] = 'فرمت ایمیل نادرست می باشد';
        $valid = false;
    }
    # check password validation
    if ($password === '') {
        $_SESSION['passwordErr'] = 'رمز عبور باید پر شود';
        $valid = false;
    } elseif (strlen($password) < 6) {
        $_SESSION['passwordErr'] = 'رمز عبور باید حداقل ۶ کاراکتر باشد';
        $valid = false;
    }
    # check username validation
    if ($username === '') {
        $_SESSION['usernameErr'] = 'نام کاربری باید پر شود';
        $valid = false;
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $_SESSION['usernameErr'] = 'نام کاربری فقط حروف انگلیسی، عدد و _ (۳ تا ۳۰ کاراکتر)';
        $valid = false;
    } elseif (mysqli_num_rows(query($db, 'SELECT id FROM users WHERE username = ?', [$username])) > 0) {
        $_SESSION['usernameErr'] = 'این نام کاربری قبلا ثبت شده است';
        $valid = false;
    }

    if ($valid) {
        // Store a one-way hash, never the real password.
        $hash = password_hash($password, PASSWORD_DEFAULT);
        query($db, 'INSERT INTO users (name, username, password, mail) VALUES (?, ?, ?, ?)',
            [$name, $username, $hash, $email]);
        $_SESSION['message'] = 'ثبت نام با موفقیت انجام شد';
    }
    redirect('../View/Register.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '') {
        $_SESSION['usernameErr'] = 'نام کاربری باید پر شود';
    }
    if ($password === '') {
        $_SESSION['passwordErr'] = 'رمز عبور باید پر شود';
    }

    if ($username !== '' && $password !== '') {
        $user = mysqli_fetch_assoc(query($db, 'SELECT * FROM users WHERE username = ?', [$username]));

        // $user is null when the username does not exist, so check it first.
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // new session id after login (prevents session fixation)
            // Keep only what we need in the session (not the password hash).
            $_SESSION['onlineUser'] = [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
            ];
            $_SESSION['loginMessage'] = 'با موفقیت وارد شدید';
            redirect('../View/dashboard.php');
        }
        $_SESSION['message'] = 'نام کاربری یا رمز ورود اشتباه است';
    }
    redirect('../View/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['exit'])) {
    $_SESSION = [];
    session_destroy();
    redirect('../View/login.php');
}
