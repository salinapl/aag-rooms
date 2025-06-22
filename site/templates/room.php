<?php date_default_timezone_set('America/Chicago'); ?>
<?php snippet('header') ?>
        <?= css('assets/css/template/room.css') ?>
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
                <ul class="schedule">
                    <?php foreach($json_ready as $json_data): ?>
                        <?php
                            $start_time = strtotime($json_data->start_date);
                            $end_time = strtotime($json_data->end_date);
                        ?>
                        <li>
                            <span class="time">
                                <?php echo date("g:ia", $start_time) ?>
                                &nbsp;-&nbsp;
                                <?php echo date("g:ia", $end_time) ?>
                            </span>
                            &nbsp;
                            <?php echo $json_data->title ?>
                        </li>
                    <?php endforeach ?>
                </ul>
            <?php endif ?>
        </div>
        <figure class="sidebar">
            <p>Occupied <?php echo $nextEvent ?></p>
            <?php if ($file = $page->files()->filterBy('extension', 'svg')->first()): ?>
                <img src="<?= $file->url() ?>">
            <?php endif ?>
        </figure>