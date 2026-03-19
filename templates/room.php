<?php snippet('header-rooms') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/fonts/BlockKie-v1.0\fonts.css') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/fonts/NicoFontPack-v1.0\base\NicoClean\fonts.css') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/fonts/NicoFontPack-v1.0\base\NicoBold\fonts.css') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/fonts/NicoFontPack-v1.0\base\NicoPups\fonts.css') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/fonts/prompt-master/css/fonts.css') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/fonts/Merriweather-Sans-master/fonts.css') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/css/template/room.css') ?>
        <?php if($page->pgcolor()->bool()): ?>
            <?= css('/media/plugins/salinapl/aag-rooms/css/template/color.css') ?>
        <?php endif ?>
        <style>

        </style>
    </head>
    <body>
        <?php 
            $roomSign = $roomStatus 
                ? "Available"
                : "Occupied";
            $roomSignclr = $roomStatus
                ? "--available"
                : "--occupied";
        ?>
        <div class="headline" style="background-color: var(<?= $roomSignclr ?>);">
            <h1 class="font-headline"><?= $page->title() ?></h1>
            <span class="font-headline">-</span>
            <div class="room-status font-headline">
                <?= $roomSign ?>
            </div>
        </div>
        <?php if($page->noticetoggle()->bool()): ?>
            <p class="notice"><?= $page->notice() ?></p>
        <?php endif ?>
        <div class ="flex-horz-wrapper">
        <?php if(count($arrayReady) === 1):  ?>

        <?php else: ?>
            <div class="main">
                    <?php 
                        snippet('timeline') 
                    ?>
            </div>
        <?php endif ?>
            <div class="sidebar font-large">
                <p>
                    <?= $timeText ?>
                    
                </p>
                <div class="description"> <?= $page->description()->kirbytext() ?></div>
                <?php if ($page->pgtouch()->bool()): ?>
                    <p>Press to reserve room:
                    <a class="aag-button" href="" onclick="scheduledoc(<?= $page->pgbutton()->url() ?>)"> Reserve </a>
                    </p>
                <?php else: ?>
                    <div class ="aag-qr">
                        <p>Scan QR Code to reserve room:</p>
                    <?php if ($file = $page->files()->filterBy('extension', 'svg')->first()): ?>
                        <img src="<?= $file->url() ?>">
                    <?php endif ?>
                    </div>
                <?php endif ?>
            </div>
        </div>
        <?php if ($page->pgtouch()->bool()): ?>
            <?= js('/media/plugins/salinapl/aag-rooms/js/reservation-times.js') ?>
        <?php endif ?>