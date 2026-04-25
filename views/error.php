<div class="row row--nogutter top-line"><div class="line"></div></div>
<div class="row">
    <div class="row--small">
        <h2>Ошибка</h2>
        <p><?= e($message ?? 'Что-то пошло не так.') ?></p>
        <p><a href="<?= e(url('home')) ?>" class="btn btn--small">На главную</a></p>
    </div>
</div>
