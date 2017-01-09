<?php

$username = 'aelahi';
$password = 'aelahi@2011';

$url = "https://pantheon.atlassian.net/rest/api/2/priority";

$ch = curl_init();

$headers = array(
    'Accept: application/json',
    'Content-Type: application/json'
);


curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
//curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($searchParam));
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

$result = curl_exec($ch);
$ch_error = curl_error($ch);

if ($ch_error) {
    echo "cURL Error: $ch_error";
} else {
    echo $result;
}

curl_close($ch);