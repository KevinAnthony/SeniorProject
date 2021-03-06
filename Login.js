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
			data = $.parseJSON(data);
			if(data.success){
				$("#lineBreaks").show();
	 			$("#loginForm").hide();
				$("#logField").html("Welcome " + username + "!");
				loadSchedules();
			
				if($("#subjectSelector").val() == 99){
					loadClasses();
				} 
			} else {
				alert(data.printable_error_message);
				$("#logField").html("Try again.");
			}
 			console.log(data);  
		},
		error: function(){
    		console.log(data);
  		}

	});
};

function register() {
	var username = $("#regUsername").val();
	var password = $("#regPassword").val();
	if(password == $("#confPassword").val()){
		$.ajax({
			type: "POST",
			url: "./json/Register.php",
			data: {"username":username,"password":password},
			success: function(data){
	 			$("#logFieldReg").html("Success!");
	 			console.log(data);  
	 			$("#username").val(username);
	 			$("#password").val(password);
	 			login();
			},
			error: function(){
				$("#logFieldReg").html("An error occured.");
				console.log(data);
	  		}

		});
	} else {
		$("#logFieldReg").html("Please retype your passwords.");
	}
};
	
	
function logout() {
	$.ajax({
		type: "POST",
		url: "./json/Logout.php",
		success: function(data){
			$("#lineBreaks").hide();
			$("#loginForm").show();
			$("#logField").html("You are not logged in.");
			showSchedule(0);
			
			if(schedules.length > 1){
				schedules.splice(1, schedules.length-1);
				loadSchedules();
				loadListings();
			}
			
			console.log(data);  
			setTimeout(function() { $("#savedSchedules").html("<button onClick=\"showSchedule(0)\" onMouseOver=\"\" class=\"scheduleButton\">" + schedules[0].sname + "</button>") }, 2000);  
 			
		},
		error: function(){
    		console.log(data);
  		}

	});
};
