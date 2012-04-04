<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
$event_name = $_GET["event_name"];
$day = $_GET["day"];
$start_time = $_GET["start_time"];
$end_time = $_GET["end_time"];
$return_array = Array("success" => true);

#first we validate the inputs, making sure they are there.  Also, make sure they are within range
if (empty($event_name)){
    $return_array["success"]=false;
    $error_message = "VALUEERROR: event_name paramiter not passed";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
    }
if (empty($day)){
    $return_array["success"]=false;
    $error_message = "VALUEERROR: day paramiter not passed";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
} else if (($day > 5) || ($start_time < 0)){
    $return_array["success"]=false;
    $error_message = "VALUEERROR: day is $day value should be >0 and <=5";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
}
if (empty($start_time)){
    $return_array["success"]=false;
    $error_message = "VALUEERROR: start_time paramiter not passed";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
} else if (($start_time > 1440) || ($start_time < 0)){
    $return_array["success"]=false;
    $error_message = "VALUEERROR: start_time is $start_time value should be >0 and <=1440";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
}
if (empty($end_time)){
    $return_array["success"]=false;
    $error_message = "VALUEERROR: day paramiter not passed";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
} else if (($end_time > 1440) || ($end_time < 0)){
    $return_array["success"]=false;
    $error_message = "VALUEERROR: end_time is $end_time value should be >0 and <=1440";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
}
if ($start_time >= $end_time){
    $return_array["success"]=false;
    $error_message = "VALUEERROR: start_time:$start_time before end_time:$end_time ";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
}
if ($return_array["success"]){
    if(isset($_COOKIE['SID'])){
        session_id($_COOKIE['SID']);
        session_start();
        if(isset($_SESSION['Username'])){
            $username = $_SESSION['Username'];
            $result = SaveEvent($event_name,$start_time,$end_time,$day,$username);
            if (!$result){
                $return_array["success"]=false;
                $error_message = "SQLERROR: Error inserting into database -- ".mysql_error();
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
}
echo json_encode($return_array);
?>

