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
mysql_select_db('ejw3_proj');

$query = "select ID,EVENT_NAME,START_TIME,DAY,END_TIME from EVENT;";
$result = mysql_query($query);

if (!$result){
    $return_array["success"]=false;
    $error_message = "SQLERROR: Error with query -- ".mysql_error();
    $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
} else {
    $return_array['number_of_rows'] = 0;
    $data = "[";
    while( $row = mysql_fetch_array($result,MYSQL_ASSOC) ) {
        $temp_array = Array("id" => $row["ID"], "event_name" => $row["EVENT_NAME"],"day" => $row["DAY"],"start_time" => $row["START_TIME"],"end_time" => $row["END_TIME"]);
        $return_array['number_of_rows']++;
        $data=$data.json_encode($temp_array).",";
    }
    $data = substr($data,0,-1)."]";
    $return_array["Data"] = $data;
}
mysql_free_result($result);
mysql_close($connection);
echo json_encode($return_array);
?>
