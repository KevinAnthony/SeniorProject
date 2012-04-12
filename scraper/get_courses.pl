#!/usr/bin/perl

use Switch;

sub getPage{
	$url = shift;
	$url="\'$url\'";
	$outFile=shift;
	$semester=shift;
	print("Getting $url pages ......\n");
	system("/usr/bin/php ./scraper.php $url $semester > $outFile"); 
	sleep(9);
	print("Done retrieving $url.\n");
	
}

sub parseSubjects{
	
	$file = shift;
	$_=<$file> while ($_ !~ /courseList_section/);
	chomp;
	
	# get rid of all the garbage since the entire subject list is on one line
	s/<div class=\'col\'>(.*?)<span>//g;
	s/<\/div>(.*?)<span>//g;
	s/<div class='courseList_section'>(.*?)<span>//g;
	s/<\/div>//g;
	s/<\/span>//g;
	s/<span>//g;
	s/<a href=(.*?)>//g;
	
	$subjects=$_;
	@temp = split('</a>', $subjects);
	while ($subject = shift(@temp)){
		$subject =~ s/([A-Z]{1,4})\s+-.*/\1/;
		$subject =~ s/([A-Z]{1,4}[0-9]{3})\s+-.*/\1/;
		$subject =~ s/\s+//g;
		push(@subjects, $subject);
	}
	
	return @subjects;
	
}

