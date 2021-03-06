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

    while ($row = mysql_fetch_assoc($result)){
        array_push($rows, $row);	
    }

    mysql_free_result($result);

    return $rows;

}


/* QUERIES */	
function DeleteEvent($id){
    $result=query("DELETE FROM event WHERE ID=$id");	// Return true or false if attempt to delete event that doesn't exist? true
    query("DELETE FROM schedule_event WHERE event_id='$id'");  // only until foreign key constraints decide to start working
    return $result ? true : false;
}	

function DeleteSchedule($user, $schedule_name){
    db_connect();
    $escaped_schedule_name=mysql_real_escape_string($schedule_name);
    $id=mysql_query("SELECT schedule_id FROM schedule WHERE user='$user' AND schedule_name='$escaped_schedule_name'");
    if (mysql_num_rows($id) == 0 || (!$id)) { return false; }  // error or schedule doesn't exist
    $id=mysql_fetch_array($id);
    $id=$id["schedule_id"];

    $result=mysql_query("DELETE FROM schedule WHERE user='$user' AND schedule_name='$escaped_schedule_name'");
    mysql_query("DELETE FROM schedule_course WHERE schedule_id='$id'");  // only until foreign key constraints decide to start working
    mysql_query("DELETE FROM schedule_event WHERE schedule_id ='$id'");

    return ($result && mysql_num_rows($result) > 0 ) ? true : false;  // return false if error or if schedule doesn't exist
}

function GetEvents($user){
    $result = query("SELECT id, event_name, start_time, end_time, day FROM event WHERE username='$user'");

    return associative($result);  
}	

function GetClassTimes($department, $course_number, $semester){ // fixed join by adding a semester condition
    $result = query("SELECT * FROM course_times T INNER JOIN courses C on T.crn=C.crn AND T.semester=C.semester".
            " WHERE C.dept = '$department' AND C.number = $course_number AND C.semester='$semester'".
            "ORDER BY T.crn,T.day;");
    if (($result) && (mysql_num_rows($result) == 0)){
        return -1;
    }
    return associative($result);
}

function GetAllCourseNumbers($department, $semester){
    return associative(query("SELECT DISTINCT C.number, D.name, D.description FROM courses C INNER JOIN ".
                "course_description D ON D.dept = C.dept AND D.number = C.number WHERE C.dept = '$department'".
                "and C.semester='$semester'"));
}

function GetSchedules($username){
    $result = query("SELECT schedule_id, semester FROM schedule where user='$username'");
    $return = array();
    if (mysql_num_rows($result) == 0){ 
        return -1;
    }

    while( $row = mysql_fetch_assoc($result) ) {
        $id = $row["schedule_id"];
        $events=array();
        $courses=array();
	$semester=$row["semester"];
	
        array_push($events, associative(query("SELECT * FROM schedule_event_view WHERE schedule_id='$id'")));
        array_push($courses,associative(query("SELECT * FROM schedule_course_view WHERE schedule_id='$id' order by crn")));
        
	$temp_array=array();
	$temp_array{"semester"}=$semester;
        $temp_array{"events"}=$events;
        $temp_array{"courses"}=$courses;
        array_push($return,$temp_array);
    }
    return $return;
}

