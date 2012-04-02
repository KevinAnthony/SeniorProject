<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
$return_array = Array("success" => true);

if(isset($_COOKIE['SID'])){
    session_id($_COOKIE['SID']);
    session_start();
    if(isset($_SESSION['Username'])){
        $result = GetSchedules[$_SESSION['Username'],'2012s');
        $scheduals = Array();
        if (!$result){
            $return_array["success"]=false;
            $error_message = "SQLERROR: Error with initial query -- ".mysql_error();
            $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
        } else {
            $return_array['number_of_scheduals'] = 0;
            $event_array = $result['events'];
            $course_array = $result['courses'];
                $single_schedual= Array();
                $event_array = Array();
                $class_array = Array();
                while( $row_event = mysql_fetch_array($result_event,MYSQL_ASSOC) ) {
                    $temp = Array("schedule_name" =>$row_event["SCHEDULE_NAME"],"event_name"=>$row_event["EVENT_NAME"],
                            "start_time"=>$row_event["START_TIME"],"end_time"=>$row_event["END_TIME"],"day"=>$row_event["DAY"]);
                    array_push($event_array,$temp);
                }
                $query = "select * from SCHEDULE_COURSE_VIEW where SCHEDULE_ID = $id";
                $result_class = mysql_query($query);
                $row_class = mysql_fetch_array($result_class,MYSQL_ASSOC);
                while( $row_class ) {
                    $i=0;
                    $temp = Array("schedule_name" =>$row_class["SCHEDULE_NAME"],"CRN" => $row_class["CRN"],
                            "department" => $row_class["DEPT"],"number" => $row_class["NUMBER"],"section" => $row_class["SECTION"]
                            ,"credits" => $row_class["CREDITS"],"instructor" => $row_class["INSTRUCTOR"],
                            "course_name" => $row_class["COURSE_NAME"],"course_description" => $row_class["DESCRIPTION"]);
                    $day = Array();
                    $start_time = Array();
                    $end_time = Array();
                    $room = Array();
                    $day[$i] = $row_class["DAY"];
                    $start_time[$i] = $row_class["START_TIME"];
                    $end_time[$i] = $row_class["END_TIME"];
                    $room[$i] = $row_class["ROOM"];
                    $i++;
                    $row_class = mysql_fetch_array($result_class,MYSQL_ASSOC);
                    while (($row_class) && $row_class["CRN"] == $temp["CRN"]){
                        $day[$i] = $row_class["DAY"];
                        $start_time[$i] = $row_class["START_TIME"];
                        $end_time[$i] = $row_class["END_TIME"];
                        $room[$i] = $row_class["ROOM"];
                        $i++;
                        $row_class = mysql_fetch_array($result_class,MYSQL_ASSOC);
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
                mysql_free_result($result_event);
                mysql_free_result($result_class);
            }
        }
        $return_array['scheduals'] = $scheduals;
        mysql_free_result($result);
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
mysql_close($connection);

echo json_encode($return_array);
?>
