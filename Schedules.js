function loadSchedules(){
	$.getJSON("./json/GetSchedules.php",
		function(data){
			console.log(data);


			var numScheds = data.number_of_schedules;

			for(i=0; i < numScheds; i++){
				var scheduleNumber;
				var scheduleLayer = new Kinetic.Layer();
				var textLayer = new Kinetic.Layer();
				var schedule = {"sname":data.schedules[i][0].schedule_name,"textLayer":textLayer, "scheduleLayer":scheduleLayer, "onSchedule":[]};
				var j;
				
				scheduleNumber = schedules.push(schedule);
				scheduleNumber -= 1;
				
				var addFrom = {};
				
				for(j=0; j < data.schedules[i][0].courses.length; j++){
					addFrom[data.schedules[i][0].courses[j].crn] = data.schedules[i][0].courses[j];
				
					addFrom[data.schedules[i][0].courses[j].crn].cname = addFrom[data.schedules[i][0].courses[j].crn].course_name;
					delete addFrom[data.schedules[i][0].courses[j].crn].course_name;
				
					addToSchedule(schedules[scheduleNumber], addFrom, data.schedules[i][0].courses[j].crn, 0, false)
				}
				
				addFrom = {};
				
				for(j=0; j < data.schedules[i][0].events.length; j++){
					addFrom[data.schedules[i][0].events[j].event_name] = data.schedules[i][0].events[j];
				
					addToSchedule(schedules[scheduleNumber], addFrom, data.schedules[i][0].events[j].event_name, 1, false)
				}
			}
			
			var list = "";
			
			for(i=0; i < schedules.length; i++){
				list += "<button onClick=\"showSchedule(" + i + ")\" onMouseOver=\"\" class=\"scheduleButton\">" + schedules[i].sname + "</button>";
				
				//display some html for each schedule
				//onClick=changeDisplay(scheduleNumber)
				//+save schedule
			}
			
			list += "<br/><br/><button onClick=\"saveSchedule()\" class=\"scheduleButton\">Save Schedule</button> <button onClick=\"exportSchedule()\" class=\"scheduleButton\">Export Schedule</button> <button onClick=\"deleteSchedule()\" class=\"scheduleButton\">Delete Schedule</button>";
			$("#savedSchedules").html(list);
		}
	);
};

function deleteSchedule(){
	//alert
	var name = currentSchedule.sname;

	$.ajax({
		type: "POST",
		url: "./json/DeleteSchedule.php?schedule_name=" + name,
		success: function(data){
			stage.remove(currentSchedule.textLayer);
			stage.remove(currentSchedule.scheduleLayer);
			
			delete schedules;
			schedules = [];
		
			var scheduleLayer = new Kinetic.Layer();
			var textLayer = new Kinetic.Layer();
			var newschedule = {"sname":"New","textLayer":textLayer, "scheduleLayer":scheduleLayer, "onSchedule":[]};
		
			schedules.push(newschedule);
			currentSchedule = schedules[0];
			loadSchedules();
		},
		error: function(){
			alert("Delete Failed!");
			console.log(data);
		}
	});
	
}

function exportSchedule(){
	//alert
	var name = currentSchedule.sname;

	window.open("./json/ExportCalendar.php?schedule_id=" + name);
}

function showSchedule(scheduleNumber){
	try {
		stage.remove(currentSchedule.scheduleLayer);
		stage.remove(currentSchedule.textLayer);
	} catch (error) {}
	
	currentSchedule = schedules[scheduleNumber];
	
	stage.add(currentSchedule.scheduleLayer);
	stage.add(currentSchedule.textLayer);	
};


function saveSchedule(){
	var elist = "";
	var clist = "";
	var scheduleName = prompt("Schedule Name:","");
	var i;

	if(scheduleName == "" || scheduleName == null){
		return;
	}
	
	for(i=0; i < currentSchedule.onSchedule.length; i++){
		if(currentSchedule.onSchedule[i].CRN != undefined){
			if(clist != "")
				clist += ", "
			clist += currentSchedule.onSchedule[i].CRN;
		} else {
			if(elist != "")
				elist += ", "
			elist += currentSchedule.onSchedule[i].id;
		}
	}
	//multi save?
	//check others unsaved
	$.ajax({
		type: "POST",
		url: "./json/SaveSchedule.php?{\"schedule_name\":\""+ scheduleName +"\",\"courses\":["+ clist +"], \"events\":["+ elist +"]}",
		success: function(data){
			if(currentSchedule.sname == "New"){
				stage.remove(schedules[0].textLayer);
				stage.remove(schedules[0].scheduleLayer);
				delete schedules;
				schedules = [];
			
				var scheduleLayer = new Kinetic.Layer();
				var textLayer = new Kinetic.Layer();
				var newschedule = {"sname":"New","textLayer":textLayer, "scheduleLayer":scheduleLayer, "onSchedule":[]};
			
				schedules.push(newschedule);
				currentSchedule = schedules[0];
			} else {
				//somethingsomethingsomething
			}
			loadSchedules();
		},
		error: function(){
		  console.log(data);
		}
	});
};









