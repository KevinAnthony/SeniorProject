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
        
        $query = "select ID,EVENT_NAME,START_TIME,DAY,END_TIME from EVENT where username = '$username';";
        $result = mysql_query($query);
        if (!$result){
            $return_array["success"]=false;
            $error_message = "SQLERROR: Error with query -- ".mysql_error();
            $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
        } else {
            $return_array['number_of_rows'] = 0;
            $data = array();
            while( $row = mysql_fetch_array($result,MYSQL_ASSOC) ) {
                $temp_array = Array("id" => $row["ID"], "event_name" => $row["EVENT_NAME"],"day" => intval($row["DAY"]),"start_time" => intval($row["START_TIME"]),"end_time" => intval($row["END_TIME"]));
                array_push($data,$temp_array);
                $return_array['number_of_rows']++;
            }
            $return_array['data'] = $data;
        }
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
