<?php
   require '../resources/vendor/autoload.php';
    use SevenShores\Hubspot\Http\Client;
    use SevenShores\Hubspot\Resources\Engagements;
    use SevenShores\Hubspot\Resources\Contacts;
    
$auth = $_REQUEST['auth'];
$server_token = '';
$server_domain = '';

$domain = $auth['domain'];
$token = $auth['application_token'];

if ($server_token === $token && $server_domain === $domain) {
function writeToLog($data, $title = '') {
	$typeRequest = $data;
	 $log .= print_r($typeRequest, 1);
	 file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
	 return true;
	}
/* User array contains users that can have tasks forwarded to them from Bitrix to Hubspot */
$userArr = array(13,19);
$userNum = 2;
    $auth_con = 'AbFMuqQ4g3LVzQrb';
    require 'resources/connectTo.php';

$full_data = $_REQUEST['data'];
$taskID = $full_data['FIELDS_AFTER']['ID'];
    $queryUrl = 'https://uzumedia.bitrix24.com/rest/1/7d5dwyla6aly7bbx/task.item.getdata.json';
     $queryData = http_build_query(array($taskID));
     $curl = curl_init();
     curl_setopt_array($curl, array(
     CURLOPT_SSL_VERIFYPEER => 0,
     CURLOPT_POST => 1,
     CURLOPT_HEADER => 0,
     CURLOPT_RETURNTRANSFER => 1,
     CURLOPT_URL => $queryUrl,
     CURLOPT_POSTFIELDS => $queryData));
     $tasks = curl_exec($curl);
     $tasks = json_decode($tasks,1);
     curl_close($curl);
     $tasks = $tasks['result'];

     $taskOwner = intval($tasks['RESPONSIBLE_ID']);
     $canProceed = 0;
    for ($p = 0; $p < $userNum; $p++) { $userID = $userArr[$p]; if ($taskOwner === $userID) { $canProceed = 1; } }
    if ($canProceed === 1) {
        if ($taskOwner === 13) { $taskOwner = 33852336; $getContact = 127201;}
        else if ($taskOwner === 19) { $taskOwner = 33543802; $getContact = 114251; }
            $apiKey = '1b8c3ac8-397a-40f8-b26f-dc216dd10c2b';
            $client = new Client(['key' => $apiKey]);
            $taskEvent = new Engagements($client);
     $addQuery = "INSERT INTO `bitrix_tasks` (id, taskID) VALUES (NULL, ".$taskID.")";
     $addTasks = $mysqli->query($addQuery);
     $mysqli->close();
        
        $taskTitle = $tasks['TITLE'];
        $taskDesc = $tasks['DESCRIPTION'];
        $taskStatus = $tasks['REAL_STATUS'];
        if ($taskStatus === "1" || $taskStatus === "2" || $taskStatus === "3") { $taskStatus = "NOT_STARTED"; }
        if ($taskStatus === "4" || $taskStatus === "5") { $taskStatus = "COMPLETED"; }
     $queryUrl2 = 'https://uzumedia.bitrix24.com/rest/1/7d5dwyla6aly7bbx/crm.activity.list.json';
     $parameters2 = array("select" => array(), "filter" => array('ASSOCIATED_ENTITY_ID' => $taskID));
     $queryData2 = http_build_query($parameters2);
         $curl = curl_init();
         curl_setopt_array($curl, array(
         CURLOPT_SSL_VERIFYPEER => 0,
         CURLOPT_POST => 1,
         CURLOPT_HEADER => 0,
         CURLOPT_RETURNTRANSFER => 1,
         CURLOPT_URL => $queryUrl2,
         CURLOPT_POSTFIELDS => $queryData2));
       $activities = curl_exec($curl);
       $activities = json_decode($activities,1);
       curl_close($curl);
       if (!empty($activities)) {
       $activities = $activities['result'][0];
             $company = $activities['OWNER_ID'];
             $queryUrl3 = 'https://uzumedia.bitrix24.com/rest/1/7d5dwyla6aly7bbx/crm.company.get.json';
             $queryData3 = http_build_query(array("id" => $company));
                 $curl = curl_init();
                 curl_setopt_array($curl, array(
                 CURLOPT_SSL_VERIFYPEER => 0,
                 CURLOPT_POST => 1,
                 CURLOPT_HEADER => 0,
                 CURLOPT_RETURNTRANSFER => 1,
                 CURLOPT_URL => $queryUrl3,
                 CURLOPT_POSTFIELDS => $queryData3));
                 $getComp = curl_exec($curl);
             $getComp = json_decode($getComp,1);
             curl_close($curl);
             
        $getComp = $getComp['result'];
        $companyName = $getComp['TITLE'];
        if (strpos($taskTitle, 'call') !== false || strpos($taskTitle, 'Call') !== false) { $taskTitle = "Call ".$companyName; }
        else { $taskTitle = "Contact ".$companyName; }
         $hasPhone = $getComp['HAS_PHONE'];
         if ($hasPhone === "Y") { $companyPhone = $getComp['PHONE'][0]['VALUE']; $taskTitle.= ": ".$companyPhone; } }
        else { $taskTitle = "Bitrix Task: ".$taskTitle; }
        $timeS = strtotime('+1 day') * 1000;
        $headData = array('active' => true,'ownerId' => $taskOwner,'type' => 'TASK','timestamp' => $timeS);
        $meta = array('subject' => $taskTitle,'body' => $taskDesc,'state' => $taskStatus,'forObjectType' => 'CONTACT');
        $assoc_array = array('contactIds' => array($getContact));
        $taskEvent->create($headData, $assoc_array, $meta);
    }
}
?>