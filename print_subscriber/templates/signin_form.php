<?= TWXHtmlUtils::startForm('signin', '', 'GET') ?>

    <?= TWXHtmlUtils::inputHidden('do', ['value' => 'signin']) ?>

    <p>
        <?= TWXHtmlUtils::label('username', 'Username') ?>
        <br/>
        <?= TWXHtmlUtils::inputText('username') ?>
    </p>

    <p>
        <?= TWXHtmlUtils::label('password', 'Password') ?>
        <br/>
        <?= TWXHtmlUtils::inputPassword('password') ?>
    </p>

    <p class="">
        <?= TWXHtmlUtils::inputSubmit('submit', 'Login', ['class' => 'button']) ?>
    </p>

<?= TWXHtmlUtils::endForm() ?>
