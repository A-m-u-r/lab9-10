<div class="row">
    <div class="hover"></div>
    <div class="title"></div>
    <div class="row--small grid between">
        <div class="content driver-page">
            <div class="driver-page-photo">
                <img src="assets/photos/<?= e($user['photo'] ?? 'master-1.jpg') ?>" alt="<?= e($user['full_name']) ?>">
            </div>
            <div class="driver-page-name"><?= e($user['full_name']) ?></div>
            <div class="driver-page-text">
                <div class="driver-page-my">Мои мастер-классы</div>
                <?php if (!$items): ?>
                    <p class="muted">Вы ещё не создали ни одного мастер-класса.</p>
                <?php else: ?>
                    <table class="driver-page-table">
                        <tbody>
                        <?php foreach ($items as $mc): ?>
                            <tr>
                                <td>
                                    <?= e(format_date_ru($mc['date'])) ?><br>
                                    <?= e(slot_label($mc['time_slot'])) ?>
                                </td>
                                <td>
                                    <b><?= e($mc['title']) ?></b><br>
                                    <span class="muted"><?= e($mc['category_name']) ?></span><br>
                                    Записано: <?= (int)$mc['booked'] ?> / <?= (int)$mc['capacity'] ?>,
                                    стоимость: <?= number_format((float)$mc['price'], 2, ',', ' ') ?> ₽
                                    <div class="actions">
                                        <a href="<?= e(url('mc_view', ['id' => $mc['id']])) ?>" class="btn btn--small">Участники</a>
                                        <a href="<?= e(url('mc_edit', ['id' => $mc['id']])) ?>" class="btn btn--small">Редактировать</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <div class="driver-page-btn-wrapper">
                <a href="<?= e(url('mc_new')) ?>" class="driver-page-btn btn">Добавить мастер-класс</a>
            </div>
        </div>
        <ul class="menu">
            <?php foreach (db()->query('SELECT id, name FROM categories ORDER BY name') as $c): ?>
                <li><a href="<?= e(url('category', ['id' => $c['id']])) ?>"><?= e($c['name']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
