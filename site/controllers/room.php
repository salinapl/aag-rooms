<?php

return function($page) {
    date_default_timezone_set('America/Chicago');
    // $roomnum = $page->rmnum();
    $auth_url = $page->authurl();
    //$json_url = "https://calendar.salinapubliclibrary.org/events/feed/json?&start=now&end=+12hours&rooms[$roomnum]=$roomnum";
    //$json_url = "https://calendar.salinapubliclibrary.org/events/feed/json?&start=now&quantity=10&rooms[$roomnum]=$roomnum";
    $feedUrl = $page->feedurl();
    $feedFlags = $page->feedflags();
    $json_url = $feedUrl . $feedFlags;
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

    function rebuildArray() {
        
    }

    // Function to lump together ending times to provide correct
    // availiablity end times for the relative time function
    function lumpyTime() {
        
    }
    

    // Function to determine relative time to the next event 
    // in relation to the current day and closing time
    function relativetime($time, $closingTime) {

        // Check if input is a valid timestamp and convert it
        if(!ctype_digit($time)) {
            $time = strtotime($time);
        }
        if(!ctype_digit($closingTime)) {
            $closingTime = strtotime($closingTime);
        }

        // If the next event time is greater than closing time, use closing time
        if ($time > $closingTime) {
            $time = $closingTime;
        } 
        
        $now = time();

        // Might need to rewrite this bit, checks if time is in the future
        // returns closed message if not (huh?)
        if ($time - (15 * 60) <= $now) {
            return "for the rest of the day.";
        }

        $interval = $time - $now;

        $totalTime = floor($interval / 60);

        if ($totalTime < 60) {
            return "For the next " . $totalTime . " minute" . ($totalTime !== 1 ? "s" : ".");
        }

        if($totalTime < 720) {
            $hours = floor($totalTime / 60);
            $minutes = $totalTime % 60;
            $timeString = "for the next " . $hours . " hour" . ($hours !== 1 ? "s" : ".");
            if ($minutes > 0) {
                $timeString .= " and " . $minutes . " minute" . ($minutes !== 1 ? "s" : ".");
            }
            return $timeString;
        }
        return "all day.";
    }
    
    $jsonRaw = json_decode($jsonFull);
    // Filter out cancelled events from the array
    $jsonFilter = array_filter($jsonRaw, function(stdClass $item) {
        return !property_exists($item, 'moderation_state')
            || $item->moderation_state !== 'cancelled';
    });
    // Rename Private event titles
    foreach ($jsonFilter as $item) {
        if (isset($item->public) && ($item->public === false || $item->public === 'false')) {
            $eventid = $item->id;
            $item->title = 'Private Reservation ('. $eventid .')';
        }
    }
    // Reset Array Numbers after filtering
    $json_ready = array_values($jsonFilter);
    $json_first = $json_ready[0] ?? null;
    $jsonNextStart = "none";
    if ($json_first == null){
        $json_first = "empty";
    } else {
        $jsonNextStart = $json_first->start_date;
        $nextEvent = relativetime($jsonNextStart, "2025-06-22 20:00:00");
    }
    
    return [
        'json_ready' => $json_ready,
        'nextEvent' => $nextEvent
    ];
};

?>