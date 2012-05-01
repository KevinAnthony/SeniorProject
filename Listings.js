var subjects = [];
var courses = {};
var events = {};
var sections = [];
var descriptions = [];


function loadSections(value) {
	var semester = $("#semesterSelector").val();
	$("#sectionTable").html("");
						
	if( $("#classSelector").value != 1 ) {
		courses = {};
		$.getJSON("./json/GetClassTimes.php?course_number=" + document.getElementById("classSelector").value + "&department=" + document.getElementById("subjectSelector").value + "&semester=" + semester,
		 function (text) {
				var i;
				
				if(text.data == undefined) {
					document.getElementById("sectionTable").innerHTML="";
					return;
				}
				
				if(value > -1)
					var list = "<tr><td colspan=\"5\" id=\"description\">" + descriptions[value] + "</td></tr>";

				var classy;
			
				for(i=0; i < text.data.length; i++){
					var days = "";
					var times = "";
					var rooms = "";
					var j;
					
					classy = text.data[i];
					classy.cname = $("#classSelector option:selected").html();
					classy.cname = classy.cname.substring(classy.cname.indexOf('-') + 2);
					
					for(j=0; j < text.data[i].day.length; j++){
						days += numToDay(text.data[i].day[j]) + "<br>";
					
						rooms += text.data[i].room[j] + "<br>";
					
						times += pixelsToTime(text.data[i].start_time[j]) + "-" + pixelsToTime(text.data[i].end_time[j]) + "<br>";
					}
								
					list += "<tr><td>" + classy.CRN + "</td><td>" + days + "</td><td>" + times + "</td><td>" + rooms + "</td><td>" + classy.instructor + "</td><td id=\"buttonCol\"><button class=\"scheduleButton\" onClick=\"addToSchedule(currentSchedule, courses, "+ classy.CRN +",0, true)\">Add / Remove</button></td></tr>";
		    		sections[i] = classy.CRN;
				
					courses[classy.CRN] = classy;	        		
		    	}
		    	
		    	if(list != undefined)
					document.getElementById("sectionTable").innerHTML=list;
		});

	}
};

function numToDay(day){
	switch(parseInt(day)){
		case 1:
			return "M";
		case 2:
			return "T";
		case 3:
			return "W";
		case 4:
			return "Th";
		case 5:
			return "F";
		case 6:
			return "S";
	}
	return day;
};

function toggleSections(){
	var isVis = $("#courseSlide").css("left");
	var toLeft;
	
	if(parseInt(isVis) == 0){
		toLeft = -$("#courseSlide").outerWidth();
		
		$("#courseSlide").animate({
			left : toLeft
		});
		
		$("#hideSections").html("Show");
	} if(parseInt(isVis) < 0){
		toLeft = 0;
		
		$("#courseSlide").animate({
			left : toLeft
		});
		
		$("#hideSections").html("Hide");
	}
};

