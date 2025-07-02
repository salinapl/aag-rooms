<?php
    $now = time();
    $minute = date('i', $now);
    $second = date('s', $now);
    $offset = ($minute < 30) ? 0 : 30;
    $timelineStart = strtotime(date('H:', $now) . $offset . ':00');
    $window = $page->noticetoggle()->bool()
        ? 2.5 * 60 * 60  // Notice enabled, 2.5 hr window
        : 3 * 60 * 60;  // Notice disabled, 3 hr window
    $windowCss = $page->noticetoggle()->bool()
        ? "twohr-window"  // Notice enabled, 2.5 hr window
        : "threehr-window";  // Notice disabled, 3 hr window

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
        <?php foreach ($slots as $label): ?>
        <div><?= $label ?></div>
        <?php endforeach; ?>
    </div>
    <div class="events-column <?= $windowCss ?>">
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

                    // for some reason even x2 doesn't get the labels to line up
                    // with the font I'm using.
                    $top = $offsetMins * 2.1;
                    $height = $durationMins * 2.1;

                    // Check if event overflows
                    $isOverflow = ($end > $timelineEnd);

                    // Assemble css classes
                    $classes = ['event'];
                    if ($isOverflow) {
                        $classes[] = 'continues';
                    }
                    $classAttr = implode(' ', $classes);

                ?>
                <div class="<?= $classAttr ?>" 
                    style="top: <?= $top ?>px; height: <?= $height ?>px;">
                    <?= htmlspecialchars($data['title']) ?>
                </div>
            <?php endforeach ?>
        <?php else: ?>
            <div class="no-events">No upcoming events</div>
        <?php endif ?>
        <?php if ($upcomingEvents > 0): ?>
        <div class="event event-footer">
            +<?= $upcomingEvents ?> Upcoming Event<?php if ($upcomingEvents > 1): ?><?= "s" ?><?php endif ?>
        </div>
        <?php endif ?>
    </div>
</div>