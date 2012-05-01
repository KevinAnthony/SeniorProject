<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
if (empty($_GET['semester'])){
    $semester = '2012s';
} else {
    $semester = $_GET['semester'];
}
$return_array = Array("success" => true);
$result = GetDepartments($semester);
if (!$result){
    $return_array["success"]=false;
    $error_message = "SQLERROR:".mysql_error();
    $return_array["error"] = $error_message;
} else {
    $return_array['number_of_rows'] = 0;
    $data = array();
    while( $row = array_shift($result)){
        $temp_array = Array("department" => $row['dept']);
        array_push($data,$temp_array);
        $return_array['number_of_rows']++;
    }
    $return_array['data'] = $data;
    mysql_free_result($result);
}
mysql_close($connection);    
echo json_encode($return_array);
?>
