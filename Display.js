

function addToSchedule(schedule, addFrom, CRN, eventToggle, display){
	var toAdd;
	var eID = CRN; 
	
	toAdd = addFrom[CRN];
	
	var i, j, k;
	var flagCheck = true;
	
	for(i=0; i < schedule.onSchedule.length; i++){
		var check;
		
		if(eventToggle == 0) {
			check = (CRN == schedule.onSchedule[i].CRN);
		} else {
			check = (eID == (schedule.onSchedule[i].id + schedule.onSchedule[i].event_name));
		}
		
		if(check){
			console.log("Already on Schedule.");
			for(j=0; j < schedule.onSchedule[i].shapeLayer.length; j++){
				schedule.scheduleLayer.remove(schedule.onSchedule[i].shapeLayer[j]);
				schedule.textLayer.remove(schedule.onSchedule[i].textLayer[j]);
			}
			stage.add(schedule.scheduleLayer);
			stage.add(schedule.textLayer);
			
			schedule.onSchedule.splice(i,1);
			loadListings();
			return;
		}
	}
	//add check against events
	for(i=0; i < schedule.onSchedule.length; i++){
		for(j=0; j < schedule.onSchedule[i].day.length && flagCheck; j++){
			for(k=0; k < toAdd.day.length && flagCheck; k++){
				if(toAdd.day[k] == schedule.onSchedule[i].day[j]){
					if(toAdd.start_time[k] >= schedule.onSchedule[i].start_time[j] && toAdd.start_time[k] <= schedule.onSchedule[i].end_time[j])
						flagCheck = false;
					
					if(toAdd.end_time[k] >= schedule.onSchedule[i].start_time[j] && toAdd.end_time[k] <= schedule.onSchedule[i].end_time[j])
						flagCheck = false;
						
					if(toAdd.start_time[k] <= schedule.onSchedule[i].start_time[j] && toAdd.end_time >= schedule.onSchedule[i].end_time[j])
						flagCheck = false;
						
				}
			}
		}
		if(!flagCheck)
			break;
	}
	
	if(flagCheck && (eventToggle == 0)){
		var text = [];
		var shapes = [];
		var texts = [];
		var both = [];
		
		text.push(toAdd.cname);
		
		if(toAdd.CRN != undefined)
			text.push("(" + toAdd.CRN + ")");
		else if(toAdd.CRN != undefined)
			text.push("(" + toAdd.CRN + ")");
	
		for(i=0; i < toAdd.day.length; i++){
			var dayText = text.slice(0);
			dayText.push(toAdd.room[i] + " " + pixelsToTime(toAdd.start_time[i]) + " - " + pixelsToTime(toAdd.end_time[i]));
			both = addBlock(schedule.scheduleLayer, schedule.textLayer, dayText, toAdd.day[i], toAdd.start_time[i], toAdd.end_time[i], display);

			shapes.push(both[0]);
			texts.push(both[1]);
		}
		toAdd.shapeLayer = shapes;
		toAdd.textLayer = texts;
		schedule.onSchedule.push(toAdd);
	} else if(flagCheck && (eventToggle == 1)){
		var text = [];
		var shapes = [];
		var texts = [];
		var both = [];
		
		text.push(toAdd.event_name);
	
		var dayText = text.slice(0);
		dayText.push(pixelsToTime(toAdd.start_time) + " - " + pixelsToTime(toAdd.end_time));
		both = addBlock(schedule.scheduleLayer, schedule.textLayer, dayText, toAdd.day, toAdd.start_time, toAdd.end_time, display);

		shapes.push(both[0]);
		texts.push(both[1]);

		toAdd.shapeLayer = shapes;
		toAdd.textLayer = texts;
		schedule.onSchedule.push(toAdd);
	} else {
		console.log("Scheduling Conflict");
		alert("Cannot add course: Scheduling Conflict");
	}
	
	loadListings();
};

//start_time, end_time in minutes
function addBlock(scheduleLayer, textLayer, dayText, day, start_time, end_time, display){	//generates a rectangle of specified dimensions
	var tX = DayToPixels(parseInt(day));
	var tY = TimeToPixels(parseInt(start_time));
	var h;
	
	var check = dayText[0].indexOf("&amp;");
	if(check != -1){
		dayText[0] = dayText[0].slice(0, check+1) + dayText[0].slice(check+5);
	}
	
	var block = new Kinetic.Shape(function(){
		var w = DayToPixels(parseInt(day)+1) - tX;
		h = TimeToPixels(parseInt(end_time)) - tY;
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
		var count;
		
		var context = this.getContext();
		context.fillStyle = "#000000";
		if(h < 36){
			count = 1;
		} else if(h < 50){
			count = 2;
		} else {
			count = dayText.length;
		}
		
		for(i=0; i < count; i++){
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
	blockText.on("mousedown", function() {window.location.hash = '#bottomList';});
	scheduleLayer.add(block);
	textLayer.add(blockText);	
	//scheduleLayer.add(text);
	if(display) {
		stage.add(scheduleLayer);
		stage.add(textLayer);
	}
	
	return [block, blockText];
};


function pixelsToTime(pixels){
	var hours = parseInt(pixels / 60).toString();
	var minutes = parseInt(pixels % 60).toString();
	hours = (hours.length == 1 ? "0" + hours : hours);
	minutes = (minutes.length == 1 ? "0" : "") + minutes;
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
