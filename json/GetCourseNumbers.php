<?php
$return_array = Array("success" => true);
$department = $_GET["department"];
if (empty($department)){
    $return_array["success"] = false;
    $return_array["error"] ="VALUEERROR: department empty";
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
$query = "select distinct C.NUMBER, D.NAME, D.DESCRIPTION from S12_COURSES as C inner join COURSE_DESCRIPTIONS as D on D.DEPT = C.DEPT and D.NUMBER = C.NUMBER where C.DEPT = '$department'";
$result = mysql_query($query);
if (!$result){
    $return_array["success"]=false;
    $error_message = "SQLERROR:".mysql_error();
    $return_array["error"] = $error_message;
} else {
    $return_array['number_of_rows'] = 0;
    $data = array();
    while( $row = mysql_fetch_array($result,MYSQL_ASSOC) ){
        $temp_array = Array("course_number" => $row['NUMBER'], "course_name" => $row['NAME'], "description" => $row['DESCRIPTION']);
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
