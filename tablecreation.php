<?php
	//   Erika Waldinger
	// 	CS 490 Alpha Database
	
	function db_connect (){
		
		$connection = mysql_connect("sql.njit.edu", "ejw3_proj", "ozw6OBAO");
		if (!$connection){
			die("Could not connect to MySQL database at sql.njit.edu ".mysql_error());
		}
		mysql_select_db("ejw3_proj", $connection);
	}	

	function query ($query_str){
		$result=mysql_query($query_str) or die( mysql_error());
		return $result;
	}

	db_connect();
	
	query("	CREATE TABLE IF NOT EXISTS COURSE_DESCRIPTIONS (
						DEPT VARCHAR(5) NOT NULL,
						NUMBER VARCHAR(4) NOT NULL,
						DESCRIPTION TEXT NOT NULL, 
						PRIMARY KEY (DEPT, NUMBER) );");
	
	query("	CREATE TABLE IF NOT EXISTS S12_COURSES (
						CRN INTEGER NOT NULL, 
						DEPT VARCHAR(5) NOT NULL,
						NUMBER VARCHAR(5) NOT NULL,
						SECTION VARCHAR(4) NOT NULL,
						CREDITS INTEGER NOT NULL,
						INSTRUCTOR VARCHAR(30) DEFAULT 'TBA',
						PRIMARY KEY (CRN),
						UNIQUE (DEPT, NUMBER, SECTION),
						FOREIGN KEY (DEPT, NUMBER) REFERENCES COURSE_DESCRIPTIONS,
						CONSTRAINT S12COURSES_5DIGIT_CRN CHECK NOT EXISTS  (SELECT CRN 
																																						FROM S12_COURSES 
																																						WHERE CRN < 10000 OR CRN > 99999));");
	query(" 	CREATE TABLE IF NOT EXISTS S12_COURSE_TIMES (
						CRN INTEGER NOT NULL, 
						DAY INTEGER NOT NULL, 
						START_TIME INTEGER NOT NULL, 
						END_TIME INTEGER NOT NULL,  
						ROOM VARCHAR(8), 
						PRIMARY KEY (CRN,DAY,START_TIME),
						FOREIGN KEY (CRN) REFERENCES S12_COURSES(CRN) ON DELETE CASCADE ON UPDATE CASCADE,
						CONSTRAINT S12COURSES_TIME_DOMAIN CHECK NOT EXISTS	(SELECT * 
																																							FROM S12_COURSE_TIMES 
																																							WHERE `START_TIME`<0 OR `END_TIME`<0 
																																							OR `START_TIME`>1439 OR `END_TIME`>1439),
						CONSTRAINT S12COURSES_DURATION CHECK NOT EXISTS 	(SELECT * 
																																					FROM s12_COURSE_TIMES
																																					WHERE END_TIME-START_TIME < 0),
						CONSTRAINT S12COURSES_DAY_DOMAIN CHECK NOT EXISTS	(SELECT DAY 
																																							FROM S12_COURSE_TIMES 
																																							WHERE DAY<0 OR DAY >6));");		

		query("	CREATE TABLE IF NOT EXISTS EVENT (
							ID INTEGER NOT NULL, 
							USERNAME VARCHAR(12) NOT NULL,
							EVENT_NAME VARCHAR(12) NOT NULL DEFAULT 'unnamed', 
							START_TIME INTEGER NOT NULL, 
							END_TIME INTEGER NOT NULL, 
							DAY INTEGER NOT NULL, 
							PRIMARY KEY (ID),
							FOREIGN KEY (USERNAME) REFERENCES USER(USERNAME) ON DELETE CASCADE ON UPDATE CASCADE,
							CONSTRAINT EVENT_TIME_DOMAIN CHECK NOT EXISTS	(SELECT *
																																					FROM EVENT
																																					WHERE START_TIME<0 OR END_TIME<0
																																					OR START_TIME>1439 OR END_TIME>1439),
							CONSTRAINT EVENT_DAY_DOMAIN CHECK NOT EXISTS	(SELECT * 
																																					FROM EVENT 
																																					WHERE DAY<1 OR DAY>6),
							CONSTRAINT POSITIVE_EVENT_DURATION CHECK NOT EXISTS	(SELECT * 
																																									FROM EVENT 
																																									WHERE END_TIME-START_TIME < 0));");
																																									
		query(" CREATE TABLE IF NOT EXISTS SAVED_SCHEDULES (
							USER VARCHAR(12) NOT NULL, 
							SCHEDULE_NAME VARCHAR(12) NOT NULL DEFAULT 'default',
							EVENT INTEGER NOT NULL,
							`TYPE` CHAR NOT NULL DEFAULT 'C',
							PRIMARY KEY (USER, SCHEDULE_NAME, EVENT), 
							CONSTRAINT RESTRICT_TYPE CHECK NOT EXISTS 	(SELECT * 
																																	FROM SAVED_SCHEDULES 
																																	WHERE TYPE NOT IN ('C', 'E')),
							CONSTRAINT REF_INTEGRITY CHECK NOT EXISTS (SELECT * 
																																	FROM SAVED_SCHEDULES 
																																	WHERE EVENT NOT IN (SELECT DISTINCT CRN
																																													FROM S12_COURSES)
																																	AND EVENT NOT IN (SELECT ID FROM EVENT)	));");
																																									
		query(" CREATE TABLE IF NOT EXISTS USER (
							USERNAME VARCHAR(12),
							PASSWORD VARCHAR(34),
							PRIMARY KEY (USERNAME));");

		/*  
		  *	NOTE:  These triggers are necessary but I don't have the permissions required to create them.
		  *	
		  *	query("CREATE TRIGGER DELETE_REMOVED_CLASS AFTER DELETE ON S12_COURSES 
		  *				FOR EACH ROW
		  *				DELETE FROM SAVED_SCHEDULES WHERE TYPE='C' AND EVENT=OLD.CRN");
		  *
		  *	query(" CREATE TRIGGER DELETE_REMOVED_EVENT AFTER DELETE ON EVENT
		  *						FOR EACH ROW
		  *						DELETE FROM SAVED_SCHEDULES WHERE TYPE='E' AND EVENT=OLD.ID");
		  */
																																						
																																						
?>