<?php
echo "webhook received";
// map of the cloner projects. Add new items if necessary
$config = json_decode( file_get_contents( __DIR__ . "/config.json"), true );
$map = $config['projectMap'];
$clientMap = $config['clientMap'];
$username = 'api_user';
$password = '@piU$er#69';
$credentials = "$username:$password";

$headers = array(
    'Accept: application/json',
    'Content-Type: application/json'
);

// collect input data
$postData = json_decode(file_get_contents("php://input"));
$issue = $_GET['issue'];
$project = $_GET['project'];

echo "echoing vars\n";
echo json_encode( $project . "\n" );
echo json_encode( $map . "\n" );
echo "echoing vars done\n";
if (isset($map[$project])) {
    $target = $map[$project];
} else {
    exit(0);
}

sendMail("Service desk hook received", $postData);
//sendMail("Params: ", array("target" => $target, "issue" => $issue, "project" => $project));

function sendMail($subject, $data)
{
    $to = "panthbabel1@gmail.com";
    $message = json_encode($data);
    $headers = "From: panthbabel1@gmail.com";

    //mail($to, $subject, $message, $headers);
    return mail($to, $subject, $message, $headers);
}

function curlPut($url, $headers, $credentials, $data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, $credentials);

    $result = curl_exec($ch);
    $ch_error = curl_error($ch);
    curl_close($ch);

    if ($ch_error) {
        echo "cURL Error: $ch_error";
    } else {
        echo "Result: " . $result;
    }
    return json_decode($result);
}

function curlPost($url, $headers, $credentials, $data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, $credentials);

    $result = curl_exec($ch);
    $ch_error = curl_error($ch);
    curl_close($ch);

    if ($ch_error) {
        echo "cURL Error: $ch_error";
    } else {
        echo "Result: " . $result;
    }
    return json_decode($result);
}

function curlGet($url, $headers, $credentials)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, $credentials);

    $result = curl_exec($ch);
    $ch_error = curl_error($ch);
    curl_close($ch);

    if ($ch_error) {
        echo "cURL Error: $ch_error";
    } else {
        echo "Result: " . $result;
    }
    return json_decode($result);
}

function createIssueLink($linkType, $inwardIssueKey, $outwardIssueKey, $headers, $credentials)
{
    $url = "https://pantheon.atlassian.net/rest/api/2/issueLink";
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

    return curlPost($url, $headers, $credentials, $data);
}

function createAttachment($targetIssueKey, $attachmentFile, $credentials)
{
    //$url = $issue->self . "/attachments";
    $url = "https://pantheon.atlassian.net/rest/api/2/issue/" . $targetIssueKey . "/attachments";

    $data = array('file' => "@$attachmentFile");
    $headers = array(
        'X-Atlassian-Token: nocheck'
    );

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_USERPWD, $credentials);
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

    return $result_file_upload;
}

function transferAttachment($targetIssueKey, $remoteFile, $localFile, $headers, $credentials)
{
    $fp = fopen($localFile, 'w+');
    //Here is the file we are downloading, replace spaces with %20
    $ch = curl_init(str_replace(" ", "%20", $remoteFile));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_USERPWD, $credentials);
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);
    // write curl response to file
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    // get curl response
    $result = curl_exec($ch);
    $ch_error = curl_error($ch);
    curl_close($ch);
    fclose($fp);

    if ($ch_error) {
        echo "File download failed";
    } else {
        echo "File downloaded. Now posting attachment..";
        createAttachment($targetIssueKey, $localFile, $credentials);
    }
    unlink($localFile);

    return json_decode($result);
}

function createIssue($postData, $target, $headers, $credentials, $clientMap)
{
    $url = "https://pantheon.atlassian.net/rest/api/2/issue";

    $data = array(
        'fields' => array(
            'project' => array(
                'key' => $target
            ),
            'summary' => $postData->issue->fields->summary,
            'description' => (!is_null($postData->issue->fields->description) ? $postData->issue->fields->description : ""),
            'issuetype' => array(
                'name' => 'Task'
            ),
            'assignee' => array(
                'name' => ''
            ),
            'components'=> array(
                array(
                    'name'=> ""
                )
            )
        )
    );
    // check for clientMap
    foreach ($clientMap as $key => $value) {
        echo $postData->user->key . " <> " . $key . "*****";
        if($postData->user->key == $key){
            // we found a map lets set the component and assignee
            $data['fields']['assignee']['name'] = $value['maintAssignee'];
            $data['fields']['components'][0]['name'] = $value['component'];
            //array_push($data[fields], array('components' => array('name' => $value->component)) );
            //array_push($data[fields], array('assignee' => array('key' => $value->maintAssignee)) );
        }
    }
    echo json_encode($data);
    sendMail("Creating task", json_encode($data));
    return curlPost($url, $headers, $credentials, $data);
}

