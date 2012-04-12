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
$result = GetClassTimes($department,$course_number,'2012s');
if ($result == -1){
    $return_array["success"]=false;
    $error_message = "SQLERROR: No Rows Returned";
    $return_array["error"] = $error_message;
} else {
    if (!$result){
        $return_array["success"]=false;
        $error_message = "SQLERROR:".mysql_error();
        $return_array["error"] = $error_message;
    } else {
        $return_array['number_of_rows'] = 0;
        $data = array();
        $row = array_shift($result);
        while( $row ){
            $i = 0;
            $temp_array = Array("CRN" => $row['crn'], 
                    "instructor"=>$row["instructor"]);
            $day = Array();
            $start_time = Array();
            $end_time = Array();
            $room = Array();
            $day[$i] = intval($row["day"]);
            $start_time[$i] = intval($row["start_time"]);
            $end_time[$i] = intval($row["end_time"]);
            $room[$i] = $row["room"];
            $i++;
            $row = array_shift($result);

            while (($row) && $row["crn"] == $temp_array["CRN"]){
                $day[$i] = intval($row["day"]);
                $start_time[$i] = intval($row["start_time"]);
                $end_time[$i] = intval($row["end_time"]);
                $room[$i] = $row["room"];
                $i++;
                $row = array_shift($result);
            }
            $temp_array['day'] = $day;
            $temp_array['start_time'] = $start_time;
            $temp_array['end_time'] = $end_time;
            $temp_array['room'] = $room;
            array_push($data,$temp_array);
            $return_array['number_of_rows']++;
        }
        $return_array['data'] = $data;
    }
}
echo json_encode($return_array);
?>
