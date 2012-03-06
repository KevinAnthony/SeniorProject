<?php
$return_array = Array("success" => true);
if (isset($_GET["debug"])){
    $username = $_GET["username"];
    $password = $_GET["password"];
} else {
    $username = $_POST["username"];
    $password = $_POST["password"];
}
if (empty($username) or empty($password)){
    $return_array["success"] = false;
    $return_array["error"] = "Parameters not passed username or password";
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
    $query = "select * from USER where USERNAME = '$escaped_username' AND PASSWORD = '$escaped_password_hash';";
    $result = mysql_query($query);
    if (!$result){
        $count = mysql_num_rows($result);
        $return_array["success"]=false;
        $error_message = "SQLERROR: with username or password -- ".mysql_error();
        $return_array["printable_error_message"] = "Invalid Username or Password";
        $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
    }else{
        $count = mysql_num_rows($result);
        if ($count > 1){
            $return_array["success"]=false;
            $error_message = "SQLERROR: Search returned more then one row";
            $return_array["printable_error_message"] = "Invalid Username or Password";
            $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
        }else if ($count < 1){
            $return_array["success"]=false;
            $error_message = "SQLERROR: Search returned more then no rows";
            $return_array["printable_error_message"] = "Invalid Username or Password";
            $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
        } else {
            session_destroy();
            session_start();
            $_SESSION['Username'] = $username;
            $return_array['SID']=session_id();
            setcookie('SID',session_id());
        }
    }
    mysql_free_result($result);
    mysql_close($connection);
}
echo json_encode($return_array);
?>