sub parseClasses{
	$file = shift;
	#$_= <$file> while ($_ !~ /<a id=\"ctl10_GridView1_ct.*_lbCourse\"/);
	
	while (<$file>){
		next if ($_ !~ /<a id=\"ctl10_GridView1_ctl.*_lbCourse\"/);
		chomp;
		s/.*\<strong\>(.*)\<\/strong\>/\1/;
		if ($_ =~ /R[0-9]{3}/) { s/(^R[0-9]{3})\s?([0-9]{3}[A-Z]?)/\1 \2/; } 
		else { s/([A-Za-z]{1,5})\s?([0-9]{3}[A-Z]?)/\1  \2/; }
		($subject, $class) = split;
		push (@classes, $class);
	}
	
	# needed because of special topics courses with same numbers but different names (prevent duplicates)
	undef %saw;
	@classes = grep(!$saw{$_}++, @classes);
	
	return @classes;	
	
}

sub parseSections{
	$file = shift;
	$semester = shift;
	$_=<$file> while($_ !~ /ctl10_lblCourse/);
	chomp;
	s/\s+\<span id\=\"ctl10_lblCourse\"\>(.*)\<\/span\>.*/\1/;
	($course, $name)= $_ !~ /co-op/i ? split('-',$_) : split(' - ', $_);
	$name =~ s/^\s+//;
	$course=~s/([A-Z]{1-4})\s?([0-9]{3}[A-Z]?)/\1 \2/;	
	($dept, $num) = split('\s+', $course);
	print("Course: $course\n") if ($debug =~ /all/ || $debug =~ /vars/);
				
	$_=<$file> while ($_ !~ /ctl10_lblCourseDesc/);
	chomp;
	s/.*<span id="ctl10_lblCourseDesc">(.*)\<\/span\>.*/\1/;	# figure out why this doesn't take out <span id=.* in some cases (ACCT 325)
	s/\s*\<p style\=\"margin-bottom:5px\;\"\>//;	
		
	if ($_ =~ /^Prerequisite/){
		$description = $_;
		$prereq = $description;
		$description =~ s/.*Prerequisite.*?\.(.*)/\1/;
		
		$coreq =~ s/.*Corequisites?: (.*)?\./\1/;
		#do something similar to prereqs
		
		$prereq =~ s/Prerequisite(.*?\.).*/\1/;
		system("echo $prereq >> raw_prerequisites.txt");	
		$prereq =~ s/(.*?)s?: //;
		$prereq =~ s/.*([A-Z][a-zA-Z]{1,4} [0-9]{3}[A-Z]?).* and .*([A-Z][a-zA-Z]{1,4} [0-9]{3}[A-Z]?).*/\1 and \2/g; #  try to combine this and the next into one
		$prereq =~ s/.*([A-Z][a-zA-Z]{1,4} [0-9]{3}[A-Z]?).* or .*([A-Z][a-zA-Z]{1,4} [0-9]{3}[A-Z]?).*/\1 OR \2/g;
		$prereq =~ s/\.//;
		push(@prereqs, split('and', $prereq));
		print("Prerequisite: $prereq\n");
	}
	else {
		$description=$_;
	}
	
	$description =~ s/([\'\"])/\\\1/g; # escape all of the quotes in the description
	
	#print("Description: $description\n") if ($debug =~ /all/ || $debug =~ /vars/);
				
	$query = "INSERT IGNORE INTO course_description VALUES (\"".$name."\",\"".$dept."\",\"".$num."\",\"".$description."\");";
	#print("$query\n") if ($debug =~ /all/ || $debug =~ /query/);
	system("echo \'$query\' >> courses.txt"); 
	
	while ($prereq = shift(@prereqs)){
		$query = "INSERT IGNORE INTO prerequisites VALUES (\"".$dept." ".$num."\",\"".uc($prereq)."\");";
		#print("$query\n") if ($debug =~ /all/ || $debug =~ /query/);
		system("echo \'$query\' >> courses.txt");	
	}
				
	while($_=<$file>){
		# section num
		while ($_=<$file>) { last if ($_ =~ /class\=\"section\"\>/); }
		$_=<$file>;
		chomp;
		s/\s+(.*)<br.*/\1/;
		$section=$_;
		print("Section: $section\n") if ( $debug =~ /all/ || $debug =~ /vars/);
		
		# call number
		while ($_=<$file>) { last if ($_ =~ /\<td class\=\"call\"\>/); }
		$_=<$file>;
		chomp;
		s/\s+\<span\>(.*)<br\/>.*/\1/;
		$callNum=$_;
		print("CRN: $callNum\n") if ($debug =~ /all/ || $debug =~ /vars/);
				
		# Day / Time
		$_=<$file>; $_=<$file>;
		s/.*\"ctl10_gv_sectionTable_ct.*_lblDays\"\>(.*)\<.*/\1/;
				
		@meetings = split(/\<br \/\>/);
		$num_meetings=scalar(@meetings);
		
		# Room
		while($_=<$file>) { last if ($_ =~ /room/) };
		$_=<$file>;
		chomp;
		s/\\r?\\n//;
		s/\s+\<span id\=\"ctl10_gv_sectionTable_ct.*_lblRoom\"\>(.*)\<\/span\>\s+/\1/;
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
			$end_minutes = $end_time % 100;
			$end_hours += ($end_time - $end_minutes) /100;
			$end_time = ($end_hours * 60 ) + $end_minutes;
			print ("End Time: $end_time\n") if ($debug =~ /all/ || $debug =~ /vars/);
			
									
			if ($num_meetings - scalar(@meetings)==1){	
				# status used only for determining whether to insert tuples
				while ($_=<$file>) { last if ($_ =~ /span id\=\"ctl10_gv_sectionTable_ct.*_lblStatus\"/); }
				s/.*span id\=\"ctl10_gv_sectionTable_ct.*_lblStatus\"(.*)\<\/span.*/\1/;
				$status = $_;
				print("Status: $status\n") if ($debug =~ /all/ || $debug =~ /vars/);
					
				# instructor
				while ($_=<$file>) { last if ($_ =~ /\.aspx\?persid\=.*/); }
				chomp;
				s/.*\.aspx\?persid\=.*\"\>(.*)\<.*/\1/;
				$instructor=$_;
				print("Instructor: $instructor\n") if ($debug =~ /all/ || $debug =~ /vars/);
					
				# credits
				while ($_=<$file>) { last if ($_ =~ /credits/); }
				chomp;
				s/\\r?\\n//;
				s/.*class=\"credits\"\>(.*)\<\/td>\s+/\1/;
				$credits=$_;
				print("Credits: $credits\n") if ($debug =~ /all/ || $debug =~ /vars/);
			}	
				
			if ($status !~ /Cancelled/i){
				if ( $num_meetings - scalar(@meetings)==1 ){
					$query = "INSERT IGNORE INTO courses VALUES (\"" .$semester."\", \"". $callNum."\",\"".$dept."\",\"".$num."\",\"".$section."\",\"".$credits."\",\"". $instructor."\");";
					print("$query\n") if ($debug =~ /query/ || $debug =~ /all/);
					system("echo \'$query\' >> courses.txt");
				}
						
				while ($day=shift(@dayNums)) {
					$query="INSERT IGNORE INTO course_times VALUES (\"".$semester."\", \"".$callNum."\",\"". $day."\",\"". $start_time."\",\"". $end_time."\",\"". $roomNum."\");";
					print("$query\n") if ($debug =~ /query/ || $debug =~ /all/);
					system("echo \'$query\' >> courses.txt");
				}
			}
			else {	
				$_=$day while ($day = shift(@dayNums));
			}
		
		}
	}	
}


$semester = ($ARGV[0] =~ /20[0-9]{2}[sufw]/) ? $ARGV[0] : "2012s";
mkdir $semester unless (-d "$semester");
getPage("http://courseschedules.njit.edu/index.aspx?semester=$semester", "./$semester/subjects", $semester) unless (-e "./$semester/subjects");
unlink("courses.txt");
open subjectFile, "./$semester/subjects" or die $!;
@subjects=parseSubjects(*subjectFile);
print "@subjects";
close subjectFile;
print ("Subjects for $semester retrieved ......");

while ($sub = shift(@subjects)){
	mkdir "$semester/$sub" unless (-d "$semester/$sub");
	print("$sub directory created.......");
	$file="./$semester/$sub/courses";
	getPage("http://courseschedules.njit.edu/index.aspx?semester=$semester&subjectID=$sub", $file, $semester) unless (-e $file);
	open classFile, $file or die $!;
	@classes = parseClasses(*classFile);
	print "@classes";
	close classFile;
	print("Class list for $semester  $sub retrieved .....");
	
	while ($class = shift(@classes)){
		$file="./$semester/$sub/$class";
		getPage("http://courseschedules.njit.edu/index.aspx?semester=$semester&subjectID=$sub&course=$class", $file, $semester) unless (-e $file);
		open sectionFile, $file or die $!;
		parseSections(*sectionFile, $semester);
		close sectionFile;
	}

}

system("./insert_data.sh courses.txt");
