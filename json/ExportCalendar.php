<?php
include_once dirname(__FILE__)."/SQL_Functions.php";
require_once dirname(__FILE__)."/iCalcreator.class.php";
$return_array = Array("success" => true);
$schedule_id = $_GET["schedule_id"];
$year = 2012;
$semsetar = "2012s";
$semsetar_string  = "Spring 2012"; 
$semsetar_start_month = '9';
$semsetar_start_day = '1';

if (empty($schedule_id)){
    $return_array["success"] = false;
    $return_array["error"] = "VALUEERROR: schedule_id paramiter not passed";
} else {
    if ($return_array["success"] && isset($_COOKIE['SID'])){
        session_id($_COOKIE['SID']);
        session_start();
        if(isset($_SESSION['Username'])){
            $schedule = GetSchedule($_SESSION['Username'],$schedule_id);
            var_dump($schedule);
            return;
            $schedule_name = $schedule['courses'][0]['schdule_name'];
            $events = $schedule['events'];
            $courses = $schedule['courses'];
            $config = array( 'unique_id' => $schedule_name );
            $v = new vcalendar( $config );
            $tz = "America/New_York";
            $v->setProperty( 'method', 'PUBLISH' );
            $v->setProperty( "x-wr-calname", "NJIT Course Calendar" );
            $v->setProperty( "X-WR-CALDESC", "Calendar of Courses for $semsetar_string generated my Team Awsome!" );
            $v->setProperty( "X-WR-TIMEZONE", $tz );
            iCalUtilityFunctions::createTimezone( $v, $tz, $xprops );
            $xprops = array( "X-LIC-LOCATION" => $tz );
            foreach ($courses as $course){
                $vevent = & $v->newComponent( 'vevent' );
                $day = $course['day']+$semsetar_start_day;
                $start_hour = intval($course['start_time']/60);
                $start_min = $course['start_time'] % 60;
                $end_hour = intval($course['end_time']/60);
                $end_min = $course['end_time'] % 60;
                $start = array( 'year'=>$year, 'month'=>$semsetar_start_month, 'day'=>$day, 'hour'=>$start_hour, 'min'=>$start_min, 'sec'=>0 );
                $vevent->setProperty( 'dtstart', $start );
                $end = array( 'year'=>$year, 'month'=>$semsetar_start_month, 'day'=>$day, 'hour'=>$end_hour, 'min'=>$end_min, 'sec'=>0 );
                $vevent->setProperty( 'dtend', $end );
                $vevent->setProperty( 'LOCATION', $course['room'] );
                $vevent->setProperty( 'summary', $course['course_name'] );
                $vevent->setProperty( 'description', $course['description'] );
                $vevent->setProperty( 'rrule', array( 'FREQ' => 'WEEKLY', 'count' => 17));
            }
            foreach ($events as $event){
                $vevent = & $v->newComponent( 'vevent' );
                $day = $event['day']+$semsetar_start_day;
                $start_hour = intval($event['start_time']/60);
                $start_min = $event['start_time']%60;
                $end_hour = intval($event['end_time']/60);
                $end_min = $event['end_time']%60;
                $start = array( 'year'=>$year, 'month'=>$semsetar_start_month, 'day'=>$day, 'hour'=>$start_hour, 'min'=>$start_min, 'sec'=>0 );
                $vevent->setProperty( 'dtstart', $start );
                $end = array( 'year'=>$year, 'month'=>$semsetar_start_month, 'day'=>$day, 'hour'=>$end_hour, 'min'=>$end_min, 'sec'=>0 );
                $vevent->setProperty( 'dtend', $end );
                $vevent->setProperty( 'rrule', array( 'FREQ' => 'WEEKLY', 'count' => 17));
            }
            $v->returnCalendar();
        } else {
            $return_array["success"]=false;
            $error_message = "SESSIONERROR: Session Expired";
            $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
        }
    } else {
        $return_array["success"]=false;
        $error_message = "SESSIONERROR: Login Required";
        $return_array["error"]=(empty($return_array["error"]) ? $error_message : $return_array["error"] .';'. $error_message);
    }

}
if (!$return_array["success"]){
echo json_encode($return_array);
}
?>
