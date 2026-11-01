<?php
require_once __DIR__ . '/../Repository/Config.php';
require_once __DIR__ . '/../Repository/helpers.php';
require_login();

$title = 'داشبورد';
$showNav = true;
require __DIR__ . '/partials/header.php';
?>
<div class="row p-2 justify-content-center">
    <div class="col-10">
        <?php if (isset($_SESSION['loginMessage'])): ?>
            <div class="alert alert-success"><?= flash('loginMessage') ?></div>
        <?php endif; ?>
    </div>
</div>
<div class="row p-2 justify-content-center">
    <div class="col-4 p-4">
        <div class="p-4 border"><a href="AddPost.php" class="btn btn-outline-dark">اضافه کردن پست</a></div>
    </div>
    <div class="col-4 p-4">
        <div class="p-4 border"><a href="AddCategory.php" class="btn btn-outline-dark">اضافه کردن دسته بندی</a></div>
    </div>
</div>
</body>
</html>
