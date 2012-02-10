<?php
$start_day = $_GET["start_day"];
$start_time = $_GET["start_time"];
$end_time = $_GET["end_time"];
$return_array = Array("success" => true);

if (empty($start_day)){
    $return_array["success"]=false;
    $return_array["error"]=(empty($return_array["error"]) ? "VALUEERROR: start_day empty" : $return_array["error"] .';'. "VALUEERROR: start_day empty");
}
if (empty($start_time)){
    $return_array["success"]=false;
    $return_array["error"]=(empty($return_array["error"]) ? "VALUEERROR: start_time empty" : $return_array["error"] .';'. "VALUEERROR: start_time empty");
} else if ($start_time > 1440){
    $return_array["success"]=false;
    $return_array["error"]=(empty($return_array["error"]) ? "VALUEERROR: start_time to large greater then 1440" : $return_array["error"] .';'. "VALUEERROR: start_time to large greater then 1440");
}
if (empty($end_time)){
    $return_array["success"]=false;
    $return_array["error"]=(empty($return_array["error"]) ? "VALUEERROR: end_time empty" : $return_array["error"] .';'. "VALUEERROR: end_time empty");
} else if ($end_time > 1440){
    $return_array["success"]=false;
    $return_array["error"]=(empty($return_array["error"]) ? "VALUEERROR: end_time to large greater then 1440" : $return_array["error"] .';'. "VALUEERROR: end_time to large greater then 1440");
}
if ($start_time >= $end_time){
    $return_array["success"]=false;
    $return_array["error"]=(empty($return_array["error"]) ? "VALUEERROR: end_time before start_time" : $return_array["error"] .';'. "VALUEERROR: end_time before start_time");
}
if ($return_array["success"]){
    if ($start_day = 0) {
        $return_array["Day"] = "Monday";
    } else if ($start_day = 1) {
        $return_array["Day"] = "Tuesday";
    } else if ($start_day = 2) {
        $return_array["Day"] = "Wednesday";
    } else if ($start_day = 3) {
        $return_array["Day"] = "Thursday";
    } else if ($start_day = 4) {
        $return_array["Day"] = "Friday";
    } else if ($start_day = 5) {
        $return_array["Day"] = "Saterday";
    } else {
        $return_array["success"]=false;
        $return_array["error"] = "start_day out of range... please use 0-5";
    }
    $return_array["Start Time"] = sprintf("%02d:%02d",intval($start_time/60),$start_time%60);
    $return_array["End Time"] = sprintf("%02d:%02d",intval($end_time/60),$end_time%60);
}
echo json_encode($return_array);
?>