function GetSchedule($id){
    $return=array();
    $return{"events"}=associative(query("SELECT * FROM schedule_event_view WHERE schedule_id='$id'"));
    $return{"courses"}=associative(query("SELECT * FROM schedule_course_view WHERE schedule_id='$id'"));
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

    mysql_query("INSERT IGNORE INTO schedule (user, schedule_name, semester) VALUES ('$user', '$escaped_schedule_name', '$semester')");
    $result=mysql_query("SELECT MAX(schedule_id) AS schedule_id FROM schedule WHERE user='$user' AND schedule_name='$escaped_schedule_name' AND semester='$semester'");


    $row = mysql_fetch_array($result);
    $id = $row["schedule_id"];

    $temp = join(',', $courses);
    mysql_query("DELETE FROM schedule_course WHERE schedule_id='$id' AND semester='$semester' AND crn NOT IN ($temp)");


    while ($course = array_shift($courses)){
        $result=mysql_query("INSERT IGNORE INTO schedule_course VALUES ('$semester', '$id', '$course')");
        if ($result || mysql_errno() == 1062 ) { continue; } else { return false; }    # ignore duplicate entry errors
    }

    $temp = join(',', $events);
    mysql_query("DELETE FROM schedule_event WHERE schedule_id='$id' AND semester='$semester' AND event_id NOT IN ($temp)");

    while ($event = array_shift($events)){
        $result=mysql_query("INSERT IGNORE INTO schedule_event VALUES ('$semester','$id', '$event')");
        if ($result || mysql_errno() == 1062) { continue; } else { return false; }
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
	// DAYOFWEEK returns a numerical value for weekday, i think with 1 as sunday and 7 as saturday
    $start = associative(query("SELECT Date, DAYOFWEEK(Date) AS weekday, description FROM dates WHERE semester='$semester' AND type=\"start\""));
    $last = associative(query("SELECT Date, DAYOFWEEK(Date) AS weekday, description FROM dates WHERE semester='$semester' AND type=\"last\""));
    $closed = associative(query("SELECT Date, DAYOFWEEK(Date) AS weekday, description FROM dates WHERE semester='$semester' AND type=\"closed\""));
	$numWeeks = mysql_fetch_assoc(query("SELECT (DATEDIFF((SELECT Date FROM dates WHERE semester='$semester' AND type='last'), (SELECT Date FROM dates WHERE semester='$semester' AND type='start'))/7) AS numWeeks"));
	$numWeeks=$numWeeks["numWeeks"];

    $dates = array();
    $dates{"start"} = $start;
    $dates{"end"} = $last;
    $dates{"closed"} = $closed;
    $dates{"numWeeks"}=$numWeeks;
    return $dates;
}

function GetEvent($id){
    return associative(query("SELECT * FROM event WHERE id='$id'"));
}

function GetPrereq($course){
    return associative(query("SELECT prerequisite FROM prerequisites WHERE course='$course'"));
}

function GetCourses($constraint_array, $semester){  // UNTESTED (Don't use yet)
    $query="SELECT * FROM courses NATURAL JOIN course_times WHERE semester='$semester' ";

    $constraints = array_keys($constraint_array);

    while ($constraint = array_shift($constraints)){
        switch ($constraint) {
            case "start-before":
                $query .= "AND start_time <'".$constraint_array[$constraint]."' ";
                break;
            case "start-after":
                $query .= "AND start_time >'".$constraint_array[$constraint]."' ";
                break;
            case "end-before":
                $query .= "AND end_time <'".$constraint_array[$constraint]."' ";
                break;
            case "end-after":
                $query .= "AND end_time >'".$constraint_array[$constraint]."' ";
                break;
            case "days":
                $query .= "AND day='".array_shift($constraint_array[$constraint])."' ";
                while ($day = array_shift($constraint_array[$constraint])){
                    $query .= "OR day='$day' ";
                }
                break;
            case "level":
                $query .= "AND number REGEXP '^$constraint_array[$constraint]' ";
                while ($level = array_shift($constraint_array[$constraint])){
                    $query .= "OR number REGEXP '^$level' ";
                }
                break;
            case "dept":
                $query .= "AND dept='$constraint_array[$constraint]' ";
                break;
            case "not-prereqs":
                while ($prereq = array_shift($constraint_array[$constraints])){
                    $query .= "AND CONCAT(dept,\" \", num) NOT IN (SELECT course FROM prerequisites WHERE prerequisite REGEXP '$prereq' ";
                }
                break;	
        }
    }

    return associative(query($query));
}

?>
