<?php

return function($page) {
    // Set the default timezone 
    // TODO - make this a variable set in the interface
    date_default_timezone_set('America/Chicago');    

    // Pull in the rest of the page configs
    $auth_url = $page->authurl();
    $feedUrl = $page->feedurl();
    $feedFlags = $page->feedflags();
    $json_url = $feedUrl . $feedFlags;

    // Pull in the oauth info from the env file
    $lc_id = env('LC_API_ID');
    $lc_secret = env('LC_API_SECRET');
    $lc_user =  env('LC_API_USER');
    $lc_pass =  env('LC_API_PASS');

    // Authorization code courtesy of 'https://auth0.com/docs/get-started/authentication-and-authorization-flow/client-credentials-flow/call-your-api-using-the-client-credentials-flow'
    $curl = curl_init();

    curl_setopt_array($curl, [
    CURLOPT_URL => "$auth_url",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id=$lc_id&client_secret=$lc_secret&username=$lc_user&password=$lc_pass",
    CURLOPT_HTTPHEADER => [
        "content-type: application/x-www-form-urlencoded"
    ],
    ]);

    $lc_token_array = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
    echo "cURL Error #:" . $err;
    } else {

    }

    //decode json token array and extract access token
    $lc_array_decode = json_decode($lc_token_array);
    $lc_token = $lc_array_decode->access_token;

    $curl = curl_init();

    curl_setopt_array($curl, [
    CURLOPT_URL => "$json_url",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
        "authorization: Bearer $lc_token",
        "content-type: application/json"
    ],
    ]);

    $jsonFull = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
    echo "cURL Error #:" . $err;
    } else {

    }
    // End Auth0 contributed code

    // Decodes, cleans up, then rebuilds the json array
    // Need to pull in page variables for some bits to work
    function rebuildArray($json, $page) {
        // List of keys to keep in the array then 
        // flip array so keys not in array are removed
        $keptKeys = [
            'title', 
            'id', 
            'public', 
            'setup_time', 
            'start_date', 
            'end_date', 
            'teardown_time', 
            'moderation_state'
        ];
        $keptKeys = array_flip($keptKeys);

        // Pull in the array from the website
        $jsonArray = json_decode($json, true);

        // Filter out cancelled events from the array and reset array index
        $jsonArray = array_values(
            array_filter(
                $jsonArray, 
                fn($item) => ($item['moderation_state'] ?? null) !== 'cancelled'
            )
        );
        $jsonArray = array_map(
            fn(array $row) => array_intersect_key($row, $keptKeys),
            $jsonArray
        );

        // Locate private events and rename them and add the event ID
        foreach ($jsonArray as & $item) {
            if (isset($item['public']) 
                && ($item['public'] === false || $item['public'] === 'false')
            ) {
                $eventid = $item['id'];
                $item['title'] = "Private Reservation ({$eventid})";
            }
        }
        unset($item);
        
        // Pull in the parent page closing times
        // $parentPage = $page->parent();
        $regularHours = (array)$page->parent()->hours()->yaml();
        $orHours = (array)$page->orhours()->yaml();
        $closeMessage = (string)$page->parent()->closemessage();
        
        // Prep today and tommorow variables
        $today = new DateTime();
        $tomorrow = (clone $today)->modify('+1 day');
        $todayName = $today->format('l');
        $tomorrowName = $tomorrow->format('l');
        
        // Fallback random close times 
        // if close time can't be set for some reason
        $closeTime = $today->format('Y-m-d') . ' 23:00';
        $openTime  = $today->format('Y-m-d') . ' 08:00';

        // Regular hours
        if (! empty($regularHours)) {
            // Look for today’s row
            $idx = array_search($todayName, array_column($regularHours, 'day'));
            if ($idx !== false) {
                $todayRow = $regularHours[$idx];
                // wrap to Sunday if Saturday
                $nextRow  = $regularHours[($idx + 1) % count($regularHours)];
                
                $closeTime = $today->format('Y-m-d') . ' ' . $todayRow['close'];
                $openTime  = $tomorrow->format('Y-m-d') . ' ' . $nextRow['open'];
            }
        }

        // Override Regular hours
        foreach ($orHours as $row) {
            // skip if no date or unparseable
            if (empty($row['ordate'])) {
                continue;
            }
            $specialDate = DateTime::createFromFormat('Y-m-d', $row['ordate']);
            if (! $specialDate) {
                continue;
            }
            // if it really is _today_, take it and stop
            if ($specialDate->format('Y-m-d') === $today->format('Y-m-d')) {
                $closeTime    = $row['orclose'];
                $openTime     = $row['oropen'];
                $closeMessage = trim((string)$row['ormessage']) ?: $closeMessage;
                break;
            }
        }

        // Structure the closing event into an array
        $closeArray = [
            'title' => trim((string)$closeMessage) ?: 'Closed',
            'setup_time' => 0,
            'start_date' => $closeTime,
            'end_date' => $openTime,
            'teardown_time' => 0,
            'moderation_state' => 'approved'
        ];
        // Append the closing event to the end of the array
        $jsonArray[] = $closeArray;
        
        $eventsArray = $jsonArray;
        return $eventsArray;
    }

    // Function to find the next gap in upcoming events greater than 90 minutes
    function findGap(
        array $events,
        int $minGap = 90*60,
        DateTimeImmutable $now = null
    ): ?array {
        $now = $now ?: new DateTimeImmutable;

        // build buffered windows
        $bufs = array_map(function($e) {
            $s = (new DateTimeImmutable($e['start_date']))
                    ->sub(new DateInterval('PT'.$e['setup_time'].'M'));
            $t = (new DateTimeImmutable($e['end_date']))
                    ->add(new DateInterval('PT'.$e['teardown_time'].'M'));
            return ['event'=>$e,'bufStart'=>$s,'bufEnd'=>$t];
        }, $events);

        // find an ongoing event
        $ongoing = null;
        foreach ($bufs as $b) {
            if ($b['bufStart'] <= $now && $now <= $b['bufEnd']) {
                $ongoing = $b;   // keep the whole buffer record
                break;
            }
        }

        // sort by bufStart so we can find the next future one
        usort($bufs, fn($a,$b) => $a['bufStart'] <=> $b['bufStart']);

        // if nothing’s ongoing, find the next event-in-future
        $nextEvt = null;
        if (!$ongoing) {
            foreach ($bufs as $b) {
                if ($b['bufStart'] > $now) {
                    $nextEvt = $b;
                    break;
                }
            }
        }

        //    if an event is ongoing > gap starts when it ends
        //    else if next event exists > gap starts now
        //    else > gap starts after last teardown
        if ($ongoing) {
            $gapStart = $ongoing['bufEnd'];
        } elseif ($nextEvt) {
            $gapStart = $now;
        } else {
            // no future events at all
            $last = end($bufs);
            $gapStart = $last['bufEnd'];
        }

        // gap end is the next event’s bufStart, or null if none
        $gapEnd = $nextEvt['bufStart'] ?? null;

        // if gap is too small, bail out (no gap)
        if ($gapEnd && ($gapEnd->getTimestamp() - $gapStart->getTimestamp()) < $minGap) {
            return null;
        }

        // is this the last event of the day?
        $hasLater = false;
        foreach ($events as $e) {
            if (new DateTimeImmutable($e['start_date']) > $now) {
                $hasLater = true;
                break;
            }
        }
        $isLast = !$hasLater;

        return [
        'start_date'       => $gapStart,
        'end_date'         => $gapEnd,
        'isEventOngoing'   => $ongoing !== null,
        'isLastEvent'      => $isLast,
        ];
    }

    
    $arrayReady = rebuildArray($jsonFull, $page);

    // if the only event in here is the close‐event
    if (count($arrayReady) === 1) {
        $only   = $arrayReady[0];
        $closeT = new DateTimeImmutable($only['start_date']);
        $roomStatus = "Room is available until " . $closeT->format('g:ia');
    }
    else {
        $nextGap = findGap($arrayReady);
        if ($nextGap !== null) {
            // Currently occupied?
            if ($nextGap['isEventOngoing']) {
                if ($nextGap['isLastEvent']) {
                    $roomStatus = "Room is currently occupied, and will be unavailable for the rest of the day";
                } else {
                    $roomStatus = "Room is currently occupied, will be available again at "
                                . $nextGap['start_date']->format('g:ia');
                }

            // Currently free
            } else {
                if ($nextGap['isLastEvent']) {
                    $roomStatus = "Room is currently available; we will be closing at "
                                . $nextGap['start_date']->format('g:ia');
                } else {
                    $roomStatus = "Room is currently available; will be occupied again at "
                                . $nextGap['end_date']->format('g:ia');
                }
            }

        } else {
            // no gap at all
            $roomStatus = "No upcoming events or availability information could be determined.";
        }
    }  

    return [
        'arrayReady' => $arrayReady,
        'roomStatus' => $roomStatus
    ];
};

?>