#!/usr/bin/perl

sub getPage{
	
	$url = shift;
	$url="\'$url\'";
	$outFile=shift;
	system("/usr/bin/php ./scraper.php $url > $outFile");
	sleep(5);
	
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
		$subject =~ s/([A-Z]{2,4})\s+-.*/\1/;
		$subject =~ s/\s+//g;
		push(@subjects, $subject);
	}
	
	return @subjects;
	
}

sub parseClasses{
	
	$file = shift;
	$_= <$file> while ($_ !~ /<a id=\"ctl10_GridView1_ct.*_lbCourse\"/);
	
	while (<$file>){
		next if ($_ !~ /<a id=\"ctl10_GridView1_ct.*_lbCourse\"/);
		chomp;
		s/.*\<strong\>(.*)\<\/strong\>/\1/;
		($subject, $class) = split;
		push (@classes, $class);
	}
	
	# eliminate duplicates from list
	undef %saw;
	@classes = grep(!$saw{$_}++, @classes);
	
	return @classes;	
	
}


$semester = ($ARGV[0] =~ /20[0-9]{2}[sufw]/) ? $ARGV[0] : "2012s";
getPage("http://courseschedules.njit.edu/index.aspx?semester=$semester", "./$semester\.subjects");

open subjectFile, "./$semester\.subjects" or die $!;
@subject=parseSubjects(*subjectFile);

while ($sub = shift(@subjects)){
	getPage("http://courseschedules.njit.edu/index.aspx?semester=$semester&subjectID=$sub", "./$sub\.courses");
	open classFile, "./$sub\.courses" or die $!;
	@classes = parseClasses(*classFile);
	
	while ($class = shift(@classes)){
		$file="./$sub\.$class\.sections";
		getPage("http://courseschedules.njit.edu/index.aspx?semester=$semester&subjectID=$sub&course=$class", $file);
		system("./course_sections.pl $file &");
		sleep(10);
		system("kill `ps -ef | grep course_sections | awk '{print \$2}'`");
	}

}

`./insert_data.sh courses.txt`
