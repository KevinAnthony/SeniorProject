<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
$return_array = Array("success" => true);
$course_number = intval($_GET["course_number"]);
$department = $_GET["department"];
if (empty($course_number)){
    $return_array["success"] = false;
    $error_message ="VALUEERROR: course_number empty";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
}
if (empty($department)){
    $return_array["success"] = false;
    $error_message ="VALUEERROR: department empty";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
}
if (!($return_array["success"])){
    echo json_encode($return_array);
    die();
}
$course = "$department $course_number";
$result = GetPrereq($course);
if (!$result){
    $return_array["success"]=false;
    $error_message = "SQLERROR:".mysql_error();
    $return_array["error"] = $error_message;
} else {
    $return_array['prerequisite'] = $result[0]['prerequisite'];
}
echo json_encode($return_array);
?>
