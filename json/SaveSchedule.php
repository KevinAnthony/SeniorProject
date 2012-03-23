<?php
$return_array = Array("success" => true);
$connection = mysql_connect('sql.njit.edu','ejw3_proj','ozw6OBAO') ;
$raw_json = $_GET["data"];
$json_array = json_decode($raw_json,true);
if (!$connection){
    $return_array["success"]=false;
    $error_message = "SQLERROR: Error Connectiong to database -- ".mysql_error();
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
    echo json_encode($return_array);
    die();
}
if (!isset($json_array['schedule_name'])){
    $return_array["success"]=false;
    $error_message = "no schedule_name field passed";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
}
if (!isset($json_array['courses'])){
    $return_array["success"]=false;
    $error_message = "no courses field passed";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
}
if (!isset($json_array['events'])){
    $return_array["success"]=false;
    $error_message = "no events field passed";
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
}
if ($return_array["success"] && isset($_COOKIE['SID'])){
    session_id($_COOKIE['SID']);
    session_start();
    if(isset($_SESSION['Username'])){
        mysql_select_db('ejw3_proj');
        $username = $_SESSION['Username'];
        $query = "select EVENT_NAME from EVENT where USERNAME = '$username'";
        $result = mysql_query($query);
        $event_array = Array();
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
            array_push($event_array,$row['EVENT_NAME']);
        }
        foreach ($json_array['events'] as $value){
            if (!in_array($value,$event_array)){
                $return_array["success"]=false;
                $error_message = "event $value is not in the database";
                $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
            }
        }
        $query = "select CRN from S12_COURSES";
        $result = mysql_query($query);
        $crn = Array();
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
            array_push($crn,$row['CRN']);
        }
        foreach ($json_array['courses'] as $value){
            if (!in_array($value,$crn)){
                $return_array["success"]=false;
                $error_message = "CRN $value is not in the database";
                $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
            }
        }
        if ($return_array["success"]){
            $scedule_name = $json_array['schedule_name'];
            $query = "select ID from SCHEDULES where USER = '$username' and SCHEDULE_NAME = '$scedule_name';";
            $result = mysql_query($query);
            $schedule_id = 0;
            if (mysql_num_rows($result) == 0){
                $query = "insert into SCHEDULES set USER = '$username', SCHEDULE_NAME = '$scedule_name';";
                $result = mysql_query($query);
                $query = "select ID from SCHEDULES where USER = '$username' and SCHEDULE_NAME = '$scedule_name';";
                $result = mysql_query($query);
                $row = mysql_fetch_array($result, MYSQL_ASSOC);
                $schedule_id = $row["ID"];
            } else {
                $row = mysql_fetch_array($result, MYSQL_ASSOC);
                $schedule_id = $row["ID"];
            }
            $return_array["schedule_id"] = $schedule_id;
            foreach ($json_array['courses'] as $value){
                $result = mysql_query("insert ignore into SCHEDULES_COURSES set ID = $schedule_id, CRN = $value;");
            }
            $query = "select ID from EVENT where EVENT_NAME in ('";
            foreach ($json_array['events'] as $value){
                $query = $query.$value."','";
            }
            $query = substr($query ,0,-2);
            $query = $query.");";
            $result = mysql_query($query);
            while ($row = mysql_fetch_array($result,MYSQL_NUM)){
                $EID = $row[0];
                mysql_query("insert into SCHEDULES_EVENTS set ID = $schedule_id, EVENT_ID = $EID;");
            }
            $result = mysql_query($query);
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
mysql_close($connection);

echo json_encode($return_array);
?>
