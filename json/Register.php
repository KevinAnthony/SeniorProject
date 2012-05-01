<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
if (isset($_GET["debug"])){
    $username = $_GET["username"];
    $password = $_GET["password"];
} else {
    $username = $_POST["username"];
    $password = $_POST["password"];
}
$return_array = Array("success" => true);

if ((empty($username)) or (empty($password))){
    $return_array["success"] = false;
    $return_array["error"] = "username or password empty";
} else {
    $password_hash = crypt($password,'$1$kevinant$');
    if (!RegisterUser($username,$password_hash)){
        $return_array["success"]=false;
        $error_message = "SQLERROR: Error with query -- ".mysql_error();
        $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
    }
}
echo json_encode($return_array);
?>
