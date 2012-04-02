<?php
$return_array = Array("success" => true);
if(isset($_COOKIE['SID'])){
    session_id($_COOKIE['SID']);
    session_start();
    if(isset($_SESSION['Username'])){
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
        session_destroy();   
    } else {
        $return_array['success'] = false;
        $return_array['error'] = "SessionID Does not Exist";
    }
} else {
    $return_array['success'] = false;
    $return_array['error'] = "No Session Infomation";
}
echo json_encode($return_array);
?>
