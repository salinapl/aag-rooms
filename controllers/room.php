<?php

return function($page) {
    // Set the default timezone 
    // TODO - make this a variable set in the interface
    date_default_timezone_set('America/Chicago');    

    // Pull in the rest of the page configs
    if ($page->orauthurltoggle()->bool() == true) {
        $authUrl = $page->authurl();
    } else {
        $authUrl = $page->parent()->authurl();
    }
    if ($page->orfeedurltoggle()->bool() == true) {
        $feedUrl = $page->feedurl();
    } else {
        $feedUrl = $page->parent()->feedurl();
    }
    
    $feedFlags = $page->feedflags();
    $jsonUrl = $feedUrl . $feedFlags;

    // Pull in the oauth info from the env file
    $lc_id = env('LC_API_ID');
    $lc_secret = env('LC_API_SECRET');
    $lc_user =  env('LC_API_USER');
    $lc_pass =  env('LC_API_PASS');

    // Authorization code courtesy of 'https://auth0.com/docs/get-started/authentication-and-authorization-flow/client-credentials-flow/call-your-api-using-the-client-credentials-flow'
    $curl = curl_init();

    curl_setopt_array($curl, [
    CURLOPT_URL => "$authUrl",
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
    CURLOPT_URL => "$jsonUrl",
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

    // Example json array data start

    // $jsonFull = null;
    // $jsonFull = F::read('/media/plugins/salinapl/aag-rooms/json/test_data.json');

    // Example json array data end

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
    ) {
        $now = $now ?: new DateTimeImmutable;

        // Add setup/teardown to event length
        $padEvents = array_map(function($e){
            $s = (new DateTimeImmutable($e['start_date']))
                    ->sub(new DateInterval('PT'.$e['setup_time'].'M'));
            $t = (new DateTimeImmutable($e['end_date']))
                    ->add(new DateInterval('PT'.$e['teardown_time'].'M'));
            return ['event'=>$e,'padStart'=>$s,'padEnd'=>$t];
        }, $events);

        usort($padEvents, fn($a,$b) => $a['padStart'] <=> $b['padStart']);

        // Set ongoing event if true
        $isEventOngoing = false;
        foreach ($padEvents as $e) {
            if (!$isEventOngoing && $e['padStart'] <= $now && $now <= $e['padEnd']) {
                $isEventOngoing = true;
                break;
            }
        }

        $gaps = [];

        for ($i = 0, $n = count($padEvents) - 1; $i < $n; $i++) {
            $end = $padEvents[$i]['padEnd'];
            $start = $padEvents[$i+1]['padStart'];

            // Only record positive gaps
            if ($end < $start) {
                $gaps[] = [
                    'start_date' => $end->format('g:ia'),
                    'end_date'   => $start->format('g:ia'),
                ];
            }
        }
        // Set default flags for isLastEvent, nextGap, & nextEvent
        $noGap = false;
        $nextGap = null;
        $nextEvent = null;
        // if there are no event gaps, return noGap true
        if (count($gaps) == 0) {
            $noGap = true;
        } else {
            $nextGap = $gaps[0]['start_date'];
        }
        if ($isEventOngoing == false && count($padEvents) > 1) {
            $nextEvent = $padEvents[0]['padStart'];
        }
        foreach ($gaps as $key => &$entry) {
            $start = new DateTime($entry['start_date']);
            $end = new DateTime($entry['end_date']);
            $interval = $end->getTimestamp() - $start->getTimestamp();

            if ($interval < $minGap) {
                unset($gaps[$key]);
            }
        }
        unset($entry); // Break the reference just to be safe

        return [
        //  'gaps'              => $gaps,
            'isEventOngoing'    => $isEventOngoing,
            'noGap'             => $noGap,
            'nextGap'           => $nextGap,
            'nextEvent'         => $nextEvent
        ];

    }
    
    $arrayReady = rebuildArray($jsonFull, $page);

    // if the only event in here is the close‐event
    if (count($arrayReady) === 1) {
        $only   = $arrayReady[0];
        $closeT = new DateTimeImmutable($only['start_date']);
        $roomStatus = "Room is currently available; we will be closing at " . $closeT->format('g:ia');
    }
    else {
        $gapCalc = findGap($arrayReady);
        if ($gapCalc['isEventOngoing'] !== null) {
            // Currently occupied?
            if ($gapCalc['isEventOngoing']) {
                if ($gapCalc['noGap']) {
                    $roomStatus = "Room is currently occupied, and will be unavailable for the rest of the day";
                } else {
                    $roomStatus = "Room is currently occupied, will be available again at "
                                . $gapCalc['nextGap'];
                }

            // Currently free
            } else {
                $roomStatus = "Room is currently available; will be occupied again at "
                                . $gapCalc['nextEvent']->format('g:ia');
                }

        } else {
            // no gap at all
            $roomStatus = "No upcoming events or availability information could be determined.";
        }
    }  

    return [
        'arrayReady' => $arrayReady,
        'roomStatus' => $roomStatus,
    ];
};

?>