function loadClasses() {
	var sel = $("#subjectSelector").val();
	var semester = $("#semesterSelector").val();
	//$("#classSelector").val(1);
	$("#sectionTable").html("");
	
	if(sel != 1 && sel != 99){
		$.getJSON("./json/GetCourseNumbers.php?department=" + sel + "&semester=" + semester, function (data) {
		    var i;
		    var list = [];
		    
		    list.push("<select id=\"classSelector\" onChange=\"loadSections(options[selectedIndex].id)\" class=\"selector\"><option id=\"-1\" value=\"1\">Select Course</option>");
		    
		    for(i=0; i < data.data.length; i++){
		    	var course = data.data[i].course_number;
		    	var courseName = data.data[i].course_name;
		    	//if coursename contains &amp, remove
		    	descriptions[i] = data.data[i].description;
    			list.push("<option id=\"" + i + "\" value=\"" + course + "\" class=\"opt\">" + course + " - " + courseName + "</option>");
        	}
		    
			list.push("</select>");
			$('#classColumn').html(list.join(''));
		});
		toggleCourseDropDown("show");
	} else if(sel == 99) {					//EVENTS
		//javascript:saveEvent()
		var form = "<tr><td colspan=\"6\" id=\"customEvent\">\
		<form id=\"saveEventForm\" action=\"javascript:saveEvent()\" method=\"post\" accept-charset=\'UTF-8\'><span><label for=\"eventName\">Event Name:</label><br/><input type=\"text\" name=\"eventName\" id=\"eventName\" maxlength=\"32\"/></span>\
		<br /><span><select id=\"daySelect\"><option value=\"-1\">Select Day</option><option value=\"1\">Monday</option><option value=\"2\">Tuesday</option><option value=\"3\">Wednesday</option><option value=\"4\">Thursday</option><option value=\"5\">Friday</option><option id=\"6\">Saturday</option></select></span>\
		<br /><span><label for=\"startTime\">Start Time:</label><br/><input type=\"text\" name=\"startTime\" id=\"startTime\" maxlength=\"5\"/><select id=\"SapSelect\"><option value=\"0\">AM</option><option value=\"1\">PM</option></select></span>\
		<br /><span><label for=\"endTime\">End Time:</label><br/><input type=\"text\" name=\"endTime\" id=\"endTime\" maxlength=\"5\"/><select id=\"EapSelect\"><option value=\"0\">AM</option><option value=\"1\">PM</option></select></span>\
		<br /><input type=\"submit\" name=\"Submit\" value=\"Save Event\"/></form></td></tr>";
		
		//retrieve and display saved events
		
		events = {};
		
		$.getJSON("./json/GetAllEvents.php",
		 function (text) {
		 	var list = "";
		 	if(text.data != undefined){
				var i;

				var eventName;
			
				for(i=0; i < text.data.length; i++){
					var day = "";
					var times = "";
					var dayHold = text.data[i].day;
					var sTimeHold = text.data[i].start_time;
					var eTimeHold = text.data[i].end_time;
					
					eventName = text.data[i].event_name;
				
					day += numToDay(text.data[i].day) + "<br>";
				
					times += pixelsToTime(text.data[i].start_time) + "-" + pixelsToTime(text.data[i].end_time) + "<br>";
					
								
					list += "<tr><td>" + eventName + "</td><td>" + day + "</td><td nowrap=\"nowrap\">" + times + "</td><td id=\"buttonCol\"><button class=\"scheduleButton\" onClick=\"addToSchedule(currentSchedule, events,\'"+ text.data[i].id + text.data[i].event_name + "\',1, true)\">Add / Remove</button></td><td><button class=\"scheduleButton\" onClick=\"deleteEvent(" + text.data[i].id +")\">Delete</button></td></tr>";
					text.data[i].day = [];
					text.data[i].day.push(dayHold);
					text.data[i].start_time = [];
					text.data[i].start_time.push(sTimeHold);
					text.data[i].end_time = [];
					text.data[i].end_time.push(eTimeHold);
					
					
					events[""+text.data[i].id + text.data[i].event_name] = text.data[i];	        		
		    	}
		    	
		    	if(list != undefined)
					form += list;

			} else {
				if(!text.success && (text.error == "SESSIONERROR: Session Expired" || text.error == "SESSIONERROR: Login Required")) {
					list = "Please log in for this functionality.";
					form = list;
				}
			}
			
			$("#sectionTable").html(form);
		});
		
		
		toggleCourseDropDown("hide");
	}
};

function deleteEvent(id){
	$.ajax({
		type: "POST",
		url: "./json/DeleteEvent.php?id=" + id,
		success: function(data){
			loadClasses();
		},
		error: function(){
			alert("Delete Failed!");
			console.log(data);
		}
	});
}

function saveEvent() {
	var event_name = $("#eventName").val();
	var day = $("#daySelect").val();
	var start_time = clockToMin($("#startTime").val(), $("#SapSelect").val());
	var end_time = clockToMin($("#endTime").val(), $("#EapSelect").val());
	console.log("???");
	//validate
	$.ajax({
		type: "GET",
		url: "./json/SaveEvent.php",
		data: {"event_name":event_name, "day":day, "start_time":start_time, "end_time":end_time},
		success: function(data){
 			//$("#loginForm").html("Success!");
 			loadClasses();
 			console.log(data);  
		},
		error: function(){
    		console.log(data);
  		}

	});
	
	//add to displayed events
};

function clockToMin(time, ap){
	var minutes = parseInt(time);
	minutes += (ap == 1) ? 12 : 0;
	minutes *= 60;
	time = time.substring(time.indexOf(':')+1);
	minutes += parseInt(time);
	
	return minutes;
};

function toggleCourseDropDown(option){
	if(option == "hide"){
		$("#classColumn").css("display", "none");
	} if(option == "show"){
		$("#classColumn").css("display", "block");
	}
};

function loadSubjects(){
	$("#subjectSelector").val(1);
	$("#classSelector").val(1);
	$("#sectionTable").html("");
	
	var semester = $("#semesterSelector").val();
	if(semester != 1){
		$.getJSON("./json/GetSubjects.php?semester=" + semester, function (data) {
		    var i;
		    var list = [];
		    
		    list.push("<select id=\"subjectSelector\" onChange=\"loadClasses()\" class=\"selector\"><option id=\"-1\" class=\"opt\" value=\"1\">Select Subject</option><option class=\"opt\" value=\"99\">Custom Event</option>");
		    
			$.each(data.data, function(key, val) {
				list.push("<option value=\"" + val.department + "\" class=\"opt\">" + val.department + "</option>");
				subjects[i] = val.department;
			});
			
			list.push("</select>");
			$('#subjectColumn').html(list.join(''));
		});
	}
};

