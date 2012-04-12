<?php
	$connection = mysql_connect("sql.njit.edu","ejw3_proj", "ozw6OBAO");
	if (!$connection) { 
		return("Could not connect: ". mysql_error());
	}
	mysql_select_db("ejw3_proj", $connection);
	
	$num_rows=1;
	
	while ($num_rows > 0){
		mysql_query("INSERT IGNORE INTO prerequisites (SELECT A.course, B.prerequisite FROM `prerequisites` A ".
					"INNER JOIN prerequisites B ON A.prerequisite = B.course)");
		$num_rows=mysql_affected_rows();			
	}
	



	mysql_close();

?>
