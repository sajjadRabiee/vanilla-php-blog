<?php
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/helpers.php';

const IMAGE_DIR = __DIR__ . '/../images/';
const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2 MB
const ALLOWED_IMAGES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
];

// Every action below needs a logged-in user.
$userId = require_login();

/** Find a post only if it belongs to this user. Returns null otherwise. */
function find_post(mysqli $db, $id, int $userId): ?array
{
    $post = mysqli_fetch_assoc(query($db, 'SELECT * FROM posts WHERE id = ? AND fk_user = ?', [$id, $userId]));
    return $post ?: null;
}

/** Ids of the categories attached to a post. */
function post_category_ids(mysqli $db, int $postId): array
{
    $rows = mysqli_fetch_all(query($db, 'SELECT id_category FROM categories_posts WHERE id_post = ?', [$postId]), MYSQLI_ASSOC);
    return array_column($rows, 'id_category');
}

/** Replace the categories of a post. Ignores categories that belong to another user. */
function save_post_categories(mysqli $db, int $postId, int $userId, array $categoryIds): void
{
    query($db, 'DELETE FROM categories_posts WHERE id_post = ?', [$postId]);
    foreach (array_unique($categoryIds) as $categoryId) {
        // INSERT ... SELECT inserts a row only if the category exists AND is ours.
        query($db, 'INSERT INTO categories_posts (id_category, id_post)
                    SELECT id, ? FROM categories WHERE id = ? AND fk_user = ?', [$postId, $categoryId, $userId]);
    }
}

/** Check name and text. Puts error messages in the session. Returns [name, text, isValid]. */
function validate_post(): array
{
    $name = trim($_POST['name'] ?? '');
    $text = trim($_POST['text'] ?? '');
    $valid = true;
    #check name of post that valid
    if ($name === '') {
        $_SESSION['nameErr'] = 'نام باید پر شود';
        $valid = false;
    }
    #check text of post that valid
    if ($text === '') {
        $_SESSION['textErr'] = 'پست بدون متن که پست نیست :)';
        $valid = false;
    }
    return [$name, $text, $valid];
}

/**
 * Save an uploaded image with a safe random name.
 * Returns the new file name, null when no file was chosen, or false on error.
 */
function upload_image(array $file): string|false|null
{
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null; // the user did not choose a file — that's fine
    }
    if ($error !== UPLOAD_ERR_OK || $file['size'] > MAX_IMAGE_SIZE) {
        $_SESSION['imageErr'] = 'آپلود عکس ناموفق بود (حداکثر ۲ مگابایت)';
        return false;
    }
    // Look at the real file content, not the name the browser sent (it can be "shell.php").
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(ALLOWED_IMAGES[$mime])) {
        $_SESSION['imageErr'] = 'فقط عکس با فرمت jpg, png, gif یا webp قبول است';
        return false;
    }
    $fileName = bin2hex(random_bytes(16)) . '.' . ALLOWED_IMAGES[$mime];
    if (!move_uploaded_file($file['tmp_name'], IMAGE_DIR . $fileName)) {
        $_SESSION['imageErr'] = 'متاسفانه عکس را نتونستیم آپلود کنیم';
        return false;
    }
    return $fileName;
}

function delete_image(?string $fileName): void
{
    if ($fileName) {
        $path = IMAGE_DIR . basename($fileName);
        if (is_file($path)) {
            unlink($path);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    [$name, $text, $valid] = validate_post();
    if ($valid) {
        $image = upload_image($_FILES['image'] ?? []);
        if ($image !== false) {
            query($db, 'INSERT INTO posts (name, text, image, fk_user) VALUES (?, ?, ?, ?)',
                [$name, $text, $image ?? '', $userId]);
            $postId = mysqli_insert_id($db);
            save_post_categories($db, $postId, $userId, (array)($_POST['categories'] ?? []));
            $_SESSION['message'] = 'پست شما با موفقیت در سیستممون ثبت شد';
        }
    }
    redirect('../View/AddPost.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $post = find_post($db, $_POST['id'] ?? 0, $userId);
    if (!$post) {
        $_SESSION['message'] = 'پست پیدا نشد';
        redirect('../View/AddPost.php');
    }
    [$name, $text, $valid] = validate_post();
    if ($valid) {
        $newImage = upload_image($_FILES['image'] ?? []);
        if ($newImage !== false) {
            // No new file chosen? Keep the old image.
            $image = $newImage ?? $post['image'];
            query($db, 'UPDATE posts SET name = ?, text = ?, image = ? WHERE id = ? AND fk_user = ?',
                [$name, $text, $image, $post['id'], $userId]);
            save_post_categories($db, (int)$post['id'], $userId, (array)($_POST['categories'] ?? []));
            if ($newImage !== null) {
                delete_image($post['image']); // remove the old file only after the new one is saved
            }
            $_SESSION['message'] = 'پست شما با موفقیت ویرایش شد .';
        }
    }
    redirect('../View/UpdatePost.php?id=' . (int)$post['id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['del'])) {
    $post = find_post($db, $_GET['del'], $userId);
    if ($post) {
        query($db, 'DELETE FROM categories_posts WHERE id_post = ?', [$post['id']]);
        query($db, 'DELETE FROM posts WHERE id = ?', [$post['id']]);
        delete_image($post['image']);
        $_SESSION['message'] = 'پست مورد نظر حذف شد';
    } else {
        $_SESSION['message'] = 'پست پیدا نشد';
    }
    redirect('../View/AddPost.php');
}

$allEntities = query($db, 'SELECT * FROM posts WHERE fk_user = ? ORDER BY id DESC', [$userId]);
$allCategories = query($db, 'SELECT id, name FROM categories WHERE fk_user = ? ORDER BY name', [$userId]);
