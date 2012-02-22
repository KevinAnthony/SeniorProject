<?php
$username = $_POST["username"];
$password = $_POST["password"];

$return_array = Array("success" => true);

if ((empty($username)) or (empty($password))){
    $return_array["success"] = false;
    $return_array["error"] = "username or password empty";
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
    $password_hash = crypt($password,'$1$kevinant$');
    $escaped_username = mysql_real_escape_string($username);
    $escaped_password_hash = mysql_real_escape_string($password_hash);
    $query = "insert into USER (USERNAME,PASSWORD) VALUES ('$escaped_username','$escaped_password_hash');";
    $result = mysql_query($query);
    if (!$result){
        $return_array["success"]=false;
        $error_message = "SQLERROR: Error with query -- ".mysql_error();
        $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
    }
    mysql_free_result($result);
    mysql_close($connection);
}
echo json_encode($return_array);
?>
