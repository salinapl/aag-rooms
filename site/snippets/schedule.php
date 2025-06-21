<?php
$roomnum = $page->rmnum();
$auth_url = $page->authurl();
// $json_url = "https://calendar.salinapubliclibrary.org/events/feed/json?&start=now&end=+12hours&rooms[$roomnum]=$roomnum";
$json_url = "https://calendar.salinapubliclibrary.org/events/feed/json?&start=now&quantity=10&rooms[$roomnum]=$roomnum";
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

$json_full = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {

}
// End Auth0 contributed code
$json_raw = json_decode($json_full);
// Filter out cancelled events from the array
$json_filter = array_filter($json_raw, function(stdClass $item) {
    return !property_exists($item, 'moderation_state')
        || $item->moderation_state !== 'cancelled';
});
// Rename Private event titles
foreach ($json_filter as $item) {
    if (isset($item->public) && ($item->public === false || $item->public === 'false')) {
        $item->title = 'Private Reservation';
    }
}
// Reset Array Numbers after filtering
$json_ready = array_values($json_filter);
$json_next_start = $json_ready[0]->start_date;
// echo $json_next_start;
// foreach ($json_ready as $json_data){
//    $start_time = strtotime($json_data->start_date);
//    $end_time = strtotime($json_data->end_date);
//    echo "<li><span class='time'>", date("g:ia", $start_time) ."&nbsp;-&nbsp;", date("g:ia", $end_time) ."</span>&nbsp;", $json_data->title ."</li>";
//}