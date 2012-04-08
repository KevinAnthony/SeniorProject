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
