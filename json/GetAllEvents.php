<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
$return_array = Array("success" => true);

if(isset($_COOKIE['SID'])){
    session_id($_COOKIE['SID']);
    session_start();
    if(isset($_SESSION['Username'])){
        $result_array = GetEvents($_SESSION['Username']);
        if (!$result_array){
            $return_array["success"]=false;
            $error_message = "SQLERROR: Error with query -- ".mysql_error();
            $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
        } else {
            $return_array['number_of_rows'] = 0;
            $data = array();
            foreach ($result_array as $row){ 
                $temp_array = Array("id" => $row["id"], "event_name" => $row["event_name"],"day" => intval($row["day"]),"start_time" => intval($row["start_time"]),"end_time" => intval($row["end_time"]));
                array_push($data,$temp_array);
                $return_array['number_of_rows']++;
            }
            $return_array['data'] = $data;
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
