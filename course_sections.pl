#!/usr/bin/perl

use DBI;
use DBD::mysql;
use Switch;
	
$debug="all";
	
open data, "./CS113.aspx" or die $!;
$dataSource="dbi:mysql:ejw3_proj:sql.njit.edu:3306";
$user = "ejw3_proj";
$password="ozw6OBAO";
$dbh = DBI->connect($dataSource, $user, $password) or die  "Can't connect";

# skip to course dept, name, , and num
$_=<data> while($_ !~ /ctl10_lblCourse/);
chomp;
s/\s+\<span id\=\"ctl10_lblCourse\"\>(.*)\<\/span\>.*/\1/;
($course, $name)=split('-',$_);
($dept, $num) = split('\s+', $course);
print("Course: $course\n") if ($debug =~ /all/ || $debug =~ /vars/);
			
# skip to the course description
$_=<data> while ($_ !~ /ctl10_lblCourseDesc/);
chomp;
s/.*\<span id\=\"ctl10_lblCourseDesc\"\>(.*)\<\/span\>\<\/p\>/\1/;		
	
# separate prerequisites from the description
if ($_ =~ /^Prerequisite/){
	@description=split(/\./);
	$prereq=shift(@description);	
	push(@prerequisites, "$course $num"=>$prereq); # will be used / fixed later
	$description=join(/ /,@description);
}
else {
	$description=$_;
}
print("Description: $description\n") if ($debug =~ /all/ || $debug =~ /vars/);
			
$query = "INSERT INTO COURSE_DESCRIPTIONS VALUES (\"".$name."\",\"".$dept."\",\"".$num."\",\"".$description."\")";
print($query) if ($debug =~ /all/ || $debug =~ /query/);
my $sth = $dbh->prepare($query) or die "Couldn't prepare statement: " . $dbh->errstr;
$sth->execute();
			
#iterates once per section
while(<data>){
	# section
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
			
	# extract day/time, remove html tags
	$_=<data>; $_=<data>;
	s/.*\"ctl10_gv_sectionTable_ct.*_lblDays\"\>(.*)\<.*/\1/;
			
	# NOTE: right now the day and time part only works for courses that meet at the same times
	#	on different days.
	# separate day(s) from time(s)
	($days, $times)=split(':');
			
	# separate days from each other if multiple days, convert days to numbers for db storage
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
	$start_time =~ s/^0//;		# leading zeroes in the times screw up the calculations
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
							
	# room number
	$_=<data> while( $_ !~ /room/);
	$_=<data>;		
	chomp;
	s/\s+\<span id\=\"ctl10_gv_sectionTable_ct.*_lblRoom\"\>(.*)\<\/span\>/\1/;
	$roomNum = $_;
	print("Room: $roomNum\n") if ($debug =~ /all/ || $debug =~ /vars/);
	
	# status (needed to prevent closed courses from messing everything up)
	# later it will be updated by a different script that can run frequently to update status
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
	s/.*class=\"credits\"\>(.*)\<\/td>/\1/;
	$credits=$_;
	print("Credits: $credits\n") if ($debug =~ /all/ || $debug =~ /vars/);
		
	if ($status !~ /Cancelled/i){
		$query = "INSERT INTO S12_COURSES VALUES (\"" . $callNum."\",\"".$dept."\",\"".$num."\",\"".$section."\",\"".$credits."\",\"". $instructor."\");";
		print($query) if ($debug =~ /query/ || $debug =~ /all/);
		$sth = $dbh->prepare($query) or die $dbh->errstr;
		$sth->execute();
				
		while ($day=shift(@dayNums)) {
			$query="INSERT INTO S12_COURSE_TIMES (\"".$callNum."\",\"". shift(@day)."\",\"". $start_time."\",\"". $end_time."\",\"". $roomNum."\");";
			print($query) if ($debug =~ /query/ || $debug =~ /all/);
			$sth = $dbh->prepare()	or die $dbh->errstr;
			$sth->execute();	
		}
		}
		else {	# this empties @dayNums if the section was cancelled
			$_=$day while ($day = shift(@dayNums));
		}

	}
	$dbh->disconnect();
	close data;
