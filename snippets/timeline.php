<?php
    $now = time();
    $minute = date('i', $now);
    $second = date('s', $now);
    $offset = ($minute < 30) ? 0 : 30;
    $timelineStart = strtotime(date('H:', $now) . $offset . ':00');
    $window = 3 * 60 * 60;  // 3 hr window

    // build your slots in 30 min increments:
    $timelineEnd = $timelineStart + $window;
        $slots = [];

    for ($t = $timelineStart; $t < $timelineEnd; $t += 1800) {
        $slots[] = date('g:ia', $t);
    }

    // Count upcoming events after the window 
    // starts at -1 to account for closing event
    $upcomingEvents = -1;
    foreach ($arrayReady as $data) {
        $start = is_int($data['start_date'])
            ? $data['start_date']
            : strtotime($data['start_date']);

        if ($start >= $timelineEnd) {
            $upcomingEvents++;
        }
    }
?>
<div class="timeline">
    <div class="time-labels font-large">
        <?php
            $timeSlot = 1;
            foreach ($slots as $label): 
        ?>
        <div class="time" style="--row:<?= $timeSlot ?>;"><?= $label ?></div>
        <?php $timeSlot += 2; ?>
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

                    // compute offset & duration (in minutes), then clamp
                    $offsetMins   = max(0, ($actualStart - $timelineStart) / 60);
                    $durationMins = max(0, ($actualEnd   - $actualStart)   / 60);

                    // Convert start/end times to grid rows for css grid (rows 1-12)
                    $startRow = floor($offsetMins / 15) + 1;
                    $endRow   = floor(($offsetMins + $durationMins) / 15) + 1;

                    // Check if event overflows
                    $isOverflow = ($end > $timelineEnd);

                    // Check if event underflows
                    $isUnderflow = ($start < $timelineStart);

                    // Assemble css classes
                    $classes = ['event'];
                    if ($isUnderflow) {
                        $classes[] = 'underflow';
                    }
                    if ($isOverflow) {
                        $classes[] = 'overflow';
                    }
                    $classAttr = implode(' ', $classes);

                ?>
                <div class="<?= $classAttr ?>" style="--start: <?= $startRow ?>; --end: <?= $endRow ?>;">
                    <?= date('g:ia', strtotime($data['start_date'])); ?> - <?= date('g:ia', strtotime($data['end_date'])); ?>: <?= htmlspecialchars($data['title']) ?>
                </div>
            <?php endforeach ?>
        <?php else: ?>
            <div class="no-events">No upcoming events</div>
        <?php endif ?>
    </div>
</div>
<?php if ($upcomingEvents > 0): ?>
    <div class="event event-footer">
        +<?= $upcomingEvents ?> Upcoming Event<?php if ($upcomingEvents > 1): ?><?= "s" ?><?php endif ?>
    </div>
<?php endif ?>