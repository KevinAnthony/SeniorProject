<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
$return_array = Array("success" => true);

if(isset($_COOKIE['SID'])){
    session_id($_COOKIE['SID']);
    session_start();
    if(isset($_SESSION['Username'])){
        $result = GetSchedules($_SESSION['Username'],'2012s');
        $scheduals = Array();
        if (!$result){
            $return_array["success"]=false;
            $error_message = "SQLERROR: Error with initial query ";
            $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
        } else {
            while ($schedual = array_shift($result)){
                    $return_array['number_of_scheduals'] = 0;
                    $event_result = $schedual['events'];
                    $course_result= $schedual['courses'];
                    $single_schedual= Array();
                    $event_array = Array();
                    $class_array = Array();
                    while( $row_event = array_shift($event_result) ) {
                    $temp = Array("schedule_name" =>$row_event["schedule_name"],"event_name"=>$row_event["event_name"],
                        "start_time"=>$row_event["start_time"],"end_time"=>$row_event["end_time"],"day"=>$row_event["day"]);
                    array_push($event_array,$temp);
                    }
                    $row_class = array_shift($course_result);
                    while( $row_class ) {
                    $i=0;
                    $temp = Array("schedule_name" =>$row_class["schedule_name"],"CRN" => $row_class["crn"],
                        "department" => $row_class["dept"],"number" => $row_class["number"],"section" => $row_class["section"]
                        ,"credits" => $row_class["credits"],"instructor" => $row_class["instructor"],
                        "course_name" => $row_class["course_name"],"course_description" => $row_class["description"]);
                    $day = Array();
                    $start_time = Array();
                    $end_time = Array();
                    $room = Array();
                    $day[$i] = $row_class["day"];
                    $start_time[$i] = $row_class["start_time"];
                    $end_time[$i] = $row_class["end_time"];
                    $room[$i] = $row_class["room"];
                    $i++;
                    $row_class = array_shift($course_result);
                    while (($row_class) && $row_class["crn"] == $temp["crn"]){
                        $day[$i] = $row_class["day"];
                        $start_time[$i] = $row_class["start_time"];
                        $end_time[$i] = $row_class["end_time"];
                        $room[$i] = $row_class["room"];
                        $i++;
                        $row_class = array_shift($course_result);
                    }
                    $temp['day'] = $day;
                    $temp['start_time'] = $start_time;
                    $temp['end_time'] = $end_time;
                    $temp['room'] = $room;
                    array_push($class_array,$temp);
                    }
                    $single_schedual['events'] = $event_array;
                    $single_schedual['courses'] = $class_array;
                    $schedual_group = Array();
                    array_push($schedual_group,$single_schedual);
                    array_push($scheduals,$schedual_group);
                    $return_array['number_of_scheduals']++;
            }
        }
        $return_array['scheduals'] = $scheduals;
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
