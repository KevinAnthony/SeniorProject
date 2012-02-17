<?php
$id = $_GET["id"];
$return_array = Array("success" => true);

if (empty($id)){
    $return_array["success"] = false;
    $return_array["error"] = (empty($return_array["error"]) ? "VALUEERROR: id empty" : $return_array["error"] .';'. "VALUEERROR: id empty");
} else {
    $connection = mysql_connect('sql.njit.edu','ejw3_proj','ozw6OBAO') ;
    if (!$connection){
        $return_array["success"]=false;
        $error_message = "SQLERROR: Error Connectiong to database -- ".mysql_error();
        $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
        echo json_encode($return_array);
        die();
    }
    mysql_select_db('ejw3_proj');

    $query = "delete from EVENT where ID = $id;";
    $result = mysql_query($query);
    if (!$result){
        $return_array["success"]=false;
        $error_message = "SQLERROR: Error deleting row $id -- ".mysql_error();
        $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
    }
}
mysql_free_result($result);
mysql_close($connection);
echo json_encode($return_array);
?>
