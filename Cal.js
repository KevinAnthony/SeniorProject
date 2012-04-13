var stage;
var wInner;
var offset;
var start_of_day;
var scheduleLayer = new Kinetic.Layer();
var textLayer = new Kinetic.Layer();

$(document).ready(function(){
	start_of_day = 510;
	wInner = window.innerWidth - 30;
    stage = new Kinetic.Stage("cont", wInner, 1100);
    var calendarLayer = new Kinetic.Layer();
    
    var divs = 7;	
    offset = 40;
    var topX = wInner / (divs * 2) + 0.5;			//column size / 2 padding on each side
	var topY = offset + 0.5;	
	var width = wInner - 2*topX;
	var height = 810;										//this maps 1 pixel : 1 minute, nifty
 	var divs = height / 30;
 	var i = 0;
    
    var calBase = new Kinetic.Shape(function(){
    	var context = this.getContext();
        context.beginPath();
		context.rect(topX, topY, width, height);
		context.fillStyle = "#ACC8FF";//"#C9C9C7";
		context.fill();
		context.lineWidth = 3;
		context.strokeStyle = "#4B4B4B";
		context.stroke(); //draw
		
		for(i=1; i<(divs-1); i++){ 									//vertical lines
			//var pos = topX + (i * width/(divs-1)) + 0.5;
			var pos = DayToPixels(i);
			context.moveTo(pos, offset);
			context.lineTo(pos, height + offset);
			context.lineWidth = 2;
			context.strokeStyle = "#7E7E7C"
			context.stroke();
		}
		
		for(i=0; i<=divs; i++){										//horizontal lines (ea. 30m), time
			var pos = topY + (i * height / divs);
			context.moveTo(topX, pos);
			context.lineTo(topX+width, pos);
			context.lineWidth = 1;
			context.strokeStyle = "#7E7E7C";
			context.stroke();
			
			if(!(i % 2)){											//time, skips the :30s
				var x = topX - 10;
				var time = pos - (offset + 0.5) + start_of_day;		//since pixels map to minutes, 0 time (7:00, 480m) is at the offset
			 	context.fillStyle = "#000000"
				context.font = "11pt Courier";
				context.textAlign = "right"
				var h = Math.floor(time/60);
				
				if(wInner > 1100){								//if window is too small, switch to condensed time (4p, 5p...)
					var m = ((time%60 != 0) ? (":"+ time % 60) : ":00");
					m = m + ((h > 11) ? " pm" : " am" )				//decide am or pm
				} else {
					var m = ((h > 11) ? "p" : "a" )
				}													//problem marking ea 30m, in condensed mode (4p, 4p)
				
				(h > 12) ? (h = h % 12) : 0;						//Comment out for military (24h) time. Fuck yea, military time.

				context.fillText(h + m, x, pos + 4);
			}
		}
    });

    calendarLayer.add(calBase);
    stage.add(calendarLayer);
    loadSubjects();
    
    $("#loginForm").onsubmit = "login()";
    
});


/* ========= CLASS LISTINGS ========= */

var subjects = [];
var courses = {};
var sections = [];
var descriptions = [];
var schedule = [];


