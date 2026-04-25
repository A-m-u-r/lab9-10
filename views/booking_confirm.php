<div class="row row--nogutter top-line"><div class="line"></div></div>
<div class="row">
    <div class="row--small">
        <h2>Подтверждение записи</h2>
        <table class="table">
            <tbody>
                <tr><th>ФИО</th><td><?= e($user['full_name']) ?></td></tr>
                <tr><th>Вид творчества</th><td><?= e($mc['category_name']) ?></td></tr>
                <tr><th>Мастер-класс</th><td><?= e($mc['title']) ?></td></tr>
                <tr><th>Ведущий</th><td><?= e($mc['master_name']) ?></td></tr>
                <tr><th>Дата</th><td><?= e(format_date_ru($mc['date'])) ?></td></tr>
                <tr><th>Время</th><td><?= e(slot_label($mc['time_slot'])) ?></td></tr>
                <tr><th>Стоимость</th><td><?= number_format((float)$mc['price'], 2, ',', ' ') ?> ₽</td></tr>
            </tbody>
        </table>

        <form method="post" action="<?= e(url('booking_create')) ?>" class="confirm-form">
            <?= csrf_field() ?>
            <input type="hidden" name="mc_id" value="<?= (int)$mc['id'] ?>">
            <button type="submit" name="action" value="confirm" class="btn">Подтвердить запись</button>
            <button type="submit" name="action" value="cancel"  class="btn btn--ghost">Отменить</button>
        </form>
    </div>
</div>