function updateIssue($postData, $target, $headers, $credentials, $clientMap)
{
    $url = "https://pantheon.atlassian.net/rest/api/2/issue/" . $postData->issue->key . "?notifyUsers=false";

    $data = array(
        'fields' => array(
            'assignee' => array(
                'name' => ''
            ),
            'components'=> array(
                array(
                    'name'=> ""
                )
            )
        )
    );
    // check for clientMap
    foreach ($clientMap as $key => $value) {
        echo $postData->user->key . " <> " . $key . "*****";
        if($postData->user->key == $key){
            // we found a map lets set the component and assignee
            $data['fields']['assignee']['name'] = $value['sdAssignee'];
            $data['fields']['components'][0]['name'] = $value['component'];
            //array_push($data[fields], array('components' => array('name' => $value->component)) );
            //array_push($data[fields], array('assignee' => array('key' => $value->maintAssignee)) );
        }
    }
    echo "Updating issue: " . json_encode($data);
    sendMail("Updating task", json_encode($data));
    return curlPut($url, $headers, $credentials, $data);
}

function createComment($postData, $targetIssueKey, $headers, $credentials)
{
    $url = "https://pantheon.atlassian.net/rest/api/2/issue/" . $targetIssueKey . "/comment";

    $data = array(
        'body' => "*" . $postData->comment->author->displayName . "* commented:\n {quote}" . $postData->comment->body . "{quote}"
    );

    return curlPost($url, $headers, $credentials, $data);
}

function cloneIssue($postData, $target, $headers, $credentials, $clientMap)
{
    $createdIssue = createIssue($postData, $target, $headers, $credentials, $clientMap);

    if (isset($createdIssue) && isset($createdIssue->key)) {

        createIssueLink("SDClone", $createdIssue->key, $postData->issue->key, $headers, $credentials);

        if (!empty($postData->issue->fields->attachment)) {

            $cnt = count($postData->issue->fields->attachment);
            echo "Attachment found: " . $cnt;
            for ($i = 0; $i < $cnt; $i++) {
                $remoteFile = $postData->issue->fields->attachment[$i]->content;
                $localFile = __DIR__ . '/tmp/' . $postData->issue->fields->attachment[$i]->filename;
                echo $remoteFile . "\n" . $localFile;

                transferAttachment($createdIssue->key, $remoteFile, $localFile, $headers, $credentials);
            }
        }

        // update SD issue and set assignee and component
        updateIssue($postData, $target, $headers, $credentials, $clientMap);
    }
    return $createdIssue;
}

function cloneComment($postData, $issueKey, $headers, $credentials)
{
    $url = "https://pantheon.atlassian.net/rest/api/2/issue/" . $issueKey . "?fields=attachment,issuelinks";

    $sourceIssue = curlGet($url, $headers, $credentials);

    $targetIssueKey = null;
    for ($i = 0; $i < count($sourceIssue->fields->issuelinks); $i++) {
        if ($sourceIssue->fields->issuelinks[$i]->type->name == "SDClone") {
            $targetIssueKey = $sourceIssue->fields->issuelinks[$i]->inwardIssue->key;
            break;
        }
    }
    if (is_null($targetIssueKey)) {
        echo "Target Issue not found";
        return false;
    }

    $comment = createComment($postData, $targetIssueKey, $headers, $credentials);

    $url = "https://pantheon.atlassian.net/rest/api/2/issue/" . $targetIssueKey . "?fields=attachment,issuelinks";
    $targetIssue = curlGet($url, $headers, $credentials);


    /////////////
    // Take care of the attachments and push if there are any new
    if (!empty($sourceIssue->fields->attachment)) {

        $cnt = count($sourceIssue->fields->attachment);
        echo "Attachment found: " . $cnt;
        for ($i = 0; $i < $cnt; $i++) {
            $remoteFile = $sourceIssue->fields->attachment[$i]->content;
            $localFile = __DIR__ . '/tmp/' . $sourceIssue->fields->attachment[$i]->filename;
            echo $remoteFile . "\n" . $localFile;

            $isFound = false;
            for ($j = 0; $j < count($targetIssue->fields->attachment); $j++) {
                if ($sourceIssue->fields->attachment[$i]->filename == $targetIssue->fields->attachment[$j]->filename) {
                    $isFound = true;
                    break;
                }
            }
            if (!$isFound) {
                transferAttachment($targetIssueKey, $remoteFile, $localFile, $headers, $credentials);
            }

        }
    }

    ////////////


    return $comment;
}

if ($postData->webhookEvent == "jira:issue_created") {
    cloneIssue($postData, $target, $headers, $credentials, $clientMap);
} elseif ($postData->webhookEvent == "comment_created") {
    cloneComment($postData, $issue, $headers, $credentials);
}
