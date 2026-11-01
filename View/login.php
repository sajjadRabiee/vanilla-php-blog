<?php
require_once __DIR__ . '/../Repository/UsersCRUD.php';
$title = 'ورود';
$showNav = false;
require __DIR__ . '/partials/header.php';
?>
<div class="row p-2 justify-content-center">
    <div class="col-10">
        <div class="alert alert-info">برای استفاده از امکانات لطفا وارد سیستم شوید .</div>
    </div>
</div>
<div class="row justify-content-center p-2">
    <div class="col-8 border p-2">
        <form action="../Repository/UsersCRUD.php" method="post">
            <div class="input-group p-1">
                <label class="col-2" for="username">نام کاربری : </label>
                <input class="form-control col-8" type="text" name="username" id="username" value="">
                <span class="font-weight-normal h6 col-2 text-danger"><?= flash('usernameErr') ?></span>
            </div>
            <div class="input-group p-1">
                <label class="col-2" for="password">رمز ورود : </label>
                <input class="form-control col-8" type="password" name="password" id="password" value="">
                <span class="font-weight-normal h6 col-2 text-danger"><?= flash('passwordErr') ?></span>
            </div>
            <div class="input-group p-5 justify-content-center">
                <button class="btn btn-success btn-block col-9" type="submit" name="login">ورود</button>
            </div>
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-warning"><?= flash('message') ?></div>
            <?php endif; ?>
            <a href="Register.php">حساب کاربری ندارید؟ ثبت نام</a>
        </form>
    </div>
</div>
</body>
</html>
