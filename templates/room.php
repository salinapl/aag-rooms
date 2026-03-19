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
        <div class ="flex-horz-wrapper">
            <div class="main">
                <?php if($page->noticetoggle()->bool()): ?>
                    <p class="notice"><?= $page->notice() ?></p>
                <?php endif ?>
                <?php if(count($arrayReady) === 1):  ?>
                    <div class="description"> <?= $page->description()->kirbytext() ?></div>
                <?php else: ?>
                    <?php 
                        snippet('timeline') 
                    ?>
                <?php endif ?>
            </div>
            <div class="sidebar font-large">
                <p>
                    <?= $timeText ?>
                    
                </p>
                <?php if ($page->pgtouch()->bool()): ?>
                <p>Press to reserve room:
                <a class="aag-button" href="<?= $page->pgbutton()->url() ?>"> Reserve </a>
                </p>
                <?php else: ?>
                <p>Scan QR Code to reserve room:
                <?php if ($file = $page->files()->filterBy('extension', 'svg')->first()): ?>
                    <img src="<?= $file->url() ?>">
                <?php endif ?>
                </p>
                <?php endif ?>
            </div>
        </div>