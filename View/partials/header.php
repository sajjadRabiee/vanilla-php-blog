<?php
/**
 * Shared top of every page. Set these before including it:
 * @var string $title   page title
 * @var bool   $showNav show the navbar (only for logged-in pages)
 */
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.rtlcss.com/bootstrap/v4.5.3/css/bootstrap.min.css"
          integrity="sha384-JvExCACAZcHNJEc7156QaHXTnQL3hQBixvj5RV5buE7vgnNEzzskDtx9NQ4p6BJe" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
            integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
            crossorigin="anonymous"></script>
    <script src="https://cdn.rtlcss.com/bootstrap/v4.5.3/js/bootstrap.bundle.min.js"
            integrity="sha384-40ix5a3dj6/qaC7tfz0Yr+p9fqWLzzAXiwxVLt9dw7UjQzGYw6rWRhFAnRapuQyK"
            crossorigin="anonymous"></script>
    <title><?= e($title) ?></title>
</head>
<body class="container">
<?php if (!empty($showNav)): ?>
    <nav class="navbar navbar-light bg-light">
        <a class="navbar-brand">پروژه CRUD</a>
        <span class="navbar-text"><?= e($_SESSION['onlineUser']['name'] ?? '') ?></span>
        <div class="form-inline">
            <a href="dashboard.php" class="btn btn-secondary my-2 my-sm-0 mx-1">داشبورد</a>
            <a href="../Repository/UsersCRUD.php?exit" class="btn btn-outline-danger my-2 my-sm-0">خروج</a>
        </div>
    </nav>
<?php endif; ?>
