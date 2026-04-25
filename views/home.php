<div class="row">
    <div class="hover"></div>
    <div class="title">ОчУмелые ручки</div>
    <div class="row--small grid between">
        <div class="content">
            <h2>Добро пожаловать!</h2>
            <p>Мы — клуб любителей ручного творчества. Здесь мастера проводят увлекательные занятия по архитектурному моделированию, кулинарии, резьбе по дереву и многому другому.</p>
            <p>Выберите интересующий вид творчества в меню справа, посмотрите расписание и запишитесь на ближайший мастер-класс. Если вы — ведущий, войдите в систему, чтобы добавить собственное расписание.</p>

            <?php if ($user && !empty($myBookings)): ?>
                <h2 class="mt">Мои записи</h2>
                <table class="table">
                    <thead>
                        <tr><th>Дата</th><th>Время</th><th>Вид творчества</th><th>Мастер-класс</th><th>Ведущий</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($myBookings as $b): ?>
                        <tr>
                            <td><?= e(format_date_ru($b['date'])) ?></td>
                            <td><?= e(slot_label($b['time_slot'])) ?></td>
                            <td><?= e($b['category_name']) ?></td>
                            <td><?= e($b['title']) ?></td>
                            <td><?= e($b['master_name']) ?></td>
                            <td>
                                <form action="<?= e(url('booking_cancel')) ?>" method="post" class="inline-form" onsubmit="return confirm('Отменить запись?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                                    <button type="submit" class="btn btn--small">Отменить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ($user): ?>
                <p class="muted mt">Вы пока не записаны ни на один мастер-класс.</p>
            <?php endif; ?>
        </div>
        <ul class="menu">
            <?php foreach ($categories as $c): ?>
                <li><a href="<?= e(url('category', ['id' => $c['id']])) ?>"><?= e($c['name']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
