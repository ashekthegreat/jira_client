<?php
$postdata = json_decode(file_get_contents("php://input"));

/**
 * This section actually sends the email.
 */

/* Your email address */
$to = "mail.ashek@gmail.com";
$subject = "Task created";
$message = json_encode( $postdata );
$headers = "From: mail.ashek@gmail.com";

//mail($to, $subject, $message, $headers);

///////////////////////////
// Now create the task

$username = 'aelahi';
$password = 'aelahi@2011';

$url = "https://pantheon.atlassian.net/rest/api/2/issue";

$data = array(
    'fields' => array(
        'project' => array(
            'key' => 'TPT'
        ),
        'summary' => $postdata->issue->fields->summary,
        'description' => $postdata->issue->fields->description,
        'issuetype' => array(
            'name' => $postdata->issue->fields->issuetype->name
        )
    )
);
mail($to, $subject, json_encode( $data ), $headers);
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
//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

$result = curl_exec($ch);

$ch_error = curl_error($ch);

if ($ch_error) {
    //echo "cURL Error: $ch_error";
    mail($to, $subject, "cURL Error: $ch_error", $headers);
} else {
    //echo $result;
    mail($to, $subject, json_encode($result), $headers);
}

curl_close($ch);