<?php
require_once __DIR__ . '/../Repository/PostsCRUD.php'; // also checks login
$title = 'پست ها';
$showNav = true;
require __DIR__ . '/partials/header.php';
?>
<div class="row justify-content-center p-2">
    <div class="col-8 border p-2">
        <form action="../Repository/PostsCRUD.php" method="post" enctype="multipart/form-data">
            <div class="input-group p-1">
                <input class="form-control" type="file" name="image" accept="image/*">
                <span class="font-weight-normal h6 text-danger"><?= flash('imageErr') ?></span>
            </div>
            <div class="input-group p-1">
                <label class="col-2" for="name">نام پست : </label>
                <input class="form-control col-7" type="text" name="name" id="name" value="">
                <span class="font-weight-normal h6 col-3 text-danger"><?= flash('nameErr') ?></span>
            </div>
            <div class="input-group p-1">
                <label class="col-2" for="categories">موضوعات</label>
                <select class="col-7" name="categories[]" id="categories" size="3" multiple>
                    <?php while ($category = mysqli_fetch_assoc($allCategories)): ?>
                        <option value="<?= e($category['id']) ?>"><?= e($category['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="input-group p-1">
                <label class="col-2" for="text">متن پست : </label>
                <textarea class="form-control col-7" name="text" id="text"></textarea>
                <span class="font-weight-normal h6 col-3 text-danger"><?= flash('textErr') ?></span>
            </div>
            <div class="input-group p-5 justify-content-center">
                <button class="btn btn-info btn-block col-8" type="submit" name="save">ارسال و ثبت پست</button>
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
<div class="row justify-content-center p-2">
    <div class="col-10 p-1">
        <table class="table">
            <thead>
            <tr>
                <th>تصویر</th>
                <th>نام</th>
                <th>متن</th>
                <th colspan="2">عملیات ها</th>
            </tr>
            </thead>
            <?php while ($post = mysqli_fetch_assoc($allEntities)): ?>
                <tr>
                    <td>
                        <?php if ($post['image']): ?>
                            <img src="../images/<?= e($post['image']) ?>" alt="" height="30">
                        <?php endif; ?>
                    </td>
                    <td><?= e($post['name']) ?></td>
                    <td><?= e($post['text']) ?></td>
                    <td>
                        <a href="UpdatePost.php?id=<?= e($post['id']) ?>" class="btn btn-primary">ویرایش</a>
                    </td>
                    <td>
                        <a href="../Repository/PostsCRUD.php?del=<?= e($post['id']) ?>"
                           class="btn btn-danger" onclick="return confirm('حذف شود؟')">حذف</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
