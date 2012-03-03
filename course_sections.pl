#!/usr/bin/perl

use Switch;
	
$debug="query";
	
open data, "./Math111.aspx" or die $!;

$_=<data> while($_ !~ /ctl10_lblCourse/);
chomp;
s/\s+\<span id\=\"ctl10_lblCourse\"\>(.*)\<\/span\>.*/\1/;
($course, $name)=split('-',$_);
$name =~ s/^\s+//;
($dept, $num) = split('\s+', $course);
print("Course: $course\n") if ($debug =~ /all/ || $debug =~ /vars/);
			
$_=<data> while ($_ !~ /ctl10_lblCourseDesc/);
chomp;
s/.*\<span id\=\"ctl10_lblCourseDesc\"\>(.*)\<\/span\>.*/\1/;		
	
if ($_ =~ /^Prerequisite/){
	@description=split(/\./);
	$prereq=shift(@description);	
	push(@prerequisites, "$course $num"=>$prereq); # will be used later
	$description=join(/\.  /,@description);
}
else {
	$description=$_;
}
print("Description: $description\n") if ($debug =~ /all/ || $debug =~ /vars/);
			
$query = "INSERT INTO COURSE_DESCRIPTIONS VALUES (\"".$name."\",\"".$dept."\",\"".$num."\",\"".$description."\");";
print("$query\n") if ($debug =~ /all/ || $debug =~ /query/);
system("echo \'$query\' > courses.txt");   ## change to >> when ready to use this script for all classes
 			
while($_=<data>){
	# section num
	$_=<data> while ($_ !~ /class\=\"section\"\>/);
	$_=<data>;
	chomp;
	s/\s+(.*)<br.*/\1/;
	$section=$_;
	print("Section: $section\n") if ( $debug =~ /all/ || $debug =~ /vars/);
	
	# call number
	$_=<data> while ($_ !~ /\<td class\=\"call\"\>/);
	$_=<data>;
	chomp;
	s/\s+\<span\>(.*)<br\/>.*/\1/;
	$callNum=$_;
	print("CRN: $callNum\n") if ($debug =~ /all/ || $debug =~ /vars/);
			
	# Day / Time
	$_=<data>; $_=<data>;
	s/.*\"ctl10_gv_sectionTable_ct.*_lblDays\"\>(.*)\<.*/\1/;
			
	@meetings = split(/\<br \/\>/);
	$num_meetings=scalar(@meetings);
	
	# Room
	$_=<data> while ($_ !~ /room/);
	$_=<data>;
	chomp;
	s/\\r?\\n//;
	s/\s+\<span id\=\"ctl10_gv_sectionTable_ct.*_lblRoom\"\>(.*)\<\/span\>/\1/;
	@rooms = split(/\<br \/\>/);
	print("Room: @rooms\n") if ($debug =~ /all/ || $debug =~ /vars/);
	
	
	while ($meeting = shift(@meetings)){
		$roomNum = shift(@rooms);
		s/\\r?\\n//;
		($days, $times)=split(':', $meeting);
		@days=split(undef, $days);
		while($day=shift(@days)){
			switch ($day){
				case ('M') 	{push(@dayNums,1);}
				case ('T') 	{push(@dayNums,2);}
				case ('W')	{push(@dayNums,3);}
				case ('R')	{push(@dayNums,4);}
				case ('F')	{push(@dayNums,5);}
				case ('S')	{push(@dayNums,6);}
				else		{push(@dayNums,0);} # used for online classes
			}
		}
				
		#separate start times from end times
		($start_time, $end_time)=split('-', $times);
		$start_time =~ s/^0//;		
		$start_hours = ($start_time =~ /PM/ && $start_time < 1159) ? 12 : 0;
		$start_time =~ s/[AP]M//;
		$start_minutes = $start_time % 100;
		$start_hours += ($start_time - $start_minutes) / 100;
		$start_time = ($start_hours * 60) + $start_minutes;
		print ("Start Time: $start_time\n") if ($debug =~ /all/ || $debug =~ /vars/);
	
		$end_time =~ s/^0//;
		$end_hours = ($end_time =~ /PM/ && $end_time < 1159) ? 12 : 0;
		$end_time =~ s/[AP]M//;
		$end_minutes = $end_minutes % 100;
		$end_hours += ($end_time - $end_minutes) /100;
		$end_time = ($end_hours * 60 ) + $end_minutes;
		print ("End Time: $end_time\n") if ($debug =~ /all/ || $debug =~ /vars/);
		
								
		if ($num_meetings - scalar(@meetings)==1){	
			# status used only for determining whether to insert tuples
			$_=<data> while ($_ !~ /span id\=\"ctl10_gv_sectionTable_ct.*_lblStatus\"/);
			s/.*span id\=\"ctl10_gv_sectionTable_ct.*_lblStatus\"(.*)\<\/span.*/\1/;
			$status = $_;
			print("Status: $status\n") if ($debug =~ /all/ || $debug =~ /vars/);
				
			# instructor
			$_=<data> while ($_ !~ /\.aspx\?persid\=.*/);
			chomp;
			s/.*\.aspx\?persid\=.*\"\>(.*)\<.*/\1/;
			$instructor=$_;
			print("Instructor: $instructor\n") if ($debug =~ /all/ || $debug =~ /vars/);
				
			# credits
			$_=<data> while ($_ !~ /credits/);
			chomp;
			s/\\r?\\n//;
			s/.*class=\"credits\"\>(.*)\<\/td>/\1/;
			$credits=$_;
			print("Credits: $credits\n") if ($debug =~ /all/ || $debug =~ /vars/);
		}	
			
		if ($status !~ /Cancelled/i){
			if ( $num_meetings - scalar(@meetings)==1 ){
				$query = "INSERT INTO S12_COURSES VALUES (\"" . $callNum."\",\"".$dept."\",\"".$num."\",\"".$section."\",\"".$credits."\",\"". $instructor."\");";
				print("$query\n") if ($debug =~ /query/ || $debug =~ /all/);
				system("echo \'$query\' >> courses.txt");
			}
					
			while ($day=shift(@dayNums)) {
				$query="INSERT INTO S12_COURSE_TIMES VALUES (\"".$callNum."\",\"". $day."\",\"". $start_time."\",\"". $end_time."\",\"". $roomNum."\");";
				print("$query\n") if ($debug =~ /query/ || $debug =~ /all/);
				system("echo \'$query\' >> courses.txt");
			}
		}
		else {	
			$_=$day while ($day = shift(@dayNums));
		}
	
	}
}	

$dbh->disconnect();
close data;
	
