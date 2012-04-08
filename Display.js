var onSchedule = [];

function addToSchedule(CRN,eventToggle){
	var toAdd;
	var eID = CRN; 
	
	if(eventToggle == 0) {		//item to add
		toAdd = courses[CRN];
	} else {
		toAdd = events[eID];
	}
	
	var i, j, k;
	var flagCheck = true;
	
	for(i=0; i < onSchedule.length; i++){
		var check;
		
		if(eventToggle == 0) {
			check = (CRN == onSchedule[i].CRN);
		} else {
			check = (eID == (onSchedule[i].id + onSchedule[i].event_name));
		}
		
		if(check){
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
	
	if(flagCheck && (eventToggle == 0)){
		var text = [];
		var shapes = [];
		var texts = [];
		var both = [];
		
		text.push(toAdd.cname);
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
	} else if(flagCheck && (eventToggle == 1)){
		var text = [];
		var shapes = [];
		var texts = [];
		var both = [];
		
		text.push(toAdd.event_name);
	
		var dayText = text.slice(0);
		dayText.push(pixelsToTime(toAdd.start_time) + " - " + pixelsToTime(toAdd.end_time));
		both = addBlock(scheduleLayer, textLayer, dayText, toAdd.day, toAdd.start_time, toAdd.end_time);

		shapes.push(both[0]);
		texts.push(both[1]);

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
