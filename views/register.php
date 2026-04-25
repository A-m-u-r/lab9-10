<div class="row row--nogutter top-line"><div class="line"></div></div>
<div class="row">
    <div class="row--small">
        <form method="post" action="<?= e(url('register')) ?>" class="form" novalidate id="form-register">
            <?= csrf_field() ?>
            <h2>Форма регистрации</h2>

            <div class="form-group">
                <label for="full_name">ФИО</label>
                <input type="text" id="full_name" name="full_name" value="<?= e(old('full_name')) ?>"
                       required minlength="2" maxlength="100"
                       pattern="[\p{L}\s\-']+"
                       data-rule="fullname"
                       autocomplete="name">
                <small class="hint">От 2 до 100 символов, только буквы, пробелы, дефисы.</small>
                <?php if (!empty($errors['full_name'])): ?><div class="err"><?= e($errors['full_name'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e(old('email')) ?>"
                       required maxlength="254"
                       data-rule="email"
                       autocomplete="email">
                <?php if (!empty($errors['email'])): ?><div class="err"><?= e($errors['email'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="phone">Номер телефона</label>
                <input type="tel" id="phone" name="phone" value="<?= e(old('phone')) ?>"
                       required maxlength="20"
                       placeholder="+7 999 123-45-67"
                       data-rule="phone"
                       autocomplete="tel">
                <small class="hint">Российский номер: +7 / 8 и 10 цифр.</small>
                <?php if (!empty($errors['phone'])): ?><div class="err"><?= e($errors['phone'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password"
                       required minlength="8" maxlength="72"
                       data-rule="password"
                       autocomplete="new-password">
                <small class="hint">Минимум 8 символов, буквы и цифры.</small>
                <?php if (!empty($errors['password'])): ?><div class="err"><?= e($errors['password'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirm">Повторите пароль</label>
                <input type="password" id="password_confirm" name="password_confirm"
                       required minlength="8" maxlength="72"
                       data-rule="match" data-match="password"
                       autocomplete="new-password">
                <?php if (!empty($errors['password_confirm'])): ?><div class="err"><?= e($errors['password_confirm'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label>Я регистрируюсь как</label>
                <label class="radio"><input type="radio" name="role" value="visitor" <?= old('role', 'visitor') === 'visitor' ? 'checked' : '' ?>> Посетитель</label>
                <label class="radio"><input type="radio" name="role" value="master"  <?= old('role') === 'master' ? 'checked' : '' ?>> Ведущий мастер-класса</label>
                <?php if (!empty($errors['role'])): ?><div class="err"><?= e($errors['role'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <button type="submit" class="btn">Зарегистрироваться</button>
                <a href="<?= e(url('login')) ?>" class="form__alt">У меня уже есть аккаунт</a>
            </div>
        </form>
    </div>
</div>
