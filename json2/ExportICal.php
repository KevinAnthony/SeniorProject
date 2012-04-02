<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
$return_array = Array("success" => true);
$schedule_id = $_GET["schedule"];

if (empty($schedule_id)){
    $return_array["success"] = false;
    $return_array["error"] = "VALUEERROR: schedule paramiter not passed";
} else {
    //get schedule from database here

    //build schedule  here

    //return schedule here
}
echo json_encode($return_array);
?>
