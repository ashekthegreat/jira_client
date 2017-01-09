<?php

$username = 'aelahi';
$password = 'aelahi@2011';

$postData = file_get_contents("php://input");
$request = json_decode($postData);

$issue_id = $request->id;
$payload = $request->data;

//$url = "https://pantheon.atlassian.net/rest/api/latest/issue/BBL-5886";
$url = "https://pantheon.atlassian.net/rest/api/2/issue/" . $issue_id;

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
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
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