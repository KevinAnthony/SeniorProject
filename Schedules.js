function loadSchedules(){
	$.getJSON("./json/GetSchedules.php",
		function(data){
			console.log(data);

			var numScheds = data.number_of_schedules;

			for(i=0; i < numScheds; i++){
				var scheduleNumber;
				var scheduleLayer = new Kinetic.Layer();
				var textLayer = new Kinetic.Layer();
				var schedule = {"sname":data.schedules[i][0].schedule_name,"id":data.schedules[i][0].id, "textLayer":textLayer, "scheduleLayer":scheduleLayer, "onSchedule":[]};
				var j;
				
				scheduleNumber = schedules.push(schedule);
				scheduleNumber -= 1;
				
				var addFrom = {};
				
				for(j=0; j < data.schedules[i][0].courses.length; j++){
					addFrom[data.schedules[i][0].courses[j].CRN] = data.schedules[i][0].courses[j];
				
					addFrom[data.schedules[i][0].courses[j].CRN].cname = addFrom[data.schedules[i][0].courses[j].CRN].course_name;
					delete addFrom[data.schedules[i][0].courses[j].CRN].course_name;
				
					addToSchedule(schedules[scheduleNumber], addFrom, data.schedules[i][0].courses[j].CRN, 0, false)
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
			
			loadListings();
		}
	);
};

function deleteSchedule(){
	//alert
	var name = currentSchedule.sname;
	
	if(currentSchedule.sname != "New"){
		$.ajax({
			type: "POST",
			url: "./json/DeleteSchedule.php?schedule_name=" + name,
			success: function(data){
				stage.remove(currentSchedule.textLayer);
				stage.remove(currentSchedule.scheduleLayer);
				
				currentSchedule = schedules[0];
				delete schedules;
				schedules = [];
				schedules.push(currentSchedule);
		
				//var scheduleLayer = new Kinetic.Layer();
				//var textLayer = new Kinetic.Layer();
				//var newschedule = {"sname":"New","textLayer":textLayer, "scheduleLayer":scheduleLayer, "onSchedule":[]};
		
				//schedules.push(newschedule);
				//currentSchedule = schedules[0];
				loadSchedules();
			},
			error: function(){
				alert("Delete Failed!");
				console.log(data);
			}
		});
	}
}


function exportSchedule(){
	//alert
	var name = currentSchedule.id;

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
	loadListings();	
};


function saveSchedule(){
	var elist = "";
	var clist = "";
	var scheduleName = prompt("Schedule Name:","");
	var i;

	if(scheduleName == "" || scheduleName == null || scheduleName == "New"){
		return;
	}
	
	for(i=0; i < currentSchedule.onSchedule.length; i++){
		if(currentSchedule.onSchedule[i].CRN != undefined){
			currentSchedule.onSchedule[i].CRN = currentSchedule.onSchedule[i].CRN;
		}
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
				var newschedule = schedules[0];
				delete schedules;
				schedules = [];
				
				schedules.push(newschedule);
				currentSchedule = schedules[0];
			}
			loadSchedules();
		},
		error: function(){
		  console.log(data);
		}
	});
};


function loadListings(){
	var i;
	var list = "";
	
	for(i=0; i < currentSchedule.onSchedule.length; i++){
		var days = "";
		var times = "";
		var rooms = "";
		var j;
		
		classy = currentSchedule.onSchedule[i];
		
		if(classy.id != undefined){
			var day = "";
			var times = "";
			var eventName;
			//var dayHold = text.data[i].day;
			//var sTimeHold = text.data[i].start_time;
			//var eTimeHold = text.data[i].end_time;
		
			eventName = classy.event_name;
	
			day += numToDay(classy.day) + "<br>";
	
			times += pixelsToTime(classy.start_time) + "-" + pixelsToTime(classy.end_time) + "<br>";
			
			
			list += "<tr><td>" + eventName + "</td><td></td><td>" + day + "</td><td nowrap=\"nowrap\">" + times + "</td><td></td><td></td><td id=\"buttonCol\"><button class=\"scheduleButton\" onClick=\"addToSchedule(currentSchedule, events,\'"+ classy.id + classy.event_name + "\',1, true)\">Remove</button></td></tr>";
			
		} else {
		
			if(classy.CRN == undefined)
				classy.CRN = classy.CRN;
		
		
			for(j=0; j < classy.day.length; j++){
				days += numToDay(classy.day[j]) + "<br>";
		
				rooms += classy.room[j] + "<br>";
		
				times += pixelsToTime(classy.start_time[j]) + "-" + pixelsToTime(classy.end_time[j]) + "<br>";
			}
					
			list += "<tr><td>"+ classy.cname +"</td><td>" + classy.CRN + "</td><td>" + days + "</td><td>" + times + "</td><td>" + rooms + "</td><td>" + classy.instructor + "</td><td id=\"buttonCol\"><button class=\"scheduleButton\" onClick=\"addToSchedule(currentSchedule, courses, "+ classy.CRN +",0, true)\">Remove</button></td></tr>";	      //new remove function?  		
		}
	}
	
	if(list != undefined)
		$("#cListTable").html(list);

};







