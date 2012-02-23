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
$query = "select distinct DEPT from S12_COURSES";
$result = mysql_query($query);
if (!$result){
    $return_array["success"]=false;
    $error_message = "SQLERROR:".mysql_error();
    $return_array["error"] = $error_message;
} else {
    $return_array['number_of_rows'] = 0;
    $data = array();
    while( $row = mysql_fetch_array($result,MYSQL_ASSOC) ){
        $temp_array = Array("department" => $row['DEPT']);
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
