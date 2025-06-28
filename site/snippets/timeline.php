<?php
    $now = time();
    $minute = date('i', $now);
    $second = date('s', $now);
    $offset = ($minute < 30) ? 0 : 30;
    $timelineStart = strtotime(date('H:', $now) . $offset . ':00');
    $window = $page->noticetoggle()->bool()
        ? 2.5 * 60 * 60  // Notice enabled, 2 hr 30 min window
        : 3 * 60 * 60;  // Notice disabled, 3 hr window
    
    // build your slots in 30 min increments:
    $timelineEnd = $timelineStart + $window;
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
            <?php foreach($arrayReady as $data): ?>
                <?php 
                    $start = is_int($data['start_date'])
                            ? $data['start_date']
                            : strtotime($data['start_date']);
                    $end   = is_int($data['end_date'])
                            ? $data['end_date']
                            : strtotime($data['end_date']);
                    if ($end <= $timelineStart || $start >= $timelineEnd) continue;

                    $actualStart = max($start, $timelineStart);
                    $actualEnd   = min($end,   $timelineEnd);

                    // 4) compute offset & duration (in minutes), then clamp ≥ 0
                    $offsetMins   = max(0, ($actualStart - $timelineStart) / 60);
                    $durationMins = max(0, ($actualEnd   - $actualStart)   / 60);

                    // $startOffsetMin = max(0, ($start - $timelineStart) / 60);
                    // $durationMin = ($end - $start) / 60;

                    $top = $offsetMins * 2;
                    $height = $durationMins * 2;
                ?>
                <div class="event" style="top: <?= $top ?>px; height: <?= $height ?>px;">
                    <?= htmlspecialchars($data['title']) ?>
                </div>
            <?php endforeach ?>
        <?php else: ?>
            <div class="no-events">No upcoming events</div>
        <?php endif ?>
    </div>
</div>