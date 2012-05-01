<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
$return_array = Array("success" => true);
$department = $_GET["department"];
if (empty($_GET['semester'])){
    $semester = '2012s';
} else {
    $semester = $_GET['semester'];
}

if (empty($department)){
    $return_array["success"] = false;
    $return_array["error"] ="VALUEERROR: department empty";
    echo json_encode($return_array);
    die();
}

$result = GetAllCourseNumbers($department,$semester);
if (!$result){
    $return_array["success"]=false;
    $error_message = "SQLERROR:".mysql_error();
    $return_array["error"] = $error_message;
} else {
    $return_array['number_of_rows'] = 0;
    $data = array();
    while( $row = array_shift($result) ){
        $temp_array = Array("course_number" => $row['number'], "course_name" => $row['name'], "description" => $row['description']);
        array_push($data,$temp_array);
        $return_array['number_of_rows']++;
    }
    $return_array['data'] = $data;
}

echo json_encode($return_array);
?>
