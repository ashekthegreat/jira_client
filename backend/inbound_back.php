<?php
$postdata = json_decode(file_get_contents("php://input"));

function addIssueLink($linkType, $inwardIssueKey, $outwardIssueKey, $headers, $credentials){
    echo "Linking issue";
    $data = array(
        "type" => array(
            "name" => $linkType
        ),
        "inwardIssue" => array(
            "key" => $inwardIssueKey
        ),
        "outwardIssue" => array(
            "key" => $outwardIssueKey
        )
    );
    $url = "https://pantheon.atlassian.net/rest/api/2/issueLink";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, $credentials);

    $result = curl_exec($ch);
    $ch_error = curl_error($ch);
    curl_close($ch);

    if ($ch_error) {
        echo "<br>Could not link. cURL Error: $ch_error";
    } else{
        echo "<br>Link done. result: " . $result;
    }
    return $result;
}
/**
 * This section actually sends the email.
 */

/* Your email address */
$to = "mail.ashek@gmail.com";
$subject = "Task created";
$message = json_encode($postdata);
$headers = "From: mail.ashek@gmail.com";

//mail($to, $subject, $message, $headers);
///////////////////////////
// Now create the task

//$username = 'aelahi';
//$password = 'aelahi@2011';
$username = 'api_user';
$password = '@piU$er#69';

$url = "https://pantheon.atlassian.net/rest/api/2/issue";

$data = array(
    'fields' => array(
        'project' => array(
            'key' => $_GET['target']
        ),
        'summary' => $postdata->issue->fields->summary,
        'description' => $postdata->issue->fields->description,
        'issuetype' => array(
            'name' => 'Task'
        )
    )
);
mail($to, $subject, json_encode($data), $headers);
mail($to, $subject, json_encode($postdata), $headers);


$headers = array(
    'Accept: application/json',
    'Content-Type: application/json'
);
$credentials = "$username:$password";
$ch = curl_init();
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
curl_close($ch);

if ($ch_error) {
    echo "<br>cURL Error: $ch_error";
    mail($to, $subject, "cURL Error: $ch_error", $headers);
} else {
    echo "<br>result: " . $result;
    mail($to, $subject, json_encode($result), $headers);
    $createdIssue =  json_decode($result);

    addIssueLink("Cloners", $createdIssue->key, $postdata->issue->key, $headers, $credentials);

    if(!empty($postdata->issue->fields->attachment)){
        echo "<br>Attachment found";
        $cnt = count($postdata->issue->fields->attachment);
        echo $cnt;
        for ($i = 0; $i < $cnt; $i++) {
            //file_put_contents($postdata->issue->fields->attachment[$i]->filename, file_get_contents($postdata->issue->fields->attachment[$i]->content));
            $file = $postdata->issue->fields->attachment[$i]->content;
            $newfile = __DIR__ . '/tmp/' . $postdata->issue->fields->attachment[$i]->filename;
            echo "<br>" . $file . "<br>\n" . $newfile;
            mail($to, "File downloading", $file . "<br>\n" . $newfile, $headers);

            //////////////////////////////////////
            $fp = fopen ($newfile, 'w+');
            //Here is the file we are downloading, replace spaces with %20
            $ch = curl_init(str_replace(" ","%20",$file));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
            // write curl response to file
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            // get curl response
            $result_file_download = curl_exec($ch);
            $ch_error = curl_error($ch);
            curl_close($ch);
            fclose($fp);
            //////////////////////////////////////
            if ( $ch_error ) {
                echo "<br>File download failed";
                mail($to, "File download failed", $file . "<br>\n" . $newfile, $headers);
            } else{
                echo "<br>File downloaded";
                mail($to, "File downloaded", $file . "<br>\n" . $newfile, $headers);


                echo "<br>Posting attachment..";
                $url = $createdIssue->self . "/attachments";
                echo "<br>URL: " . $url;
                $data = array('file'=>"@$newfile");
                $headers = array(
                    'X-Atlassian-Token: nocheck'
                );

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_VERBOSE, 1);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

                $result_file_upload = curl_exec($curl);
                $ch_error = curl_error($curl);

                if ($ch_error) {

                    echo "cURL Error: $ch_error";
                } else {
                    echo "<br>Attachment posted<br>";
                    echo $result_file_upload;
                }

                curl_close($curl);



            }
        }
    }
}

