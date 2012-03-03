var stage;
var wInner;
var offset;
var start_of_day;
var scheduleLayer = new Kinetic.Layer();

window.onload = function(){
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
    
};



var subjects = [];
var courses = [];
var sections = [];
var descriptions = [];

function loadSections(value) {
	var sel = "./json/GetClassTimes.php?course_number=" + document.getElementById("classSelector").value + "&department=" + document.getElementById("subjectSelector").value;
	
	if(sel != 1){
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
				
			    for(i=0; i < text.data.length; i++){
			    	var section = text.data[i].crn;
			    	if(text.data[i+1] != undefined)
						var nextCrn = text.data[i+1].crn;

					var professor = text.data[i].instructor;
					
			    	days += text.data[i].day + "<br>";
			    	rooms += text.data[i].room + "<br>";
					times += text.data[i].start_time + " - " + text.data[i].end_time + "<br>";
																	    	
			    	if(i+1 == text.data.length || section != nextCrn){
						list += "<tr><td>" + section + "</td><td>" + days + "</td><td>" + times + "</td><td>" + rooms + "</td><td>" + professor + "</td><td id=\"buttonCol\"><button class=\"scheduleButton\">Select</button></td></tr>";
		        		sections[i] = section;
		        		days = "";
		        		times = "";
		        		rooms = "";
            		}
            	}
            	if(list != undefined)
					document.getElementById("sectionTable").innerHTML=list;
			}
		};
		jsonGet.send();
	}
};

function loadClasses() {
	var sel = "./json/GetCourseNumbers.php?department=" + document.getElementById("subjectSelector").value;
	
	if(sel != 1){
		var jsonGet = new XMLHttpRequest();
		jsonGet.open("GET", sel);
	    jsonGet.onreadystatechange = function () {
			if(jsonGet.readyState == 4 && jsonGet.status == 200){
			    text = JSON.parse(jsonGet.responseText);
			    var i;
			    var list = "<select id=\"classSelector\" onChange=\"loadSections(options[selectedIndex].id)\" class=\"selector\"><option id=\"-1\" value=\"1\">Select Course</option>";
			    for(i=0; i < text.data.length; i++){
			    	var course = text.data[i].course_number;
			    	var courseName = text.data[i].course_name;
			    	descriptions[i] = text.data[i].description;
	    			list += "<option id=\"" + i + "\" value=\"" + course + "\" class=\"opt\">" + course + " - " + courseName + "</option>";
            		subjects[i] = course;
            	}
            	list += "</select>";
				document.getElementById("classColumn").innerHTML=list;
			}
		};
		jsonGet.send();
	}
};

function loadSubjects(){
	var jsonGet = new XMLHttpRequest();
	jsonGet.open("GET","./json/GetSubjects.php");
    jsonGet.onreadystatechange = function () {
		if(jsonGet.readyState == 4 && jsonGet.status == 200){
	        text = JSON.parse(jsonGet.responseText);
	        var i;
	        var list = "<select id=\"subjectSelector\" onChange=\"loadClasses()\" class=\"selector\"><option id=\"-1\" value=\"1\">Select Subject</option>";
	        for(i=0; i < text.data.length; i++){
	        	var dept = text.data[i].department;
    			list += "<option value=\"" + dept + "\" class=\"opt\">" + dept + "</option>";
        		subjects[i] = dept;
        	}
        	list += "</select>";
			document.getElementById("subjectColumn").innerHTML=list;
    	}
    };
    jsonGet.send();
};


//start_time, end_time in minutes
function addBlock(scheduleLayer, day, start_time, end_time){	//generates a rectangle of specified dimensions
	var block = new Kinetic.Shape(function(){
		var tX = DayToPixels(day);
		var tY = TimeToPixels(start_time);
		var w = DayToPixels(day+1) - tX;
		var h = TimeToPixels(end_time) - tY;
		var context = this.getContext();
        
		context.rect(tX, tY, w, h);
		context.fillStyle = "#c9c9c9";//"#C9C9C7";
		context.fill();4	
		context.lineWidth = 2;
		context.strokeStyle = "#000000";
		
		context.stroke(); //draw
	});

	scheduleLayer.add(block);
	stage.add(scheduleLayer);
	return block;
};


function TimeToPixels(time){						//adjusts the time by the offset and the start time of calendar
	var position;
	position = time - (start_of_day - offset);
	
	//console.write(pixies + '\n');
	return position;
};

function DayToPixels(day){							//returns the pixel position of a given day
	var pixals = 0;
	pixals += (wInner/14) + (day * (wInner/7));
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
	var overlay = document.getElementById("overlay");
	overlay.style.visibility = (overlay.style.visibility == "visible") ? "hidden" : "visible";
};

function login() {
	var form = document.getElementById("loginForm");
	var username = form.username.value;
	var password = form.password.value;
	
	this.http.open("POST", this.action, false, username, password);
	this.http.send(""); 
	if (http.status == 200) { 
		//document.location = this.action; 
	} else { 
		alert("Incorrect username and/or password."); 
	} 
	
	return false;
	/*
	var jsonGet = new XMLHttpRequest();
	jsonGet.open("POST","./json/Login.php?username=" + username + "&password=" + password);
    jsonGet.onreadystatechange = function () {
		if(jsonGet.readyState == 4 && jsonGet.status == 200){
	        text = JSON.parse(jsonGet.responseText);
	        
	        console.log(text.success);
    	}
    };
    jsonGet.send();//*/

};

//Block size to font size
//text handling
//wrap create block