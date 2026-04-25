<?php
$__current = current_user();
$__success = flash_get('success');
$__error   = flash_get('error');
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($__title) ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>
<body class="<?= e($__bodyClass ?? '') ?>">
<div class="header">
    <div class="row grid middle between">
        <div class="logo"><a href="<?= e(url('home')) ?>"><img src="assets/img/logo.png" alt="logo"></a></div>
        <div class="title">Клуб любителей творчества «ОчУмелые ручки»</div>
        <div class="auth">
            <?php if ($__current): ?>
                <span class="auth__name"><?= e($__current['full_name']) ?>
                    <span class="auth__role">(<?= $__current['role'] === 'master' ? 'ведущий' : 'посетитель' ?>)</span>
                </span>
                <?php if ($__current['role'] === 'master'): ?>
                    <a href="<?= e(url('cabinet')) ?>">Кабинет</a>
                <?php endif; ?>
                <form action="<?= e(url('logout')) ?>" method="post" class="auth__logout">
                    <?= csrf_field() ?>
                    <button type="submit" class="auth__link-btn">Выход</button>
                </form>
            <?php else: ?>
                <a href="<?= e(url('login')) ?>">Вход</a>
                <span class="auth__sep">/</span>
                <a href="<?= e(url('register')) ?>">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="row row--nogutter">
    <div class="menu-burger">
        <div class="burger"><div></div><div></div><div></div></div>
    </div>
</div>
<?php if ($__success): ?>
    <div class="row"><div class="row--small flash flash--ok"><?= e($__success) ?></div></div>
<?php endif; ?>
<?php if ($__error): ?>
    <div class="row"><div class="row--small flash flash--err"><?= e($__error) ?></div></div>
<?php endif; ?>
<div class="main">
