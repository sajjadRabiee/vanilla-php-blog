<?php
require_once __DIR__ . '/../Repository/CategoriesCRUD.php'; // also checks login
$title = 'دسته بندی ها';
$showNav = true;
require __DIR__ . '/partials/header.php';
?>
<div class="row justify-content-center p-2">
    <div class="col-8 border p-2">
        <form action="../Repository/CategoriesCRUD.php" method="post">
            <div class="input-group p-1">
                <label class="col-3" for="name">نام دسته بندی : </label>
                <input class="form-control col-6" type="text" name="name" id="name" value="">
                <span class="font-weight-normal h6 col-3 text-danger"><?= flash('nameErr') ?></span>
            </div>
            <div class="input-group p-5 justify-content-center">
                <button class="btn btn-warning col-6" type="submit" name="save">اضافه کردن دسته بندی جدید</button>
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
                <th>نام</th>
                <th>حذف دسته بندی</th>
            </tr>
            </thead>
            <?php while ($row = mysqli_fetch_assoc($allEntities)): ?>
                <tr>
                    <td><?= e($row['name']) ?></td>
                    <td>
                        <a href="../Repository/CategoriesCRUD.php?del=<?= e($row['id']) ?>"
                           class="btn btn-danger" onclick="return confirm('حذف شود؟')">حذف</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
