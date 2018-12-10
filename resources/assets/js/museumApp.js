

function showNotification(message, customParams){

	var params = {
		life : 4000,
		// life : 10000000000,
		color : "teal", // blue...
		// onClose : function(){
			// window.location = "/home";
		// }
	}

	for(param in customParams){
		params[param] = customParams[param];
	}

	message = '<div class="museum-notif">'

			+ '<div class="icon-box">'
				+ '<span> </span>'
			+ '</div>'
			+ '<div class="message-box">'
				+ message
			+ '</div>'
			+ '<div class="close-box" onclick="closeNotification(); return false;">'
				+ 'x'
			+ '</div>'

		+ '</div>';

	$.notific8(message, params);
}
function closeNotification(){
	$('.notific8-notification').remove();
	// $('.notific8-notification').removeClass("open");
}

function laravelValidateErrorsToArray(jsonResponse){

	var errors = [];

	if(jsonResponse){
		for(field in jsonResponse){
			// console.log("Field:");
			// console.log( res.responseJSON[field]);
			for(let i = 0; i < jsonResponse[field].length; i++){
				errors.push(jsonResponse[field][i]);
			}
		}
	}

	return errors;
}

function laravelValidateMarkInvalidFields(jsonResponse, formSelector){
	if(jsonResponse){
		for(field in jsonResponse){
			// console.log("Field:");
			// console.log( res.responseJSON[field]);
			for(let i = 0; i < jsonResponse[field].length; i++){
				// errors.push(jsonResponse[field][i]);
			}

			var tmpSelector = formSelector + ' input[name='+field+']'
					+ "," + formSelector + "textarea[name="+field+"]"
					+ "," + formSelector + "select[name="+field+"]";

			// console.log("SELECTOR:" + tmpSelector);
			$(tmpSelector).addClass("input-invalid");
		}
	}
}

/**
* Login via AJAX.
* @param JSON postdata (username and password)
**/
function doLogin(postData){

	$.ajax({
		type : "POST",
		url : '/api/user/ajax_login',
		data : postData,
		success : function(res){

			// console.log("login res:");
			// console.log(res);

			if(res.success && res.success == true){

				showNotification('You are now logged in! Redirecting to home page...', {
					onClose : function(){
						window.location = "/home";
					}
				});

			} else {
				showNotification('Login failed!');
			}
		}
	});
}

function showLoginForm(){
	$("#formResetPassword").hide();
	$("#formLogin").slideDown();

	$("#loginFormPopupTitle").html("Login to your account");
}
function showResetPasswordForm(){
	$("#formLogin").hide();
	$("#formResetPassword").slideDown();

	$("#loginFormPopupTitle").html("Request a new password");
}

// On document ready
$(function(){

	initEvents();

});

// 
function initEvents(){


	$("input , select, textarea").on('keypress', function(){
		$(this).removeClass("input-invalid");
	});

	// When any bootstrap modal is opened hide menu bar...
	$(".modal").on("show.bs.modal", function () {
		$(".navbar").hide();
	});
	$(".modal").on("hide.bs.modal", function(){
		$(".navbar").show();
	});


	$("#formRegister").on('submit', function(){

		$("#btnRegister").addClass("disabled");

		var registrationErrorMessage = function(res){

			$("#btnRegister").removeClass("disabled");

			console.log(res);

			// List of errors...
			var errors = "";
			var laravelErrors = laravelValidateErrorsToArray(res.responseJSON);
			laravelValidateMarkInvalidFields(res.responseJSON, '#formRegister');

			for(let i = 0; i < laravelErrors.length; i++){
				errors += '<p>' + laravelErrors[i] + '</p>';
			}

			showNotification(errors);
		}

		// Post data
		var pData = $(this).serialize();

		$.ajax({
			type : "POST",
			url : '/api/user/ajax_register',
			data : pData,
			success : function(res){
				
				if(res.success){

					$("#formRegister input").val("");

					showNotification("You are now registered!<br/>You will be signed in a few moments...", {
						onClose : function(){
							window.location = "/home";
						}
					});
				} else {
					registrationErrorMessage(res);
				}
			},
			error : registrationErrorMessage
		});
		
		return false;
	});



	$("#formLogin").on('submit', function(){

		var pData = $(this).serialize();

		doLogin(pData);

		return false;
	});

	$("#btnOpenResetPassword").click(function(){
		showResetPasswordForm();
		return false;
	});

	$("#btnOpenLoginForm").click(function(){
		showLoginForm();
		return false;
	});



	$("#formResetPassword").on("submit", function(){

		var pData = $(this).serialize();

		$.ajax({
			type : "POST",
			url : '/api/user/ajax_forgot_password',
			data : pData,
			success : function(res){
				if(res.success){
					$("#formRegister input").val("");
					showNotification("A reset link is sent. Please check your email.");
					showLoginForm();
				} else {
					var errors = laravelValidateErrorsToArray(res.responseJSON);
					showNotification("ERROR: Can't reset password" + errors);
				}
			},
			error : function(res){
				var errors = laravelValidateErrorsToArray(res.responseJSON);
				showNotification("ERROR: Can't reset password " + errors);
			}
		});

		return false;
	});

} //initEvents()