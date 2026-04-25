<div class="row">
    <div class="hover"></div>
    <div class="title"><?= e($category['name']) ?></div>
    <div class="row--small grid between">
        <div class="content">
            <img src="assets/photos/<?= e($category['image']) ?>" alt="<?= e($category['name']) ?>" class="content__img">
            <p><?= nl2br(e($category['description'])) ?></p>
        </div>
        <ul class="menu">
            <?php foreach ($categories as $c): ?>
                <li><a href="<?= e(url('category', ['id' => $c['id']])) ?>"><?= e($c['name']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="row shedule">
        <div class="row--small">
            <h2>Расписание</h2>
            <?php if (!$items): ?>
                <p>Пока нет запланированных мастер-классов.</p>
            <?php endif; ?>
            <div class="drivers">
                <?php foreach ($items as $mc):
                    $free  = (int)$mc['capacity'] - (int)$mc['booked'];
                    $alreadyBooked = in_array((int)$mc['id'], $userBookings, true);
                    $isOwn = $user && $user['role'] === 'master' && (int)$user['id'] === (int)$mc['master_id'];
                ?>
                    <div class="driver grid">
                        <div class="driver-left grid">
                            <div class="driver-photo">
                                <img src="assets/photos/<?= e($mc['master_photo'] ?? 'master-1.jpg') ?>" alt="<?= e($mc['master_name']) ?>">
                            </div>
                            <div class="driver-text">
                                <div class="driver-name"><?= e($mc['master_name']) ?></div>
                                <div class="driver-name driver-name--mc"><?= e($mc['title']) ?></div>
                                <div class="driver-desc"><?= nl2br(e($mc['description'])) ?></div>
                                <div class="driver-meta">
                                    <span>Стоимость: <b><?= number_format((float)$mc['price'], 2, ',', ' ') ?> ₽</b></span>
                                    <span>Свободных мест: <b><?= $free ?> / <?= (int)$mc['capacity'] ?></b></span>
                                </div>
                            </div>
                        </div>
                        <div class="driver-right">
                            <?php if (!$user): ?>
                                <a href="<?= e(url('login')) ?>" class="driver-btn">Войдите для записи</a>
                            <?php elseif ($isOwn): ?>
                                <span class="driver-tag">Ваш мастер-класс</span>
                            <?php elseif ($alreadyBooked): ?>
                                <span class="driver-tag">Вы записаны</span>
                            <?php elseif ($free <= 0): ?>
                                <span class="driver-tag driver-tag--full">Мест нет</span>
                            <?php else: ?>
                                <a href="<?= e(url('booking_confirm', ['mc_id' => $mc['id']])) ?>" class="driver-btn">записаться</a>
                            <?php endif; ?>
                            <div class="driver-time"><?= e(format_date_ru($mc['date'])) ?><br> <?= e(slot_label($mc['time_slot'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
