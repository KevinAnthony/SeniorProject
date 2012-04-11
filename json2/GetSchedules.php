<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
$return_array = Array("success" => true);

if(isset($_COOKIE['SID'])){
    session_id($_COOKIE['SID']);
    session_start();
    if(isset($_SESSION['Username'])){
        $result = GetSchedules($_SESSION['Username'],'2012s');
        $scheduals = Array();
        if ($result == -1){
            $return_array['number_of_scheduals'] = 0;
            $single_schedual['events'] = array();
            $single_schedual['courses'] = array();
            $schedual_group = Array();
            array_push($schedual_group,$single_schedual);
            array_push($scheduals,$schedual_group);
            $return_array['scheduals'] = $scheduals;
        } else {
            if (!$result){
                $return_array["success"]=false;
                $error_message = "SQLERROR: Error with GetSchedules query ";
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
                        array_push($event_array,Array("schedule_name" =>$row_event["schedule_name"],"event_name"=>$row_event["event_name"],"start_time"=>$row_event["start_time"],"end_time"=>$row_event["end_time"],"day"=>$row_event["day"]));
                    }
                    $row_class = array_shift($course_result);
                    while( $row_class ) {
                        $i=0;
                        $single_class["schedule_name"] =$row_class["schedule_name"];
                        $single_class["crn"] = $row_class["crn"];
                        $single_class["department"] = $row_class["dept"];
                        $single_class["number"] = $row_class["number"];
                        $single_class["section"] = $row_class["section"];
                        $single_class["credits"] = $row_class["credits"];
                        $single_class["instructor"] = $row_class["instructor"];
                        $single_class["course_name"] = $row_class["course_name"];
                        $single_class["course_description"] = $row_class["description"];
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
                        while (($row_class) && $row_class["crn"] == $single_class["crn"]){
                            $day[$i] = $row_class["day"];
                            $start_time[$i] = $row_class["start_time"];
                            $end_time[$i] = $row_class["end_time"];
                            $room[$i] = $row_class["room"];
                            $i++;
                            $row_class = array_shift($course_result);
                        }
                        $single_class['day'] = $day;
                        $single_class['start_time'] = $start_time;
                        $single_class['end_time'] = $end_time;
                        $single_class['room'] = $room;
                        array_push($class_array,$single_class);
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
