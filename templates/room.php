<?php snippet('header-rooms') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/fonts/BlockKie-v1.0\fonts.css') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/fonts/NicoFontPack-v1.0\base\NicoClean\fonts.css') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/fonts/NicoFontPack-v1.0\base\NicoBold\fonts.css') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/fonts/NicoFontPack-v1.0\base\NicoPups\fonts.css') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/css/template/room.css') ?>
        <style>

        </style>
    </head>
    <body>
        <div class="main">
            <h1 class="font-headline"><?= $page->title() ?></h1>
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
                <?= $roomStatus ?>
                 
            </p>
            <p>Scan QR Code to reserve room:
            <?php if ($file = $page->files()->filterBy('extension', 'svg')->first()): ?>
                <img src="<?= $file->url() ?>">
            <?php endif ?>
        </div>