function loadSections(value) {
	if( $("#classSelector").value != 1 ) {
		courses = {};
		$.getJSON("./json/GetClassTimes.php?course_number=" + document.getElementById("classSelector").value + "&department=" + document.getElementById("subjectSelector").value,
		 function (text) {
				var i;
			
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
					
						times += pixelsToTime(text.data[i].start_time[j]) + " - " + pixelsToTime(text.data[i].end_time[j]) + "<br>";
					}
								
					list += "<tr><td>" + classy.CRN + "</td><td>" + days + "</td><td>" + times + "</td><td>" + rooms + "</td><td>" + classy.instructor + "</td><td id=\"buttonCol\"><button class=\"scheduleButton\" onClick=\"addToSchedule("+ classy.CRN +")\">Add / Remove</button></td></tr>";
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
	
	if(sel != 1 && sel != 99){
		$.getJSON("./json/GetCourseNumbers.php?department=" + sel, function (data) {
		    var i;
		    var list = [];
		    
		    list.push("<select id=\"classSelector\" onChange=\"loadSections(options[selectedIndex].id)\" class=\"selector\"><option id=\"-1\" value=\"1\">Select Course</option>");
		    
		    for(i=0; i < data.data.length; i++){
		    	var course = data.data[i].course_number;
		    	var courseName = data.data[i].course_name;
		    	descriptions[i] = data.data[i].description;
    			list.push("<option id=\"" + i + "\" value=\"" + course + "\" class=\"opt\">" + course + " - " + courseName + "</option>");
        	}
		    
			list.push("</select>");
			$('#classColumn').html(list.join(''));
		});
		toggleCourseDropDown("show");
	} else if(sel == 99) {
		//javascript:saveEvent()
		var form = "<tr><td colspan=\"6\" id=\"customEvent\">\
		<form id=\"saveEventForm\" action=\"javascript:saveEvent()\" method=\"post\" accept-charset=\'UTF-8\'><span><label for=\"eventName\">Event Name:</label><br/><input type=\"text\" name=\"eventName\" id=\"eventName\" maxlength=\"32\"/></span>\
		<br /><span><select id=\"daySelect\"><option value=\"-1\">Select Day<option/><option value=\"1\">Monday</option><option value=\"2\">Tuesday</option><option value=\"3\">Wednesday</option><option value=\"4\">Thursday</option><option value=\"5\">Friday</option><option id=\"6\">Saturday</option></select></span>\
		<br /><span><label for=\"startTime\">Start Time:</label><br/><input type=\"text\" name=\"startTime\" id=\"startTime\" maxlength=\"5\"/><select id=\"SapSelect\"><option value=\"0\">AM</option><option value=\"1\">PM</option></select></span>\
		<br /><span><label for=\"endTime\">End Time:</label><br/><input type=\"text\" name=\"endTime\" id=\"endTime\" maxlength=\"5\"/><select id=\"EapSelect\"><option value=\"0\">AM</option><option value=\"1\">PM</option></select></span>\
		<br /><input type=\"submit\" name=\"Submit\" value=\"Save Event\"/></form></td></tr>";
		
		//retrieve and display saved events
		$.getJSON("./json/GetAllEvents.php",
		 function (text) {
		 		var list = "";
				var i;

				var eventName;
			
				for(i=0; i < text.data.length; i++){
					var day = "";
					var times = "";
					
					eventName = text.data[i].event_name;
				
					day += numToDay(text.data[i].day) + "<br>";
				
					times += pixelsToTime(text.data[i].start_time) + " - " + pixelsToTime(text.data[i].end_time) + "<br>";
					
								
					list += "<tr><td>" + eventName + "</td><td>" + day + "</td><td>" + times + "</td><td id=\"buttonCol\"><button class=\"scheduleButton\" onClick=\"addToSchedule("+  ")\">Add / Remove</button></td></tr>";
//		    		sections[i] = classy.CRN;
				
//					courses[classy.CRN] = classy;	        		
		    	}
		    	
		    	if(list != undefined)
					form += list;
					
				$("#sectionTable").html(form);
		});
		
		
		toggleCourseDropDown("hide");
	}
};

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
	minutes += ap == 1 ? 12 : 0;
	minutes *= 60;
	time = time.substring(time.indexOf(':'+1));
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
	$.getJSON("./json/GetSubjects.php", function (data) {
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
};


/* ========== Calendar Management ========= */

var onSchedule = [];

function addToSchedule(CRN){
	var toAdd = courses[CRN];
	var i, j, k;
	var flagCheck = true;
	
	for(i=0; i < onSchedule.length; i++){
		if(CRN == onSchedule[i].CRN){
			console.log("Already on Schedule.");
			for(j=0; j < onSchedule[i].shapeLayer.length; j++){
				scheduleLayer.remove(onSchedule[i].shapeLayer[j]);
				textLayer.remove(onSchedule[i].textLayer[j]);
			}
			stage.add(scheduleLayer);
			stage.add(textLayer);
			
			onSchedule.splice(i,1);
			return;
		}
	}
	
	for(i=0; i < onSchedule.length; i++){
		for(j=0; j < onSchedule[i].day.length && flagCheck; j++){
			for(k=0; k < toAdd.day.length && flagCheck; k++){
				if(toAdd.day[k] == onSchedule[i].day[j]){
					if(toAdd.start_time[k] >= onSchedule[i].start_time[j] && toAdd.start_time[k] <= onSchedule[i].end_time[j])
						flagCheck = false;
						
					if(toAdd.end_time[k] >= onSchedule[i].start_time[j] && toAdd.end_time[k] <= onSchedule[i].end_time[j])
						flagCheck = false;
					
				}
			}
		}
		if(!flagCheck)
			break;
	}
	
	if(flagCheck){
		var text = [];
		var shapes = [];
		var texts = [];
		var both = [];
		
		text.push(toAdd.cname)
		text.push("(" + toAdd.CRN + ")");
	
		for(i=0; i < toAdd.day.length; i++){
			var dayText = text.slice(0);
			dayText.push(toAdd.room[i] + " " + pixelsToTime(toAdd.start_time[i]) + " - " + pixelsToTime(toAdd.end_time[i]));
			both = addBlock(scheduleLayer, textLayer, dayText, toAdd.day[i], toAdd.start_time[i], toAdd.end_time[i]);

			shapes.push(both[0]);
			texts.push(both[1]);
		}
		toAdd.shapeLayer = shapes;
		toAdd.textLayer = texts;
		onSchedule.push(toAdd);
	} else {
		console.log("Scheduling Conflict");
	}
};

//start_time, end_time in minutes
function addBlock(scheduleLayer, textLayer, dayText, day, start_time, end_time){	//generates a rectangle of specified dimensions
	var tX = DayToPixels(parseInt(day));
	var tY = TimeToPixels(parseInt(start_time));
	
	var block = new Kinetic.Shape(function(){
		var w = DayToPixels(parseInt(day)+1) - tX;
		var h = TimeToPixels(parseInt(end_time)) - tY;
		var context = this.getContext();
        
		context.rect(tX, tY, w, h);
		context.fillStyle = "#c9c9c9";//"#C9C9C7";
		context.fill();	
		context.lineWidth = 2;
		context.strokeStyle = "#000000";
		
		context.stroke(); //draw
		
	});
	
	var blockText = new Kinetic.Shape(function() {
		var i;
		var context = this.getContext();
		context.fillStyle = "#000000";
		for(i=0; i < dayText.length; i++){
			context.fillText(dayText[i], tX+8, tY+(15 * (i+1)));
			context.stroke();
		}
	});
	/*var text = new Kinetic.Text({
		text: "TestText",
		fontSize: 12,
		x: (tX+10),
		y: (tY+10),
		fontFamily: "monospace"
	});*/
	
	//blockText.setZIndex(block.getZIndex() + 1);
	
	scheduleLayer.add(block);
	textLayer.add(blockText);	
	//scheduleLayer.add(text);
	stage.add(scheduleLayer);
	stage.add(textLayer);
	
	return [block, blockText];
};


function pixelsToTime(pixels){
	var hours = parseInt(pixels / 60).toString();
	var minutes = parseInt(pixels % 60).toString();
	hours = (hours.length == 1 ? "0" + hours : hours);
	minutes += (minutes.length == 1 ? "0" : "");
	var time = hours + ":" + minutes;
	
	return time;
};

function TimeToPixels(time){						//adjusts the time by the offset and the start time of calendar
	var position;
	position = time - (start_of_day - offset);
	
	//console.write(pixies + '\n');
	return position;
};

function DayToPixels(day){							//returns the pixel position of a given day
	var pixals = 0;
	var dayInt = parseInt(day) - 1;
	pixals += (wInner/14) + (dayInt * (wInner/7));
	return Math.floor(pixals);
};

var toggle = 0;
var tgle = 1;
var text = {};

function sendEvent(){
	var startTime = document.getElementById("sbox1").value;
	var endTime = document.getElementById("sbox2").value;
	var day = document.getElementById("sbox3").value;
	var jsonGet = new XMLHttpRequest();
    jsonGet.open("POST","./json/SaveEvent.php?day=" + day + "&start_time=" + startTime + "&end_time=" + endTime);
    jsonGet.send();
};


/*============= LOGIN ==============*/

function loginBoxToggle(){
	var blah = $("#slider").css('left');
	var toLeft;
	if(blah.indexOf("px") == -1){
			toLeft = $("#slider").css('left') == "35%" ?
			"100%" :
			"35%";
		} else {
			toLeft = parseInt(100 * parseFloat($("#slider").css('left')) / parseFloat($("#slider").parent().css('width'))) < 40 ?
			"100%" :
			"35%";
		}
	$("#slider").animate({
		left : toLeft
	});
};

function login() {
	var username = $("#username").val();
	var password = $("#password").val();
	$.ajax({
		type: "POST",
		url: "./json/Login.php",
		data: {"password":password, "username":username},
		success: function(data){
 			//$("#loginForm").html("Success!");
 			console.log(data);  
		},
		error: function(){
    		console.log(data);
  		}

	});
};

function register() {
	var username = $("#username").val();
	var password = $("#password").val();
	$.ajax({
		type: "POST",
		url: "./json/Register.php",
		data: {"password":password, "username":username},
		success: function(data){
 			//$("#loginForm").html("Success!");
 			console.log(data);  
		},
		error: function(){
    		console.log(data);
  		}

	});
};
	
	
function logout() {
	$.ajax({
		type: "POST",
		url: "./json/Logout.php",
		success: function(data){
 			console.log(data);  
		},
		error: function(){
    		console.log(data);
  		}

	});
};
//Finished=====================
//login works on any credentials?
//saved schedules?

//logout and register functions -> ajax
//classes stored as object of objects
//select buttons display classes
//hiding class listings
//times as times (not pixels)
//text on added blocks
//displayed classes stored as objects, linked to displayed blocks 
//time collision handling
//user controlled events on menus, building them
//chrome bug on drop down lists (ugly coloring)
//conflicts in list
//slide out course selections
//save event
//retrieve events
//custom event on schedule
//multiple sched

//To Do=======================
//save sched
//add loadSchedules to login
	//check login at start?

//block text overflow
//give error messages
//constraint checking on all input (times)
//conf pass on reg
//something happen on login
//height of selection

//mouseover
//(highlight same crn? mouseovers?)

//red conflict on course/event selection (?)

//selection drop down

//njit logo
//colors

//schedule predictor
//minify
