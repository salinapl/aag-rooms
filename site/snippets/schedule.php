<?php
$roomnum = $page->rmnum();
#$json_url = "https://calendar.salinapubliclibrary.org/events/feed/json?&start=now&end=+12hours&rooms[$roomnum]=$roomnum";
$json_url = "https://calendar.salinapubliclibrary.org/events/feed/json?&start=now&quantity=10&rooms[$roomnum]=$roomnum";
$json_string = file_get_contents("$json_url");
$json_decode = json_decode($json_string);
foreach ($json_decode as $json_data){
    $start_time = strtotime($json_data->start_date);
    $end_time = strtotime($json_data->end_date);
    echo "<li><span class='time'>", date("g:ia", $start_time) ."&nbsp;-&nbsp;", date("g:ia", $end_time) ."</span>&nbsp;", $json_data->title ."</li>";
}