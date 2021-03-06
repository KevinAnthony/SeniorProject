<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
$raw_json =  urldecode($_SERVER["QUERY_STRING"]);
$return_array = Array("success" => true);
$json_array = json_decode($raw_json,true);
if (empty($json_array['semester'])){
    $semester = '2012s';
} else {
    $semester = $json_array['semester'];
}
if (!isset($json_array['schedule_name'])){
    $return_array["success"]=false;
    $error_message = "no schedule_name field passed";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
}
if (!isset($json_array['courses'])){
    $return_array["success"]=false;
    $error_message = "no courses field passed";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
}
if (!isset($json_array['events'])){
    $return_array["success"]=false;
    $error_message = "no events field passed";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
}
if ($return_array["success"] && isset($_COOKIE['SID'])){
    session_id($_COOKIE['SID']);
    session_start();
    if(isset($_SESSION['Username'])){
        if (!SaveSchedule($semester,$_SESSION['Username'],$json_array['schedule_name'],$json_array['courses'],$json_array['events'])){
            $return_array["success"]=false;
            $error_message = "SQLERROR: Error inserting into database";
            $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
        }
    } else {
        $return_array["success"]=false;
        $error_message = "SESSIONERROR: Session Expired";
        $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
    }
} else {
    $return_array["success"]=false;
    $error_message = "SESSIONERROR: Login Required";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
}
echo json_encode($return_array);
?>
