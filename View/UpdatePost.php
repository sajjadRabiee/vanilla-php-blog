<?php
require_once __DIR__ . '/../Repository/PostsCRUD.php'; // also checks login

// Load the post from the URL (?id=5), and only if it belongs to the logged-in user.
$post = find_post($db, $_GET['id'] ?? 0, $userId);
if (!$post) {
    $_SESSION['message'] = 'پست پیدا نشد';
    redirect('AddPost.php');
}
$selectedCategories = post_category_ids($db, (int)$post['id']);

$title = 'ویرایش پست';
$showNav = true;
require __DIR__ . '/partials/header.php';
?>
<div class="row justify-content-center p-2">
    <div class="col-8 border p-2">
        <form action="../Repository/PostsCRUD.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= e($post['id']) ?>">

            <?php if ($post['image']): ?>
                <div><img class="col-8" src="../images/<?= e($post['image']) ?>" alt=""></div>
            <?php endif; ?>
            <div class="input-group p-1">
                <label class="col-2" for="image">تغییر عکس : </label>
                <input class="form-control col-7" type="file" name="image" id="image" accept="image/*">
                <span class="font-weight-normal h6 col-3 text-danger"><?= flash('imageErr') ?></span>
            </div>
            <div class="input-group p-1">
                <label class="col-2" for="name">نام پست : </label>
                <input class="form-control col-7" type="text" name="name" id="name" value="<?= e($post['name']) ?>">
                <span class="font-weight-normal h6 col-3 text-danger"><?= flash('nameErr') ?></span>
            </div>
            <div class="input-group p-1">
                <label class="col-2" for="categories">موضوعات</label>
                <select class="col-7" name="categories[]" id="categories" size="3" multiple>
                    <?php while ($category = mysqli_fetch_assoc($allCategories)): ?>
                        <option value="<?= e($category['id']) ?>"
                            <?= in_array($category['id'], $selectedCategories) ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="input-group p-1">
                <label class="col-2" for="text">متن پست : </label>
                <textarea class="form-control col-7" name="text" id="text"><?= e($post['text']) ?></textarea>
                <span class="font-weight-normal h6 col-3 text-danger"><?= flash('textErr') ?></span>
            </div>
            <div class="input-group p-5 justify-content-center">
                <button class="btn btn-info btn-block col-8" type="submit" name="update">تکمیل ویرایش پست</button>
            </div>
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success">
                    <?= flash('message') ?>
                    <a href="dashboard.php">بازگشت به داشبورد</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
</body>
</html>
