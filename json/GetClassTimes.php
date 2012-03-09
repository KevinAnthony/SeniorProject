<?php
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
$connection = mysql_connect('sql.njit.edu','ejw3_proj','ozw6OBAO') ;

if (!$connection){
    $return_array["success"]=false;
    $error_message = "SQLERROR: Error Connectiong to database -- ".mysql_error();
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
    echo json_encode($return_array);
    die();
}

mysql_select_db('ejw3_proj');
$query = "select * from S12_COURSE_TIMES as T inner join S12_COURSES as C on T.CRN=C.CRN";
$query = $query." where C.DEPT = '$department' and C.NUMBER = $course_number order by T.CRN,T.DAY;";
$result = mysql_query($query);
if (!$result){
    $return_array["success"]=false;
    $error_message = "SQLERROR:".mysql_error();
    $return_array["error"] = $error_message;
} else {
    $return_array['number_of_rows'] = 0;
    $data = array();
    $row = mysql_fetch_array($result,MYSQL_ASSOC);
    while( $row ){
        $i = 0;
        $temp_array = Array("CRN" => $row['CRN'], 
        "instructor"=>$row["INSTRUCTOR"]);
        $day = Array();
        $start_time = Array();
        $end_time = Array();
        $room = Array();
        $day[$i] = intval($row["DAY"]);
        $start_time[$i] = intval($row["START_TIME"]);
        $end_time[$i] = intval($row["END_TIME"]);
        $room[$i] = $row["ROOM"];
        $i++;
        $row = mysql_fetch_array($result,MYSQL_ASSOC);
        while (($row) && $row["CRN"] == $temp_array["CRN"]){
            $day[$i] = intval($row["DAY"]);
            $start_time[$i] = intval($row["START_TIME"]);
            $end_time[$i] = intval($row["END_TIME"]);
            $room[$i] = $row["ROOM"];
            $i++;
            $row = mysql_fetch_array($result,MYSQL_ASSOC);
        }
        $temp_array['day'] = $day;
        $temp_array['start_time'] = $start_time;
        $temp_array['end_time'] = $end_time;
        $temp_array['room'] = $room;
        array_push($data,$temp_array);
        $return_array['number_of_rows']++;
    }
    $return_array['data'] = $data;
    mysql_free_result($result);
}
mysql_close($connection);    

echo json_encode($return_array);
echo "\n";
?>
