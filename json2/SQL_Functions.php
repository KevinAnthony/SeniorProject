<?php

function db_connect(){
    $connection = mysql_connect("sql.njit.edu", "ejw3_proj", "ozw6OBAO");
    if (!$connection){
        return("Could not connect to MySQL database: ".mysql_error());
    }
    mysql_select_db("ejw3_proj", $connection);
}	

function query ($query_str){
    db_connect();

    $result=mysql_query($query_str) or die( mysql_error());
    mysql_close();

    return $result;
}

function associative($result){

    $rows = array();

    while ($row = mysql_fetch_array($result)){
        array_push($rows, $row);	
    }

    mysql_free_result($result);

    return $rows;

}


/* QUERIES */	
function DeleteEvent($id, $user){
    $result=query("DELETE FROM event WHERE user='$user' AND ID=$id");	// Return true or false if attempt to delete event that doesn't exist?
    return $result ? true : false;
}	

function GetEvents($user){
    $result = query("SELECT id, event_name, start_time, end_time, day FROM event WHERE username='$user'");

    return associative($result);  
}	

function GetClassTimes($department, $course_number, $semester){
    $result = query("SELECT * FROM course_times T INNER JOIN courses C on T.crn=C.crn ".
            " WHERE C.dept = '$department' AND C.number = $course_number AND C.semester='$semester'".
            "ORDER BY T.crn,T.day;");

    return associative($result);
}

function GetAllCourseNumbers($department, $semester){
    return associative(query("SELECT DISTINCT C.number, D.name, D.description FROM courses C INNER JOIN ".
                "course_description D ON D.dept = C.dept AND D.number = C.number WHERE C.dept = '$department'".
                "and C.semester='$semester'"));
}

function GetSchedules($username){
    $result = query("SELECT schedule_id FROM schedule where user = '$username'");
    $return = array();
    if (mysql_num_rows($result) == 0){ 
        return -1;
    }
    while( $row = mysql_fetch_assoc($result) ) {
        $id = $row["schedule_id"];
        $events = associative(query("SELECT * FROM schedule_event_view WHERE schedule_id='$id'"));
        $courses = associative(query("SELECT * FROM schedule_course_view WHERE schedule_id='$id'"));

        $temp_array=array();
        $temp_array{"events"}=$events;
        $temp_array{"courses"}=$courses;
        array_push($return,$temp_array);
    }
    return $return;
}

function GetDepartments($semester){
    $result = query("SELECT DISTINCT dept FROM courses where semester='$semester' ORDER BY dept");
    return associative($result);
}

function CheckCredentials($username, $password){
    db_connect();
    $result = mysql_query("SELECT * FROM user WHERE username='".mysql_real_escape_string($username)."' AND password='".mysql_real_escape_string($password)."'");
    mysql_close();
    return (mysql_num_rows($result) == 1) ? true : false;
}

function RegisterUser($username, $password){
    db_connect();
    $result = query("INSERT INTO user VALUES ('".mysql_real_escape_string($username)."', '".mysql_real_escape_string($password)."')");
    mysql_close();
    return ($result) ? true : false;
}

function SaveEvent($event_name, $start, $end, $day, $username){	
    $result = query("INSERT INTO event(event_name, start_time, end_time, day, username) VALUES ".
            "('$event_name', '$start', '$end', '$day', '$username')");
    return ($result) ? true : false;
}

function SaveSchedule($semester, $user, $schedule_name, $courses, $events){
    db_connect();
    $escaped_schedule_name = mysql_real_escape_string($schedule_name);

	mysql_query("INSERT IGNORE INTO schedule (user, schedule_name) VALUES ('$user', '$escaped_schedule_name')");
	$result=mysql_query("SELECT MAX(schedule_id) AS schedule_id FROM schedule WHERE user='$user' AND schedule_name='$escaped_schedule_name'");
	
	
	$row = mysql_fetch_array($result);
	$id = $row["schedule_id"];

	$temp = join(',', $courses);
	mysql_query("DELETE FROM schedule_course WHERE schedule_id='$id' AND crn NOT IN ($temp)");
	

    while ($course = array_shift($courses)){
        $result=mysql_query("INSERT INTO schedule_course VALUES ('$semester', '$id', '$course')");
        if ($result || mysql_errno() == 1062 ) { continue; } else { return false; }    # ignore duplicate entry errors
    }

	$temp = join(',', $events);
    mysql_query("DELETE FROM schedule_event WHERE schedule_id='$id' AND event_id NOT IN ($temp)");

    while ($event = array_shift($events)){
        $result=mysql_query("INSERT INTO schedule_event VALUES ('$semester','$id', '$event')");
        if ($result || mysql_errno() == 1062) { continue; } else { echo "No Here<br>\n";return false; }
    }
    mysql_close();
    return true;
}

function get_event_names ($username){		
    return associative(query("SELECT event_name FROM event WHERE username='$username'"));
}

function get_all_crn($semester){		
    return associative(query("SELECT crn FROM courses WHERE semester='$semester'"));
}

function get_dates($semester){
    $start = assoiciative(query("SELECT month, day, description FROM dates WHERE semester='$semester' AND type=\"start\""));
    $last = associative(query("SELECT month, day, description FROM dates WHERE semester='$semester' AND type=\"last\""));
    $closed = associative(query("SELECT month, day, description FROM dates WHRE semester='$semester' AND type=\"closed\""));

    $dates = array();
    $dates{"start"} = $start;
    $dates{"end"} = $last;
    $dates{"closed"} = $closed;

    return $dates;
}

function GetEvent($id){
    return associative(query("SELECT * FROM event WHERE id='$id'"));
}
?>
