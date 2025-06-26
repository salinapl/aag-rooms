<?php
    $now = time();
    $minute = date('i', $now);
    $second = date('s', $now);
    $offset = ($minute < 30) ? 0 : 30;
    $timelineStart = strtotime(date('H:', $now) . $offset . ':00');
    $timelineEnd = $timelineStart + (3 * 60 * 60);
    $slots = [];

    for ($t = $timelineStart; $t < $timelineEnd; $t += 1800) {
        $slots[] = date('g:ia', $t);
    }
?>
<div class="timeline">
    <div class="time-labels">
        <?php foreach ($slots as $label): ?>
        <div><?= $label ?></div>
        <?php endforeach; ?>
    </div>
    <div class="events-column">
        <?php if (!empty($arrayReady)): ?>
            <?php foreach($arrayReady as $jsonData): ?>
                <?php 
                    if ($jsonData['end_date'] <= $timelineStart || $jsonData['start_date'] >= $timelineEnd) continue;
                    $startOffsetMin = max(0, ($jsonData['start_date'] - $timelineStart) / 60);
                    $durationMin = ($jsonData['end_date'] - $jsonData['start_date']) / 60;

                    $top = $startOffsetMin * 2;
                    $height = $durationMin * 2;
                ?>
                <div class="event" style="top: <?= $top ?>px; height: <?= $height ?>px;">
                    <?= htmlspecialchars($jsonData['title']) ?>
                </div>
            <?php endforeach ?>
        <?php else: ?>
            <div class="no-events">No upcoming events</div>
        <?php endif ?>
    </div>
</div>