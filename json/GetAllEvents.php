<?php

$return_array = Array();
for ($x = 0; $x < 50; $x++){
    $start_time = rand(0,1200);
    $end_time = rand($start_time,1440);
    $day = rand(0,5);
    $temp_array = Array("start_time" => $start_time,"end_time" => $end_time,"start_day" =>$day);
    array_push($return_array,$temp_array);
}

echo json_encode($return_array);
?>
