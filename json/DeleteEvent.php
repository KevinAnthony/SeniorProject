<?php
$id = $_GET["id"];
$return_array = Array("success" => true);

if (empty($id)){
    $return_array["success"] = false;
    $return_array["error"] = (empty($return_array["error"]) ? "VALUEERROR: id empty" : $return_array["error"] .';'. "VALUEERROR: id empty");
}
if ($return_array["success"]){
    $return_array["id"] = $id;
}

echo json_encode($return_array);
?>
