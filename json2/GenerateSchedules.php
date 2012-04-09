<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
$return_array = Array("success" => true);
$raw_json = $_GET["data"];
$json_array = json_decode($raw_json,true);
$courses_raw = $json_array['courses'];
$events = $json_array['events'];
$numsched = (!empty($_GET["number_of_schedules"]))?$_GET["number_of_schedules"]:1;
if (empty($courses_raw)){
    $return_array["success"]=false;
    $error_message = "VALUEERROR: courses paramiter not passed";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message :     $return_array["error"] .';'. $error_message);
} 
if (!is_array($courses_raw)) {

    $return_array["success"]=false;
    $error_message = "VALUEERROR: courses paramiter is not an array";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message :     $return_array["error"] .';'. $error_message);
}
if (empty($events_raw)){
    $return_array["success"]=false;
    $error_message = "VALUEERROR: events paramiter not passed";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message :     $return_array["error"] .';'. $error_message);
} 
if (!is_array($events_raw)) {
    $return_array["success"]=false;
    $error_message = "VALUEERROR: events paramiter is not an array";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message :     $return_array["error"] .';'. $error_message);
}
$courses = array();
foreach ($courses_raw as $course){
    try{
        $number = substr($course,-3);
        $dept = substr($course,0,-3);
        array_push($courses,GetClassTimes($dept,$number,'2012s'));
    } catch ( Exception $e ){
        $return_array["success"]=false;
        $error_message = "VALUEERROR: improperly formed course: $course";
        $return_array["error"]=(empty($return_array["error"]) ? $error_message :     $return_array["error"] .';'. $error_message);
        break;
    }
}
$numclasses = (!empty($_GET["number_of_classes"]))?$_GET["number_of_classes"]:count($courses);
if ($return_array["success"] && isset($_COOKIE['SID'])){
    session_id($_COOKIE['SID']);
    session_start();
    if(isset($_SESSION['Username'])){
        $username = $_SESSION['Username'];
        $events = array();
        foreach ($events_raw as $event){
            array_push($events,$get_event($username,$event));
        }
        $gen_scheds = array();
        $offset = 0;
        while ($numsched > 0){
            $gen_sched = array();
            $current_class_index = 1;
            $current_class = $courses[$current_class_index];
            while ($numclasses > 0){
                $temp_offset = $offset;
                $good_flag = true;
                for ($i = 0; $i < count($events);$i++){
                    if 
            }
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
