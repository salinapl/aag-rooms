<ul class="schedule">
    <?php foreach($json_ready as $json_data): ?>
        <?php
            $start_time = strtotime($json_data->start_date);
            $end_time = strtotime($json_data->end_date);
        ?>
        <li>
            <span class="time">
                <?php echo date("g:ia", $start_time) ?>
                -
                <?php echo date("g:ia", $end_time) ?>
            </span>

            <?php echo $json_data->title ?>
        </li>
    <?php endforeach ?>
</ul>