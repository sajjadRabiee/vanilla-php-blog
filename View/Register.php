<?php
require_once __DIR__ . '/../Repository/UsersCRUD.php';
$title = 'ثبت نام';
$showNav = false;
require __DIR__ . '/partials/header.php';
?>
<div class="row justify-content-center p-2">
    <div class="col-6 border p-2">
        <form action="../Repository/UsersCRUD.php" method="post">
            <div class="input-group p-1">
                <label class="col" for="name">نام : </label>
                <input class="form-control col" type="text" name="name" id="name" value="">
                <span class="font-weight-normal h6 col text-danger"><?= flash('nameErr') ?></span>
            </div>
            <div class="input-group p-1">
                <label class="col" for="username">نام کاربری : </label>
                <input class="form-control col" type="text" name="username" id="username" value="">
                <span class="font-weight-normal h6 col text-danger"><?= flash('usernameErr') ?></span>
            </div>
            <div class="input-group p-1">
                <label class="col" for="email">ایمیل : </label>
                <input class="form-control col" type="email" name="email" id="email" value="">
                <span class="font-weight-normal h6 col text-danger"><?= flash('emailErr') ?></span>
            </div>
            <div class="input-group p-1">
                <label class="col" for="password">رمز عبور : </label>
                <input class="form-control col" type="password" name="password" id="password" value="">
                <span class="font-weight-normal h6 col text-danger"><?= flash('passwordErr') ?></span>
            </div>
            <div class="input-group p-5 justify-content-center">
                <button class="btn btn-primary btn-block col-6" type="submit" name="register">ثبت نام</button>
            </div>
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success">
                    <?= flash('message') ?>
                    <a href="login.php">بازگشت به صفحه ورود</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
</body>
</html>
