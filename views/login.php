<div class="row row--nogutter top-line"><div class="line"></div></div>
<div class="row">
    <div class="row--small">
        <form method="post" action="<?= e(url('login')) ?>" class="form" novalidate id="form-login">
            <?= csrf_field() ?>
            <h2>Вход</h2>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e(old('email')) ?>"
                       required maxlength="254" data-rule="email" autocomplete="username">
                <?php if (!empty($errors['email'])): ?><div class="err"><?= e($errors['email'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password"
                       required maxlength="72" autocomplete="current-password">
                <?php if (!empty($errors['password'])): ?><div class="err"><?= e($errors['password'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <button type="submit" class="btn">Войти</button>
                <a href="<?= e(url('register')) ?>" class="form__alt">Регистрация</a>
            </div>
        </form>
    </div>
</div>
