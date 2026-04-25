<div class="row row--nogutter top-line"><div class="line"></div></div>
<div class="row">
    <div class="row--small">
        <h2><?= e($mc['title']) ?></h2>
        <p class="muted"><?= e($categoryName) ?> · <?= e(format_date_ru($mc['date'])) ?> · <?= e(slot_label($mc['time_slot'])) ?></p>
        <p><?= nl2br(e($mc['description'])) ?></p>
        <p>
            Стоимость: <b><?= number_format((float)$mc['price'], 2, ',', ' ') ?> ₽</b>
            · Записано: <b><?= count($participants) ?> / <?= (int)$mc['capacity'] ?></b>
        </p>

        <h2 class="mt">Участники</h2>
        <?php if (!$participants): ?>
            <p class="muted">Пока никто не записался.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr><th>#</th><th>ФИО</th><th>Email</th><th>Телефон</th><th>Записан</th></tr>
                </thead>
                <tbody>
                <?php foreach ($participants as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($p['full_name']) ?></td>
                        <td><?= e($p['email']) ?></td>
                        <td><?= e($p['phone']) ?></td>
                        <td><?= e($p['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p class="mt"><a href="<?= e(url('cabinet')) ?>" class="btn btn--small">Назад в кабинет</a></p>
    </div>
</div>
