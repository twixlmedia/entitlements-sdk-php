<!DOCTYPE html>

<html>

<head>

    <title><?= $title ?></title>

    <?= TWXHtmlUtils::css('css/style.css') ?>

    <?= TWXHtmlUtils::charset() ?>
    <?= TWXHtmlUtils::viewport() ?>

</head>

<body>

    <h1><?= $title ?></h1>

    <div class="body">
        <?= $twxContentForLayout ?>
    </div>

</body>

</html>
