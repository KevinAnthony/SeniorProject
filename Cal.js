var stage;
var wInner;
var offset;
var start_of_day;
var scheduleLayer = new Kinetic.Layer();

$(document).ready(function(){
	start_of_day = 420;
	wInner = window.innerWidth - 30;
    stage = new Kinetic.Stage("cont", wInner, 1100);
    var calendarLayer = new Kinetic.Layer();
    
    var divs = 7;	
    offset = 40;
    var topX = wInner / (divs * 2) + 0.5;			//column size / 2 padding on each side
	var topY = offset + 0.5;	
	var width = wInner - 2*topX;
	var height = 900;										//this maps 1 pixel : 1 minute, nifty
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



var subjects = [];
var courses = {};
var sections = [];
var descriptions = [];
var schedule = [];

function loadSections(value) {
	var sel = "./json/GetClassTimes.php?course_number=" + document.getElementById("classSelector").value + "&department=" + document.getElementById("subjectSelector").value;
	courses = {};								//IF THINGS DON'T WORK CHECK THIS LINE
	var jsonGet = new XMLHttpRequest();
	jsonGet.open("GET", sel);
    jsonGet.onreadystatechange = function () {
		if(jsonGet.readyState == 4 && jsonGet.status == 200){
		    text = JSON.parse(jsonGet.responseText);
		    var i;
			
			if(value > -1)
			    var list = "<tr><td colspan=\"5\" id=\"description\">" + descriptions[value] + "</td></tr>";

			var days = "";
			var times = "";
			var rooms = ""; 
			var classy = {"crn":undefined, "cname":undefined, "prof":undefined, "days":[], "stimes":[], "etimes":[], "rooms":[]};
			
		    for(i=0; i < text.data.length; i++){
		    	classy.crn = text.data[i].crn;
		    	if(text.data[i+1] != undefined)
					var nextCrn = text.data[i+1].crn;

				classy.prof = text.data[i].instructor;
				
				classy.days.push(text.data[i].day);
		    	days += text.data[i].day + "<br>";
		    	
		    	classy.rooms.push(text.data[i].room);
		    	rooms += text.data[i].room + "<br>";
		    	
		    	classy.stimes.push(text.data[i].start_time);
		    	classy.etimes.push(text.data[i].end_time);
				times += pixelsToTime(text.data[i].start_time) + " - " + pixelsToTime(text.data[i].end_time) + "<br>";
																    	
		    	if(i+1 == text.data.length || classy.crn != nextCrn){
					list += "<tr><td>" + classy.crn + "</td><td>" + days + "</td><td>" + times + "</td><td>" + rooms + "</td><td>" + classy.prof + "</td><td id=\"buttonCol\"><button class=\"scheduleButton\" onClick=\"addToSchedule("+ classy.crn +")\">Select</button></td></tr>";
	        		sections[i] = classy.crn;
	        		days = "";
	        		times = "";
	        		rooms = "";
					
					courses[classy.crn] = classy;	        		
	        		var classy = {"crn":undefined, "cname":undefined, "prof":undefined, "days":[], "stimes":[], "etimes":[], "rooms":[]};
        		}
        	}
        	if(list != undefined)
				document.getElementById("sectionTable").innerHTML=list;
		}
	};
	jsonGet.send();
	
};

function addToSchedule(crn){
	var toAdd = courses[crn];
	var i;
	
	for(i=0; i < toAdd.days.length; i++){
		addBlock(scheduleLayer, toAdd.days[i], toAdd.stimes[i], toAdd.etimes[i]);
	}
	
};

function loadClasses() {
	var sel = $("#subjectSelector").val();
	
	if(sel != 1){
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
	}
};

function loadSubjects(){
	$.getJSON("./json/GetSubjects.php", function (data) {
        var i;
        var list = [];
        
        list.push("<select id=\"subjectSelector\" onChange=\"loadClasses()\" class=\"selector\"><option id=\"-1\" value=\"1\">Select Subject</option>");
        
		$.each(data.data, function(key, val) {
			list.push("<option value=\"" + val.department + "\" class=\"opt\">" + val.department + "</option>");
    		subjects[i] = val.department;
    	});
    	
    	list.push("</select>");
		$('#subjectColumn').html(list.join(''));
	});
};


//start_time, end_time in minutes
function addBlock(scheduleLayer, day, start_time, end_time){	//generates a rectangle of specified dimensions
	var block = new Kinetic.Shape(function(){
		var tX = DayToPixels(parseInt(day));
		var tY = TimeToPixels(parseInt(start_time));
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

	scheduleLayer.add(block);
	stage.add(scheduleLayer);
	return block;
};


function pixelsToTime(pixels){
	var hours = parseInt(pixels / 60);
	var minutes = parseInt(pixels % 60);
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

function populateList(){							//retrieves list from the server, adds it to the option box
	if(toggle == 0){
    	var jsonGet = new XMLHttpRequest();
    	jsonGet.open("GET","./json/GetAllEvents.php");
        jsonGet.onreadystatechange = function () {
        	//console.log(jsonGet.readyState);
        	//console.log(jsonGet.status);
			if(jsonGet.readyState == 4 && jsonGet.status == 200){
		        text = JSON.parse(jsonGet.responseText);
		        var i;
		        var list = "<select id=\"sbox\" class=\"selector\">";
		        for(i=0; i < text.data.length; i++){
		        	//text.data[i].start_time = parseInt(text.data[i].start_time);
		        	//text.data[i].end_time = parseInt(text.data[i].end_time);
		        	//text.data[i].day = parseInt(text.data[i].day);
		        	var time = text.data[i].start_time / 60;
		        	time = Math.floor(time);
		        	var mins = text.data[i].start_time % 60;
		        	(mins < 10) ? mins = ("0" + mins) : null;
        			list += "<option value=\""+i+"\" class=\"opt\">" + text.data[i].day + " - " + time + ":" + mins + "</option>";
            		blocks[i] = null;
            	}
            	list += "</select>";
				document.getElementById("sdiv").innerHTML=list;
	    	}
	    };
	    jsonGet.send();
	    toggle = 0;
	} else {}

};

//{"success":true,"number_of_rows":2,"0":{"id":"7","event_name":null,"day":"3","start_time":"510","end_time":"617"},"1":{"id":"8","event_name":null,"day":"3","start_time":"510","end_time":"617"}}
/*
	if(text.success)
*/

var blocks = [];
function displaySelected(){								//generates a block for the selected option
	var sel = document.getElementById("sbox").value;
	var start = ((text.data[sel].start_time < start_of_day) ? 420 : text.data[sel].start_time);
	var end = ((text.data[sel].end_time > (900 + start_of_day)) ? (900 + start_of_day) : text.data[sel].end_time);
	if(end > start_of_day && start < (900 + start_of_day) && blocks[sel] == null){
		blocks[sel] = addBlock(scheduleLayer, text.data[sel].day, start, end); 
	}	
};
	
function removeSelected(){								//removes the block of the selected option
	var sel = document.getElementById("sbox").value;
	if(blocks[sel] != null){
		scheduleLayer.remove(blocks[sel]);
		stage.add(scheduleLayer);
		blocks[sel] = null;
	}
};


function sendEvent(){
	var startTime = document.getElementById("sbox1").value;
	var endTime = document.getElementById("sbox2").value;
	var day = document.getElementById("sbox3").value;
	var jsonGet = new XMLHttpRequest();
    jsonGet.open("POST","./json/SaveEvent.php?day=" + day + "&start_time=" + startTime + "&end_time=" + endTime);
    jsonGet.send();
};

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
//logout and register functions -> ajax
//classes stored as array of objects
//displayed classes stored as objects, linked to displayed blocks
//select buttons display classes
//user controlled events on menus, building them
//saving events
//hiding class listings
//times as times (not pixels)

//login works on any credentials?
//saved schedules?
