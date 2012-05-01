<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
$return_array = Array("success" => true);

if(isset($_COOKIE['SID'])){
    session_id($_COOKIE['SID']);
    session_start();
    if(isset($_SESSION['Username'])){
        $result = GetSchedules($_SESSION['Username']);
        $schedules = Array();
        if ($result == -1){
            $return_array['number_of_schedules'] = 0;
            $single_schedule['events'] = array();
            $single_schedule['courses'] = array();
            $schedule_group = Array();
            array_push($schedule_group,$single_schedule);
            array_push($schedules,$schedule_group);
            $return_array['schedules'] = $schedules;
            $return_array['number_of_schedules'] = 0;
            
        } else {
            if (!$result){
                $return_array["success"]=false;
                $error_message = "SQLERROR: Error with GetSchedules query ";
                $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
            } else {
                while ($schedule = array_shift($result)){
                    $event_result = $schedule['events'][0];
                    $course_result= $schedule['courses'][0];
                    $single_schedule= Array();
                    $event_array = Array();
                    $class_array = Array();
                    while( $row_event = array_shift($event_result) ) {
                        array_push($event_array,Array("event_name"=>$row_event["event_name"],"start_time"=>$row_event["start_time"],"end_time"=>$row_event["end_time"],"day"=>$row_event["day"],"id" => $row_event["event_id"]));
                        $single_schedule['schedule_name'] = $row_event["schedule_name"];
                        $single_schedule['semester'] = $row_event['semester'];
                    }
                    $row_class = array_shift($course_result);
                    while( $row_class ) {
                        $i=0;
                        $single_schedule['schedule_name'] = $row_class["schedule_name"];
                        $single_class = Array("CRN"=> $row_class["crn"],"deptartment"=> $row_class["dept"],"number"=> $row_class["number"],"section"=> $row_class["section"],"credits"=> $row_class["credits"],"instructor"=> $row_class["instructor"],"course_name" => $row_class["course_name"],"course_description" => $row_class["description"]);
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
                        while (($row_class) && $row_class["crn"] == $single_class["CRN"]){
                            if (in_array($row_class["day"],$day)){
                                $row_class = array_shift($course_result);
                                continue;
                            }
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
                    $single_schedule['events'] = $event_array;
                    $single_schedule['courses'] = $class_array;
                    $schedule_group = Array();
                    array_push($schedule_group,$single_schedule);
                    array_push($schedules,$schedule_group);
                    $return_array['number_of_schedules']++;
                }
            }
            $return_array['schedules'] = $schedules;
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
