<?php snippet('header-rooms') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/fonts/prompt-master/css/fonts.css') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/fonts/Merriweather-Sans-master/fonts.css') ?>
        <?= css('/media/plugins/salinapl/aag-rooms/css/template/room.css') ?>
        <?= 
            $page->pgcolor()->bool()
            ? css('/media/plugins/salinapl/aag-rooms/css/template/color.css')
            : css('/media/plugins/salinapl/aag-rooms/css/template/2bit.css');
        ?>
        <style>
            <?php if($page->pgcolor()->bool()):?>
            :root{
                --primary:<?= $page->aagprimary() ?>;
                --secondary:<?= $page->aagsecondary() ?>;
                --status:<?= $page->aagstatus() ?>;
                --fontprimary:<?= $page->aagprimaryfont() ?>;
                --fontsecondary:<?= $page->aagsecondaryfont() ?>;
                --fontstatus:<?= $page->aagstatusfont() ?>;
                --buttonbg:<?= $page->aagbuttonbg() ?>;
                --fontbutton:<?= $page->aagbuttonfont() ?>;
            }
            <?php endif ?>
        </style>
    </head>
    <body class="font-small">
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
            <p class="notice font-large"><?= $page->notice() ?></p>
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
            <div class="sidebar font-small">
                <p>
                    <?= $timeText ?>
                    
                </p>
                <div class="description"> <?= $page->description()->kirbytext() ?></div>
                <?php if ($page->pgtouch()->bool()): ?>
                    <p>Press to reserve room:
                        <a class="aag-button font-headline" href="<?= url('reserve', [
                            'query' => [
                                'url' => $page->pgbutton()->value()
                            ]
                        ]) ?>"> 
                            Reserve 
                        </a>
                    </p>
                <?php else: ?>
                    <div class ="aag-qr">
                        <p>Scan QR Code to reserve room:</p>
                        <?= qr($page->pgbutton()->url())->toSvg(130, '#000', '#fff', 1) ?>
                    </div>
                <?php endif ?>
            </div>
        </div>
        <?php if ($page->pgtouch()->bool()): ?>
            <?= js('/media/plugins/salinapl/aag-rooms/js/reservation-times.js') ?>
        <?php endif ?>