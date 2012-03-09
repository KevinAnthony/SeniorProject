<?php

$url=$_SERVER['argv'][1];

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_HEADER, false); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);



$result= curl_exec($ch);
if (!$result) {
	echo "<br />cURL error number:" .curl_errno($ch);
	echo "<br />cURL error:" . curl_error($ch);
	exit;
} 
curl_close($ch); 

echo $result;//show page 1st

preg_match_all("/name=\"__VIEWSTATE\" id=\"__VIEWSTATE\" value=\"(.*?)\"/", $result, $arr_viewstate);

//print_r($arr_viewstate);
$viewstate = urlencode($arr_viewstate[1][0]);
//$viewstate = $arr_viewstate[1][0];
//print_r($viewstate);

preg_match_all("/name=\"__EVENTVALIDATION\" id=\"__EVENTVALIDATION\" value=\"(.*?)\"/", $result, $arr_eventvalidation);

$validation = urlencode($arr_eventvalidation[1][0]);
//print_r($validation);
////--------------------now try again with proper post variables
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_HEADER, true); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);



//curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, '__EVENTTARGET='.urlencode('ctl10$GridView1').'&__EVENTARGUMENT='.urlencode('Page$2').'&__LASTFOCUS=&__VIEWSTATE='.$viewstate.'&__VIEWSTATEENCRYPTED=&__EVENTVALIDATION='.$validation.'&'.urlencode('ctl10$ddlSemester').'=2011f');
//curl_setopt($ch, CURLOPT_POSTFIELDS, '__EVENTTARGET='.urlencode('ctl10$GridView1').'&__EVENTARGUMENT='.urlencode('Page$2').'&__VIEWSTATE='.$viewstate);





//curl_setopt($ch, CURLOPT_COOKIE, 'ASP.NET_SessionId='.urlencode('a4au0tfh0f3fsdmccnzylg55').'; path=/; domain=courseschedules.njit.edu; HttpOnly');
curl_setopt($ch, CURLOPT_COOKIE, 'ASP.NET_SessionId='.urlencode('a4au0tfh0f3fsdmccnzylg55'));
curl_setopt($ch, CURLOPT_REFERER, urlencode($url));



$result= curl_exec($ch);
if (!$result) {
	echo "<br />cURL error number:" .curl_errno($ch);
	echo "<br />cURL error:" . curl_error($ch);
	exit;
} 
curl_close($ch);

echo $result;//show page after posting data




////--------------------now try again with proper post variables
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_HEADER, true); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);



//curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, '__EVENTTARGET='.urlencode('ctl10$GridView1').'&__EVENTARGUMENT='.urlencode('Page$3').'&__LASTFOCUS=&__VIEWSTATE='.$viewstate.'&__VIEWSTATEENCRYPTED=&__EVENTVALIDATION='.$validation.'&'.urlencode('ctl10$ddlSemester').'=2011f');
//curl_setopt($ch, CURLOPT_POSTFIELDS, '__EVENTTARGET='.urlencode('ctl10$GridView1').'&__EVENTARGUMENT='.urlencode('Page$2').'&__VIEWSTATE='.$viewstate);





//curl_setopt($ch, CURLOPT_COOKIE, 'ASP.NET_SessionId='.urlencode('a4au0tfh0f3fsdmccnzylg55').'; path=/; domain=courseschedules.njit.edu; HttpOnly');
curl_setopt($ch, CURLOPT_COOKIE, 'ASP.NET_SessionId='.urlencode('a4au0tfh0f3fsdmccnzylg55'));
curl_setopt($ch, CURLOPT_REFERER, urlencode($url));



$result= curl_exec($ch);
if (!$result) {
	echo "<br />cURL error number:" .curl_errno($ch);
	echo "<br />cURL error:" . curl_error($ch);
	exit;
} 
curl_close($ch);

echo $result;//show page after posting data


?>
