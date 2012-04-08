<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
$return_array = Array("success" => true);
if (isset($_GET["debug"])){
    $username = $_GET["username"];
    $password = $_GET["password"];
} else {
    $username = $_POST["username"];
    $password = $_POST["password"];
}
$username = 'kevin';
$password = 'Inverse';
if (empty($username) or empty($password)){
    $return_array["success"] = false;
    $return_array["error"] = "Parameters not passed username or password";
} else {
    $password_hash = crypt($password,'$1$kevinant$');
    if (!CheckCredentials($username,$password_hash)){
        $return_array["success"]=false;
        $error_message = "SQLERROR: with username or password -- ".mysql_error();
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
echo json_encode($return_array);
?>
