#!/bin/bash

if [$1!=""]
then
	echo "Usage: $0 sql_file"
	exit

fi


mysql --host=sql.njit.edu --user=ejw3_proj --password=ozw6OBAO --port=3306 --database=ejw3_proj --force < $1
