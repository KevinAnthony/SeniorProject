<?php
$return_array = Array("success" => true);
$connection = mysql_connect('sql.njit.edu','ejw3_proj','ozw6OBAO') ;
if (!$connection){
    $return_array["success"]=false;
    $error_message = "SQLERROR: Error Connectiong to database -- ".mysql_error();
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
    echo json_encode($return_array);
    die();
}
if(isset($_COOKIE['SID'])){
    session_id($_COOKIE['SID']);
    session_start();
    if(isset($_SESSION['Username'])){
        mysql_select_db('ejw3_proj');
        $username = $_SESSION['Username'];
        $query = "select ID from SCHEDULES where USER = '$username'";
        $result = mysql_query($query);
        $scheduals = Array();
        if (!$result){
            $return_array["success"]=false;
            $error_message = "SQLERROR: Error with initial query -- ".mysql_error();
            $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
        } else {
            
            $return_array['number_of_scheduals'] = 0;
            while( $row = mysql_fetch_array($result,MYSQL_ASSOC) ) {
                $id = intval($row["ID"]);
                $single_schedual= Array();
                $event_array = Array();
                $class_array = Array();
                $query = "select * from SCHEDULE_EVENT_VIEW where SCHEDULE_ID = $id";
                $result_event = mysql_query($query);
                while( $row_event = mysql_fetch_array($result_event,MYSQL_ASSOC) ) {
                    $temp = Array("schedule_name" =>$row_event["SCHEDULE_NAME"],"event_name"=>$row_event["EVENT_NAME"],
                    "start_time"=>$row_event["START_TIME"],"end_time"=>$row_event["END_TIME"],"day"=>$row_event["DAY"]);
                    array_push($event_array,$temp);
                }
                $query = "select * from SCHEDULE_COURSE_VIEW where SCHEDULE_ID = $id";
                $result_class = mysql_query($query);
                while( $row_class = mysql_fetch_array($result_class,MYSQL_ASSOC) ) {
                    $temp = Array("schedule_name" =>$row_class["SCHEDULE_NAME"],"CRN" => $row_class["CRN"],
                    "department" => $row_class["DEPT"],"number" => $row_class["NUMBER"],"section" => $row_class["SECTION"]
                    ,"credits" => $row_class["CREDITS"],"instructor" => $row_class["INSTRUCTOR"],"day" => $row_class["DAY"]
                    ,"start_time" => $row_class["START_TIME"],"end_time" => $row_class["END_TIME"]
                    ,"room" => $row_class["ROOM"],"course_name" => $row_class["COURSE_NAME"]
                    ,"course_description" => $row_class["DESCRIPTION"],);
                    array_push($class_array,$temp);
                }
                $single_schedual['events'] = $event_array;
                $single_schedual['courses'] = $class_array;
                $tt = Array();
                array_push($tt,$single_schedual);
                array_push($scheduals,$tt);
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
