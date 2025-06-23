<?php date_default_timezone_set('America/Chicago'); ?>
<?php snippet('header') ?>
        <?= css('assets/css/template/room.css') ?>
        <style>

        </style>
    </head>
    <body>
        <div class="main">
            <h1><?= $page->title() ?></h1>
            <?php if($page->noticetoggle()->bool()): ?>
                <p class="notice"><?= $page->notice() ?></p>
            <?php endif ?>
            <?php if(empty($json_ready)):  ?>
                <p> The room is available all day today</p>
            <?php else: ?>
                <?php snippet ('timeline') ?>    
            <?php endif ?>
        </div>
        <div class="sidebar">
            <p>Occupied <?php echo $nextEvent ?></p>
            <p>Scan QR Code to reserve:
            <?php if ($file = $page->files()->filterBy('extension', 'svg')->first()): ?>
                <img src="<?= $file->url() ?>">
            <?php endif ?>
        </div>