<?php
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/helpers.php';

// Every action below needs a logged-in user.
$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = trim($_POST['name'] ?? '');
    # check name of category that valid
    if ($name === '') {
        $_SESSION['nameErr'] = 'نام دسته بندی باید پر شود';
    } elseif (mb_strlen($name) > 100) {
        $_SESSION['nameErr'] = 'نام دسته بندی حداکثر ۱۰۰ کاراکتر است';
    } else {
        query($db, 'INSERT INTO categories (name, fk_user) VALUES (?, ?)', [$name, $userId]);
        $_SESSION['message'] = 'دسته بندی جدید به سیستم اضافه شد';
    }
    redirect('../View/AddCategory.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['del'])) {
    $id = $_GET['del'];
    // "AND fk_user = ?" makes sure users can only delete their OWN categories.
    $category = mysqli_fetch_assoc(query($db, 'SELECT id FROM categories WHERE id = ? AND fk_user = ?', [$id, $userId]));
    if ($category) {
        query($db, 'DELETE FROM categories_posts WHERE id_category = ?', [$category['id']]);
        query($db, 'DELETE FROM categories WHERE id = ?', [$category['id']]);
        $_SESSION['message'] = 'دسته بندی مورد نظر حذف شد';
    } else {
        $_SESSION['message'] = 'دسته بندی پیدا نشد';
    }
    redirect('../View/AddCategory.php');
}

$allEntities = query($db, 'SELECT id, name FROM categories WHERE fk_user = ? ORDER BY name', [$userId]);
