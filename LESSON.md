# Lesson: From "It Works" to "It's Safe" — Fixing My First PHP Project

This project was my first PHP app (2021). It has registration, login, posts with images, and categories.
It **worked**, but it had the classic beginner problems. This lesson walks through each problem using
the **real code from the first version**, explains why it is dangerous, and shows the fix.

> Want to see the old code? Run `git show 684c161:Repository/PostsCRUD.php` (or any other file).

## Contents

1. [SQL injection → prepared statements](#1-sql-injection--prepared-statements)
2. [Plain-text passwords → password_hash](#2-plain-text-passwords--password_hash)
3. [Escape on output, not on input (XSS)](#3-escape-on-output-not-on-input-xss)
4. [Logged in ≠ allowed (authorization / IDOR)](#4-logged-in--allowed-authorization--idor)
5. [File uploads are dangerous](#5-file-uploads-are-dangerous)
6. [`header()` does not stop your script](#6-header-does-not-stop-your-script)
7. [Small bugs that hide in "happy path" testing](#7-small-bugs-that-hide-in-happy-path-testing)
8. [Don't Repeat Yourself (DRY)](#8-dont-repeat-yourself-dry)
9. [Secrets don't belong in git](#9-secrets-dont-belong-in-git)
10. [Make the project runnable by others](#10-make-the-project-runnable-by-others)
11. [Exercises](#exercises)

---

## 1. SQL injection → prepared statements

**Before** (`PostsCRUD.php`):

```php
$id = $_GET['del'];
mysqli_query($db, "DELETE FROM categories_posts WHERE id_post = '$id'");
mysqli_query($db, "DELETE FROM posts WHERE id='$id'");
```

**The problem:** the user's input is pasted *inside* the SQL text. Visit
`PostsCRUD.php?del=1' OR '1'='1` and the query becomes:

```sql
DELETE FROM posts WHERE id='1' OR '1'='1'   -- deletes EVERY post of EVERY user
```

The same problem was in login: a username like `alice' -- ` comments out the rest of the query.

`test_input()` did **not** protect against this. `htmlspecialchars` is for HTML, not SQL.

**After:** the SQL and the data travel separately. The `?` is a placeholder. The database
knows the value is *data* and can never become *code*.

```php
$stmt = mysqli_prepare($db, 'DELETE FROM posts WHERE id = ?');
mysqli_stmt_bind_param($stmt, 's', $id);
mysqli_stmt_execute($stmt);
```

Writing these three lines every time is boring, so `Repository/helpers.php` has a small `query()` helper:

```php
query($db, 'DELETE FROM posts WHERE id = ? AND fk_user = ?', [$post['id'], $userId]);
```

> **Rule:** never put a variable inside an SQL string. Always use `?` placeholders.

---

## 2. Plain-text passwords → `password_hash`

**Before** (`UsersCRUD.php`):

```php
mysqli_query($db, "INSERT INTO users (..., password, ...) VALUES (..., '$password', ...)");
// ...
if ($user["password"] == $password) {
```

**The problem:** anyone who can read the database (a backup, a leak, a co-worker) sees every
password. People reuse passwords, so you leak their email and bank passwords too.

**After:**

```php
// register
$hash = password_hash($password, PASSWORD_DEFAULT);   // "$2y$10$Jk3..."  (one-way)

// login
if ($user && password_verify($password, $user['password'])) {
```

- A hash can't be reversed. `password_verify` hashes the typed password and compares the results.
- Each hash has a random **salt** built in, so two users with the same password get different hashes.
- The `password` column must be `VARCHAR(255)`. Hashes are long.

Two more login fixes:

- `$user` is `null` when the username doesn't exist. The old code read `$user["password"]` anyway,
  which printed a warning. Now we check `$user &&` first.
- `session_regenerate_id(true)` after login gives the user a fresh session id (this prevents *session fixation*).
  We also store only `id`, `name` and `username` in the session, not the whole user row with the password.

---

## 3. Escape on output, not on input (XSS)

**Before:**

```php
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);   // escaped BEFORE saving to the database
    return $data;
}
// ...
<td><?php echo $row['name']; ?></td>  // echoed with no escaping
```

**The problems:**

1. Data in the database is "dirty". `Tom & Jerry` is saved as `Tom &amp; Jerry`. If you later
   build a JSON API or an Excel export, you get `&amp;` everywhere.
2. Some values were **never** escaped: the uploaded file name, `$_SESSION['image']`, and
   `$_GET['del']`. One missed place is enough for an attacker.
3. `stripslashes` removes backslashes the user really typed. It is left over from PHP 5's "magic quotes".

**After:** store the raw value. Escape it **at the moment you print it into HTML**, every time:

```php
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
```

```php
<td><?= e($row['name']) ?></td>
<input value="<?= e($post['name']) ?>">
<option value="<?= e($category['id']) ?>"><?= e($category['name']) ?></option>
```

Test it: create a category called `<script>alert(1)</script>`. It shows as text, and no alert box appears.

> **Rule:** *Validate input, escape output.* Each output type has its own escaping:
> HTML → `htmlspecialchars`, SQL → prepared statements, URL → `urlencode`, shell → `escapeshellarg`.

---

## 4. Logged in ≠ allowed (authorization / IDOR)

**Before:** the *pages* in `View/` checked `$_SESSION['onlineUser']`, but the files that actually
do the work (`Repository/*CRUD.php`) did **not**. Anyone could open
`Repository/PostsCRUD.php?del=5` directly, without logging in.

Even when logged in, nothing checked *whose* post it was:

```php
mysqli_query($db, "DELETE FROM posts WHERE id='$id'");   // any user can delete any post
```

This is called **IDOR** (Insecure Direct Object Reference). You change the id in the URL and
you can touch someone else's data. It is one of the most common bugs in real web apps.

**After:**

```php
$userId = require_login();   // first line of every CRUD file

function find_post(mysqli $db, $id, int $userId): ?array {
    // the post must exist AND belong to me
    return mysqli_fetch_assoc(query($db,
        'SELECT * FROM posts WHERE id = ? AND fk_user = ?', [$id, $userId])) ?: null;
}
```

The same idea is used for categories, and for the categories you attach to a post:

```php
// only inserts if the category belongs to this user
INSERT INTO categories_posts (id_category, id_post)
SELECT id, ? FROM categories WHERE id = ? AND fk_user = ?
```

> **Rule:** authentication = *who are you?* Authorization = *are you allowed to do this to this thing?*
> Check both, on the server, for every request.

---

## 5. File uploads are dangerous

**Before:**

```php
if (!empty($_FILES['image'])) {
    $image = test_input($_FILES['image']['name']);
    $target = "../images/" . basename($image);
}
move_uploaded_file($_FILES['image']['tmp_name'], $target);
```

**Problems:**

1. **Remote code execution.** Upload `shell.php` and then visit `/images/shell.php`. The server *runs* it.
   The attacker now controls your server.
2. **Overwriting.** Two users upload `photo.jpg`, and the second one replaces the first.
3. **`!empty($_FILES['image'])` is always true** when the form has a file input, even if no file was
   chosen. So *editing a post without choosing a new image deleted the old image* (`unlink`) and saved an
   empty name.
4. The `images/` folder was deleted from the repo, so uploads always failed on a fresh clone.

**After** (`upload_image()` in `PostsCRUD.php`):

```php
if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;              // no file chosen: OK
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);     // check the real content
if (!isset(ALLOWED_IMAGES[$mime])) { /* error */ }                    // allow-list: jpg/png/gif/webp
$fileName = bin2hex(random_bytes(16)) . '.' . ALLOWED_IMAGES[$mime];  // our name, our extension
```

- We never trust the file name or type the browser sends. Both are controlled by the user.
- The extension comes from *our* allow-list, so a `.php` file can never be created.
- The old image is deleted **only after** the new one is saved successfully.
- Deleting a post also deletes its image file.

---

## 6. `header()` does not stop your script

**Before:**

```php
if (isset($_GET['exit'])) {
    if (isset($_SESSION['onlineUser'])) {
        session_destroy();
        header("Location: ../View/login.php");
    }
}
```

`header('Location: ...')` only *adds a header to the response*. PHP keeps running the next lines.
In this project, the next lines only printed HTML, but in other code the next line can be a
`DELETE`. Also, if the user was not logged in, there was no redirect at all, just a blank page.

**After:**

```php
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}
```

The `never` return type tells PHP (and you) that this function never returns.

---

## 7. Small bugs that hide in "happy path" testing

These only show up when you test the *unhappy* paths:

| Bug | Fix |
|---|---|
| `foreach ($_POST["categories"] ...)` → warning when no category is selected | `(array)($_POST['categories'] ?? [])` |
| Login page linked to `register.php`, but the file is `Register.php`. It works on macOS/Windows and gives **404 on Linux servers** | Use the exact file name |
| Same username could register twice | Check before insert, plus `UNIQUE` in `schema.sql` |
| Username regex `/^[a-zA-Z-' ]*$/` allowed quotes and spaces | `/^[a-zA-Z0-9_]{3,30}$/` |
| "Image could not be uploaded" was shown even when no image was chosen | `upload_image()` returns `null` for "no file" |
| Edit page didn't show which categories were already selected | `post_category_ids()` + `selected` |
| Edit page read the post from the session (`$_SESSION['id']`, `['name']` …). Two tabs = wrong post | Load it from the URL: `UpdatePost.php?id=5` (with an ownership check) |
| Post form listed **all users'** categories | `WHERE fk_user = ?` |

> **Habit:** after "it works", try empty fields, wrong ids, someone else's ids, huge files,
> weird characters (`' " < > &`), and opening the backend URL directly.

---

## 8. Don't Repeat Yourself (DRY)

**Before:**

- `test_input()` was copy-pasted into 3 files.
- The same 15-line `<head>` + navbar was in 6 files.
- The same "show error, then unset it" block appeared about 20 times.

When you find a bug in copy-pasted code, you have to fix it in every copy, and you will miss one.

**After:**

- `Repository/helpers.php`: `e()`, `redirect()`, `require_login()`, `flash()`, `query()`
- `View/partials/header.php`: one `<head>` + navbar for all pages
- `<?= flash('nameErr') ?>` replaces each 4-line show-and-unset block

---

## 9. Secrets don't belong in git

The first commit had the real database password in `Config.php`. Changing it in a later commit
does **not** remove it. Anyone can still read it with `git log -p`.

**After:** settings come from environment variables:

```php
define('DB_PASS', getenv('DB_PASS') ?: 'password');   // 'password' is only a local default
```

```bash
DB_PASS=my-real-password php -S localhost:8000
```

> If a real password was ever pushed, treat it as leaked: **change the password**. Rewriting history
> is not enough once it is public.

---

## 10. Make the project runnable by others

The repo had no database schema, so nobody (including future me) could run it.
Now there is:

- `schema.sql`: the tables, with `UNIQUE` usernames, foreign keys, and `utf8mb4` for Persian text
- `README.md`: setup steps
- `.gitignore`: IDE files (`.idea/`) and uploaded images stay out of git

---

## Exercises

Try these yourself. Each one builds on a section above.

1. **CSRF** (§4): deleting is a plain link (`?del=5`), so another website can make your browser click it.
   Change delete to a `<form method="post">` and add a CSRF token (`bin2hex(random_bytes(32))` in the
   session plus a hidden input; compare them with `hash_equals`).
2. **Keep the form values** (§7): when validation fails, the form comes back empty. Save the typed values
   in the session and refill the inputs (escaped with `e()`).
3. **Login rate limit** (§2): block a username for 5 minutes after 5 wrong passwords.
4. **PDO** (§1): rewrite `query()` with PDO instead of mysqli. Notice that no other file has to change.
   That is the benefit of §8.
5. **Front controller**: today every file in `Repository/` is directly reachable by URL. Move them out of
   the web root and route every request through one `public/index.php`. This is the first step toward
   how frameworks like Laravel work.
6. **Tests**: install PHPUnit with Composer and write a test for the username regex and `e()`.
