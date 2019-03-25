<?php
require '../resources/vendor/autoload.php';
use SevenShores\Hubspot\Http\Client;
use SevenShores\Hubspot\Resources\Companies;

date_default_timezone_set('America/Denver');
$hourNow = intval(date('G'));
$weekday = intval(date('N'));

if ($hourNow >= 7 && $hourNow < 17 && $weekday !== 6 && $weekday !== 7) {
$table = '';
$host = '';
$user = '';
$pass = '';
$db = '';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) { echo $mysqli->connect_error; }

$crntDate = date('d-m-Y');
$startMon = date('m');
$startYr = date('Y');
$startDate = '01-'.$startMon.'-'.$startYr;
$getURL = "https://interact.leadforensics.com/WebApi_v2/Visit/GetAllVisits?datefrom=".$startDate."%2000:00:00&dateto=".$crntDate."%2023:59:59&pagesize=30&pageno=1";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $getURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
$headers = array();
$headers[] = "Accept: application/json, text/javascript, */*; q=0.01";
$headers[] = "Clientid: 150896";
$headers[] = "Referer: http://crm.uzudev.com/getLeads.php";
$headers[] = "Origin: http://crm.uzudev.com";
$headers[] = "Authorization-Token: 9aXWyoPxlkTG0520SAjZO5R";
$headers[] = "User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36";
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$result = curl_exec($ch);
if (curl_errno($ch)) { echo 'Error:' . curl_error($ch); }
curl_close ($ch);

$data = json_decode($result,1);
$bizArr = $data['SiteVisitList'];

$apiKey = '1b8c3ac8-397a-40f8-b26f-dc216dd10c2b';
$client = new Client(['key' => $apiKey]);
$companies = new Companies($client);

foreach ($bizArr as $biz) {
$bizID = $biz['BusinessID'];
$visitVar = $biz['VisitID'];
$forensics = "";
$getQuery = "SELECT id FROM `".$table."` WHERE forensicsID = '".$bizID."'";
$findData = $mysqli->query($getQuery);
while ($getData = $findData->fetch_assoc()) { $forensics = $getData['forensicsID']; }

if(empty($forensics)) {
    $requestURL = 'https://interact.leadforensics.com/WebApi_v2/Business/GetBusiness?businessid='.$bizID;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $requestURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
    $headers = array();
    $headers[] = "Accept: application/json, text/javascript, */*; q=0.01";
    $headers[] = "Clientid: 150896";
    $headers[] = "Referer: http://crm.uzudev.com/getLeads.php";
    $headers[] = "Origin: http://crm.uzudev.com";
    $headers[] = "Authorization-Token: 9aXWyoPxlkTG0520SAjZO5R";
    $headers[] = "User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) { echo 'Error:' . curl_error($ch); }
    curl_close ($ch);
    
    $data2 = json_decode($result,1);
    $newName = $data2['Name'];
    $lowName = strtolower($newName);
    $newAddress = $data2['AddressLine1'];
    $newCity = $data2['Town'];
    $newState = $data2['County'];
    $newZip = $data2['PostCode'];
    $newCountry = $data2['Country'];
    $newPhone = $data2['Telephone'];
    $newInd = $data2['Industry'];
    $website = $data2['Website'];
    $employees = $data2['EmployeeNumber'];

    $chkQuery = "SELECT id FROM `".$table."` WHERE value = '".$newName."'";
    $runCheck = $mysqli->query($chkQuery);
    $duplicate = "";
    while ($getChk = $runCheck->fetch_assoc()) { $duplicate = $getChk['id']; }
    if(empty($duplicate) && strpos($lowName, 'education') === false && strpos($lowName, 'college') === false && strpos($lowName, 'university') === false && strpos($lowName, 'school') === false) {
        $addKey = "INSERT INTO `".$table."` (id,forensicsID,value) VALUES (NULL,'".$bizID."','".$newName."')";
        $insertKey = $mysqli->query($addKey);
            if ($employees !== "" && !empty($employees)) {
            $tempEmployee = $employees;
            if (strpos($tempEmployee, ',') !== false) { $tempEmployee = str_replace(",","",$tempEmployee); }
            if (strpos($tempEmployee, '-') !== false) { $splitValue = explode("-",$tempEmployee); $tempEmployee = $splitValue[1]; }
            if (strpos($tempEmployee, '+') !== false) { $tempEmployee = str_replace("+","",$tempEmployee); }
            $tempEmployee = trim($tempEmployee);
            $tempEmployee = intval($tempEmployee); 
            } else { $tempEmployee = 0; $employees = 0; }
            
            if ($newCountry === "United States" && $tempEmployee < 100) {
            $requestURL3 = 'https://interact.leadforensics.com/WebApi_v2/Page/GetPagesByVisit?visitid='.$visitVar.'&pagesize=10&pageno=1';
                if (strpos($employees, '-') !== false) { $employees = str_replace("-"," to ",$tempEmployee); }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $requestURL3);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
            $headers = array();
            $headers[] = "Accept: application/json, text/javascript, */*; q=0.01";
            $headers[] = "Clientid: 150896";
            $headers[] = "Referer: http://crm.uzudev.com/getLeads.php";
            $headers[] = "Origin: http://crm.uzudev.com";
            $headers[] = "Authorization-Token: 9aXWyoPxlkTG0520SAjZO5R";
            $headers[] = "User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_errno($ch)) { echo 'Error:' . curl_error($ch); }
            curl_close ($ch);
            
            $data3 = json_decode($result,1);
            $pageLoc = $data3['PageVisitList'][0]['PageLocation'];
                if (strpos($pageLoc, 'uzu-media.com') !== false) { $type = "UZU Media"; }
                else if (strpos($pageLoc, 'spotonfinancial.com') !== false) { $type = "SpotOn Financial"; }
                $properties = array();
                if (!empty($newName) && $newName !== '') {
	            $properties[] = array('name' => 'name', 'value' => $newName);
	                if (!empty($newAddress) && $newAddress !== '') { $properties[] = array('name' => 'address', 'value' => $newAddress); }
	                if (!empty($newCity) && $newCity !== '') { $properties[] = array('name' => 'city', 'value' => $newCity); }
	                if (!empty($newState) && $newState !== '') { $properties[] = array('name' => 'state', 'value' => $newState); }
	                if (!empty($newCountry) && $newCountry !== '') { $properties[] = array('name' => 'country', 'value' => $newCountry); }
	                if (!empty($newZip) && $newZip !== '') { $properties[] = array('name' => 'zip', 'value' => $newZip); }
	                if (!empty($newPhone) && $newPhone !== '') { $properties[] = array('name' => 'phone', 'value' => $newPhone); }
	                if (!empty($newInd) && $newInd !== '') { $properties[] = array('name' => 'classification', 'value' => $newInd); }
	                if (!empty($employees) && $employees !== '' && $employees !== 0) { $properties[] = array('name' => 'numberofemployees', 'value' => $employees); }
	                if (!empty($type) && $type !== '') { $properties[] = array('name' => 'owner', 'value' => $type); }
	                $properties[] = array('name' => 'company_type', 'value' => 'Lead Forensics');
	                if (!empty($website) && $website !== '') { $properties[] = array('name' => 'website', 'value' => $website); }
                $companies->create($properties);
                }
            }
        }
    }
}
$mysqli->close(); } else { die(); }
?>