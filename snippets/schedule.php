<ul class="schedule">
    <?php foreach($arrayReady as $jsonData): ?>
        <?php
            $start_time = strtotime($jsonData['start_date']);
            $end_time = strtotime($jsonData['end_date']);
        ?>
        <li>
            <span class="time">
                <?php echo date("g:ia", $start_time) ?>
                -
                <?php echo date("g:ia", $end_time) ?>
            </span>

            <?= $jsonData['title'] ?>
        </li>
    <?php endforeach ?>
</ul>