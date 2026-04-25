<?php
$isEdit = $mode === 'edit';
$today  = (new DateTimeImmutable('today'))->format('Y-m-d');
?>
<div class="row row--nogutter top-line"><div class="line"></div></div>
<div class="row">
    <div class="row--small">
        <form method="post"
              action="<?= e($isEdit ? url('mc_edit', ['id' => $mc['id']]) : url('mc_new')) ?>"
              class="form" novalidate id="form-mc"
              data-edit="<?= $isEdit ? '1' : '0' ?>"
              <?= $isEdit ? 'data-mc-id="' . (int)$mc['id'] . '"' : '' ?>>
            <?= csrf_field() ?>
            <h2><?= $isEdit ? 'Редактирование мастер-класса' : 'Форма добавления мастер-класса' ?></h2>

            <div class="form-group">
                <label for="category_id">Вид творчества</label>
                <select id="category_id" name="category_id" required <?= $isEdit ? 'disabled' : '' ?>>
                    <option value="">— выберите —</option>
                    <?php foreach ($categories as $c): ?>
                        <?php
                        $sel = $isEdit
                            ? ((int)$c['id'] === (int)$mc['category_id'])
                            : ((string)$c['id'] === old('category_id'));
                        ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $sel ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['category_id'])): ?><div class="err"><?= e($errors['category_id'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="title">Название мастер-класса</label>
                <input type="text" id="title" name="title"
                       value="<?= e($isEdit ? $mc['title'] : old('title')) ?>"
                       required minlength="3" maxlength="120"
                       <?= $isEdit ? 'readonly' : '' ?>>
                <?php if (!empty($errors['title'])): ?><div class="err"><?= e($errors['title'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">Описание мастер-класса</label>
                <textarea id="description" name="description" required minlength="10" maxlength="2000"
                          data-rule="length"><?= e($isEdit ? $mc['description'] : old('description')) ?></textarea>
                <small class="hint">От 10 до 2000 символов.</small>
                <?php if (!empty($errors['description'])): ?><div class="err"><?= e($errors['description'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="date">Дата</label>
                <input type="date" id="date" name="date"
                       value="<?= e($isEdit ? $mc['date'] : old('date')) ?>"
                       min="<?= e($today) ?>" required <?= $isEdit ? 'readonly' : '' ?>>
                <?php if (!empty($errors['date'])): ?><div class="err"><?= e($errors['date'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="time_slot">Время</label>
                <select id="time_slot" name="time_slot" required <?= $isEdit ? 'disabled' : '' ?>>
                    <option value="">— выберите дату —</option>
                    <?php foreach ($allowedSlots as $s):
                        $sel = $isEdit
                            ? ($s === $mc['time_slot'])
                            : ($s === old('time_slot'));
                    ?>
                        <option value="<?= e($s) ?>" <?= $sel ? 'selected' : '' ?>><?= e(slot_label($s)) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="hint">Сетка: 9–11, 11–13, 13–15, 15–17. Занятые слоты будут отключены автоматически.</small>
                <?php if (!empty($errors['time_slot'])): ?><div class="err"><?= e($errors['time_slot'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="capacity">Количество человек в группе</label>
                <input type="number" id="capacity" name="capacity"
                       value="<?= e($isEdit ? (string)$mc['capacity'] : old('capacity')) ?>"
                       required min="1" max="100" step="1"
                       <?= $isEdit ? 'readonly' : '' ?>>
                <?php if (!empty($errors['capacity'])): ?><div class="err"><?= e($errors['capacity'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="price">Стоимость (₽)</label>
                <input type="number" id="price" name="price"
                       value="<?= e($isEdit ? (string)$mc['price'] : old('price')) ?>"
                       required min="0" max="1000000" step="0.01">
                <?php if (!empty($errors['price'])): ?><div class="err"><?= e($errors['price'][0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <button type="submit" class="btn"><?= $isEdit ? 'Сохранить' : 'Создать мастер-класс' ?></button>
                <a href="<?= e(url('cabinet')) ?>" class="form__alt">Отмена</a>
            </div>
        </form>
    </div>
</div>
