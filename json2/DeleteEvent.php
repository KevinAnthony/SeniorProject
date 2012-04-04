<?php

include_once dirname(__FILE__)."/SQL_Functions.php";

$id = $_GET["id"];
$return_array = Array("success" => true);

if (empty($id)){
    $return_array["success"] = false;
    $return_array["error"] = (empty($return_array["error"]) ? "VALUEERROR: id empty" : $return_array["error"] .';'. "VALUEERROR: id empty");
} else {
    if(isset($_COOKIE['SID'])){
        session_id($_COOKIE['SID']);
        session_start();
        if(isset($_SESSION['Username'])){
            if (!DeleteEvent($id,$_SESSION['Username'])) {
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
