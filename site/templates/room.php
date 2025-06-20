<?php date_default_timezone_set('America/Chicago'); ?>
<?php snippet('header') ?>
        <?= css('assets/css/template/room.css') ?>
    </head>
    <body>
        <h1><?= $page->title() ?></h1>
        <ul class="schedule">
            <?php snippet('schedule') ?>
        </ul>