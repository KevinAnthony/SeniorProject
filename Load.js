var stage;
var wInner;
var offset;
var start_of_day;
var schedules = [];
var currentSchedule;

$(document).ready(function(){
	logout();
	$("#courseSlide").css("left", -$("#courseSlide").outerWidth());
	var scheduleLayer = new Kinetic.Layer();
	var textLayer = new Kinetic.Layer();
	var newschedule = {"sname":"New","textLayer":textLayer, "scheduleLayer":scheduleLayer, "onSchedule":[]};
	schedules.push(newschedule);
	currentSchedule = schedules[0];
	
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
    //loadSubjects();
    
    $("#loginForm").onsubmit = "login()";
    
});
