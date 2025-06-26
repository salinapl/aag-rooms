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
        $parentPage = $page->parent();
        $regularHours = $parentPage->hours()->yaml();
        $orHours = $page->orhours()->yaml();
        $closeMessage = $parentPage->closemessage();
        $today = new DateTime();
        
        foreach ($orHours as $row) {
            $specialDate = new DateTime($row['ordate']);
            $today = new DateTime();
            $tomorrow = (clone $today)->modify('+1 day');
            $todayName = $today->format('l');
            $tomorrowName = $tomorrow->format('l');
            $indexToday = array_search($todayName, array_column($regularHours, 'day'));

            if ($specialDate->format('Y-m-d') === $today->format('Y-m-d')) {
                $closeTime = $row['orclose'];
                $openTime = $row['oropen'];
                $closeMessage = $row['ormessage'];
            } elseif ($indexToday !== false) {
                    $todayRow = $regularHours[$indexToday];

                    // Handle Sat-Sun wraparound
                    $indexNext = ($indexToday + 1) % count ($regularHours);
                    $nextRow = $regularHours[$indexNext];

                    $closeTime = $today->format('Y-m-d') . ' ' . $todayRow['close'];
                    $openTime = $tomorrow->format('Y-m-d') . ' ' . $nextRow['open'];

            } else {
                // Random fallback times if no data
                $closeTime = $today->format('Y-m-d') . ' ' . '23:00';
                $openTime =  $today->format('Y-m-d') . ' ' . '08:00';
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
        array $eventsArray,
        // Set to a 90 minute gap
        int $minGap = 90 * 60,
        DateTimeImmutable $from = null
    ): ?array {
        $from       = $from ?: new DateTimeImmutable();

        // Sort a copy by raw start to get soonest‐upcoming event
        $byStart = $eventsArray;
        usort($byStart, fn($a, $b) =>
            (new DateTimeImmutable($a['start_date']))
            <=> 
            (new DateTimeImmutable($b['start_date']))
        );

        // find title of first event starting >= $from
        $soonestTitle = null;
        foreach ($byStart as $e) {
            if (new DateTimeImmutable($e['start_date']) >= $from) {
                $soonestTitle = $e['title'] ?? null;
                break;
            }
        }

        // Also compute setup/teardown–buffered status of the VERY first event
        if (isset($byStart[0])) {
            $first = $byStart[0];
            $bufStart = (new DateTimeImmutable($first['start_date']))
                            ->sub(new DateInterval('PT'.$first['setup_time'].'M'));
            $bufEnd   = (new DateTimeImmutable($first['end_date']))
                            ->add(new DateInterval('PT'.$first['teardown_time'].'M'));
            $isAfterSoonestStart = $from >= $bufStart;
            $isEventOngoing      = $from >= $bufStart && $from <= $bufEnd;
        } else {
            $isAfterSoonestStart = false;
            $isEventOngoing      = false;
        }

        // Sort original events by buffered start and scan for a gap
        usort($eventsArray, function($a, $b) {
            $aBuf = (new DateTimeImmutable($a['start_date']))
                        ->sub(new DateInterval('PT'.$a['setup_time'].'M'));
            $bBuf = (new DateTimeImmutable($b['start_date']))
                        ->sub(new DateInterval('PT'.$b['setup_time'].'M'));
            return $aBuf <=> $bBuf;
        });

        $freeStart = null;
        $freeEnd   = null;

        // Check gap *before* first buffered event
        if (!empty($eventsArray)) {
            $firstBufStart = (new DateTimeImmutable($eventsArray[0]['start_date']))
                                ->sub(new DateInterval('PT'.$eventsArray[0]['setup_time'].'M'));
            $gap = $firstBufStart->getTimestamp() - $from->getTimestamp();
            if ($firstBufStart > $from && $gap >= $minGap) {
                $freeStart = $from;
                $freeEnd   = $firstBufStart;
            }
        }

        // If not found yet, scan between events
        if ($freeStart === null) {
            $currentEnd = $from;
            foreach ($eventsArray as $e) {
                $bS = (new DateTimeImmutable($e['start_date']))
                        ->sub(new DateInterval('PT'.$e['setup_time'].'M'));
                $bE = (new DateTimeImmutable($e['end_date']))
                        ->add(new DateInterval('PT'.$e['teardown_time'].'M'));

                if ($bE <= $from) {
                    continue;
                }
                if ($bS > $currentEnd) {
                    $gap = $bS->getTimestamp() - $currentEnd->getTimestamp();
                    if ($gap >= $minGap) {
                        $freeStart = $currentEnd;
                        $freeEnd   = $bS;
                        break;
                    }
                }
                $currentEnd = max($currentEnd, $bE);
            }
        }

        // Check if current event is last in the array
        $isLastEvent = false;
        if ($soonestTitle !== null && !empty($byStart)) {
            $lastEvent = end($byStart); // Last by start_date
            $isLastEvent = ($lastEvent['title'] ?? null) === $soonestTitle;
        }

        // Single return
        if ($freeStart !== null) {
            return [
                'start_date'          => $freeStart,
                'end_date'            => $freeEnd,
                'isAfterSoonestStart' => $isAfterSoonestStart,
                'isEventOngoing'      => $isEventOngoing,
                'soonestTitle'        => $soonestTitle,
                'isLastEvent'         => $isLastEvent
            ];
        }

        return null;
    }
    
    $arrayReady = rebuildArray($jsonFull, $page);
    $nextGap = findgap($arrayReady);
    
    //TODO - Rewrite this, need it to not fire null when no upcoming events at end of the day
    // Check the variable from the function findgap to see if there is an event running
    if ($nextGap !== null) {
        if ($nextGap['isLastEvent'] && $nextGap['isEventOngoing']) {
            $roomStatus = "Room is currently occupied, and will be unavailable for the rest of the day";
        } elseif ($nextGap['isLastEvent'] && !$nextGap['isEventOngoing']) {
            $roomStatus = "Room is currently available, We will be closing at " . $nextGap['start_date']->format('g:ia');
        } elseif ($nextGap['isEventOngoing'] === false) {
            $roomStatus = "Room is currently available, will be occupied again at " . $nextGap['end_date']->format('g:ia');
        } else {
            $roomStatus = "Room is currently occupied, will be available again at " . $nextGap['start_date']->format('g:ia');
        }
    } else {
        // Fallback if no gap was found at all
        $roomStatus = "No upcoming events or availability information could be determined.";
    }
    
    return [
        'arrayReady' => $arrayReady,
        'roomStatus' => $roomStatus,
        'nextGap' => $nextGap,
    ];
};

?>