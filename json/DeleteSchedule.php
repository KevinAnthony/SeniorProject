<?php

include_once dirname(__FILE__)."/SQL_Functions.php";

$schedule_name = $_GET["schedule_name"];
$return_array = Array("success" => true);

if (empty($schedule_name)){
    $return_array["success"] = false;
    $return_array["error"] = (empty($return_array["error"]) ? "VALUEERROR: schedule_name empty" : $return_array["error"] .';'. "VALUEERROR: schedule_name empty");
} else {
    if(isset($_COOKIE['SID'])){
        session_id($_COOKIE['SID']);
        session_start();
        if(isset($_SESSION['Username'])){
            if (!DeleteSchedule($_SESSION['Username'],$schedule_name)) {
                $return_array["success"]=false;
                $error_message = "SQLERROR: Error deleting row $id -- ".mysql_error();
                $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
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
}
echo json_encode($return_array);
?>
