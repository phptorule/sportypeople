/**
 * @author Will Steinmetz
 * notific8 Javascript plug-in
 * Copyright (c)2013-2016, Will Steinmetz
 * Licensed under the BSD license.
 * http://opensource.org/licenses/BSD-3-Clause
 */
var notific8;
var terms_check = false;

notific8 = (function() {
  window.notific8Defaults = {
    life: 10000,
    theme: 'ocho',
    color: 'teal',
    sticky: false,
    verticalEdge: 'right',
    horizontalEdge: 'top',
    zindex: 1100,
    closeText: 'close',
    onInit: [],
    onCreate: [],
    onClose: [],
    namespace: 'notific8',
    queue: false
  };
  window.notific8RegisteredModules = {
    beforeContent: [],
    afterContent: [],
    beforeContainer: [],
    afterContainer: [],
    insideContainer: []
  };
  window.notific8Queue = [];
  window.notific8DataStore = {};
  window.notific8ContainerHandlers = {
    onContainerCreate: []
  };

  /**
   * Destroy the notification
   * @param {Object} options object defining the settings of the notification
   */
  function destroy(options) {
    let containerClass = options.namespace + "-container",
      containers = document.getElementsByClassName(containerClass),
      body = document.getElementsByTagName('body')[0];

    while (containers.length > 0) {
      body.removeChild(containers[0]);
    }
  }

  /**
   * Get the container that the notification is inside of
   * @params {Object} data object defining the settings of the notification
   * @return {Object}      html DOM object for the container
   */
  function getContainer(data) {
    let { verticalEdge, horizontalEdge, namespace } = data.settings,
      containerClass = `.${namespace}-container.${verticalEdge}.${horizontalEdge}`;

    return document.querySelector(containerClass);
  }

  /**
   * Build the notification close HTML
   * @param  {Object} data object defining the settings of the notification
   * @return {String}      HTML for rendering the close button of the notification
   */
  function buildClose(data) {
    let closeClasses = [ `${data.settings.namespace}-close` ],
      closeText = '&times;';

    if (data.settings.sticky) {
      closeClasses.push('sticky');
      closeText = data.settings.closeText;
    }

    return `<button type="button" class="${closeClasses.join(' ')}" aria-label="dismiss notification">${closeText}</button>`;
  }

  /**
   * Build the HTML for the heading if it is there
   * @param  {Object} data object defining the settings of the notification
   * @return {String}      HTML for the heading part of the notification
   */
  function buildHeading(data) {
    if (
      (data.settings.heading !== null) &&
      (typeof data.settings.heading === "string")
    ) {
      return `<header class="${data.settings.namespace}-heading">${data.settings.heading}</header>`;
    } else {
      return "";
    }
  }

  /**
   * Build the message HTML for the notification
   * @param  {Object} data object defining the settings of the notification
   * @return {String}      HTML for the message part of the notification
   */
  function buildMessage(data) {
    return `<div class="${data.settings.namespace}-message">${data.message}</div>`;
  }

  /**
   * Build the list of notification classes to apply
   * @param  {Object} data object defining the settings of the notification
   * @return {Array}       array of classes to assign to the notification
   */
  function notificationClasses(data) {
    let classes;

    // @TODO remove for 5.0.0 - deprecated
    if (data.settings.theme.toLowerCase() === 'legacy') {
      data.settings.theme = 'ocho';
    }
    classes = [
      `${data.settings.namespace}-notification`,
      `family-${data.settings.theme}`,
      data.settings.theme,
      data.settings.color
    ];
    if (data.settings.sticky) {
      classes.push("sticky");
    }
    if (
      (data.settings.heading !== null) &&
      (typeof data.settings.heading === "string")
    ) {
      classes.push("has-heading");
    }
    return classes;
  }

  /**
   * Build the notification and add it to the screen's stack
   * @param {Object} data object defining the data for building the notification
   */
  function buildNotification(data) {
    var body = document.getElementsByTagName('body')[0],
      container = getContainer(data),
      generatedNotificationClasses = notificationClasses(data),
      namespace = data.settings.namespace,
      num = Number(body.dataset.notific8s) + 1,
      notificationId = `${namespace}-notification-${num}`,
      notification = `<article class="$notificationClasses" id="${notificationId}" data-name="${data.settings.notificationName}" role="status" aria-live="polite">`,
      beforeContentModules = notific8RegisteredModules.beforeContent,
      afterContentModules = notific8RegisteredModules.afterContent,
      onCreateHandlers = data.settings.onCreate;

    body.dataset.notific8s = num;
    for (let i = 0, len = beforeContentModules.length; i < len; i++) {
      let module = beforeContentModules[i],
        moduleResults = module.callbackMethod(data);
      generatedNotificationClasses = generatedNotificationClasses.concat(moduleResults.classes);
      notification += moduleResults.html;
    }

    notification += `<div class="${data.settings.namespace}-message-content">${buildHeading(data)} ${buildMessage(data)}</div>`;
    for (let j = 0, len = afterContentModules.length; j < len; j++) {
      let module = afterContentModules[j],
        moduleResults = module.callbackMethod(data);
      generatedNotificationClasses = generatedNotificationClasses.concat(moduleResults.classes);
      notification += moduleResults.html;
    }

    notification += `${buildClose(data)}</article>`;
    notification = notification.replace('$notificationClasses', generatedNotificationClasses.join(' '));
    container.innerHTML += notification;

    for (let k = 0, len = onCreateHandlers.length; k < len; k++) {
      let onCreate = onCreateHandlers[k];
      onCreate(notification, data);
    }

    setTimeout(function() {
      let notification = document.getElementById(notificationId);

      // need to make sure the notification still exists in case of a race
      // condition due to calling the remove method several times
      if (!notification) {
        return;
      }

      notification.className += " open";
      notific8DataStore[notificationId] = data;
      if (!data.settings.sticky) {
        (function(n, l) {
          setTimeout(function() {
            closeNotification(notificationId, data);
          }, l);
        })(notification, Number(data.settings.life) + 200);
      }
    }, 5);
  };

  /**
   * Close the given notification
   * @param {String} notificationId notification ID to look for
   * @param {Object} data           object defining the data for building the notification
   */
  function closeNotification(notificationId, data) {
    let n = document.getElementById(notificationId);

    // if something happened to cause the notifcation to be removed from the
    // screen before this method is called (such as with remove), we need to
    // return so that there isn't an error in the console
    if (n === null) {
      return;
    }

    n.className = n.className.replace('open', '');

    // it's possible this method may be called in quick succession so we need
    // to isolate scope to this notification
    (function(notification, notificationId) {
      let container = getContainer(data),
        next,
        onClose,
        onCloseCallbacks;

      setTimeout(function() {
        container.removeChild(notification);
        delete notific8DataStore[notificationId];

        if (data.settings.onClose.length) {
          onCloseCallbacks = data.settings.onClose;
          for (let i = 0, len = onCloseCallbacks.length; i < len; i++) {
            onClose = onCloseCallbacks[i];
            onClose(notification, data);
          }
        }

        // call the next notification in the queue
        if (notific8Defaults.queue && notific8Queue.length) {
          next = notific8Queue.shift();
          notific8(next.message, next.options);
        }
      }, 200);
    })(n, notificationId);
  }

  /**
   * Set up the configuration settings
   * @param {Object} options object containing the options to configure as the defaults
   */
  function configure(options) {
    let key, option;

    for (key in options) {
      option = options[key];
      if (['onInit', 'onCreate', 'onClose'].indexOf(key) > -1) {
        if (typeof option === 'function') {
          notific8Defaults[key].push(option);
        } else {
          notific8Defaults[key] = notific8Defaults[key].concat(option);
        }
      } else if (key === 'onContainerCreate') {
        if (typeof option === 'function') {
          notific8ContainerHandlers.onContainerCreate.push(option);
        } else {
          notific8ContainerHandlers.onContainerCreate = notific8ContainerHandlers.onContainerCreate.concat(option);
        }
      } else {
        notific8Defaults[key] = option;
      }
    }
  }

  /**
   * Remove the currently visible notifications from the screen
   * @param {Object} options object containing the options that build the notifications
   */
  function remove(options) {
    let notificationClass = `${options.namespace}-notification`,
      notifications = document.getElementsByClassName(notificationClass);

    while (notifications.length > 0) {
      notifications[0].parentNode.removeChild(notifications[0]);
    }
  }

  /**
   * Remove the given notification names from the queue
   * @param {Mixed} notificationNames list of notifications
   */
  function removeFromQueue(notificationNames) {
    let key, notification;

    if (typeof notificationNames !== "object") {
      notificationNames = [notificationNames];
    }

    for (let i = 0, len = notificationNames.length; i < len; i++) {
      notification = notificationNames[i];
      for (key in notific8Queue) {
        if (notific8Queue[key].options.notificationName === notification) {
          notific8Queue.splice(key, 1);
          break;
        }
      }
    }
  }

  /**
   * Set up the z-index
   * @param {Integer} z the z-index to set as the default
   */
  function zindex(z) {
    notific8Defaults.zindex = z;
  }

  /**
   * Initialize the plug-in
   * @param {String} message string representing the message for the notification
   * @param {Object} options options to build the notification with
   */
  function init(message, options) {
    let arrayKeys = ['onInit', 'onCreate', 'onClose'],
      data = {
        settings: {},
        message: message
      },
      key,
      onInit,
      option,
      propertiesToRemove,
      onInitHandlers;

    for (key in notific8Defaults) {
      option = notific8Defaults[key];
      data.settings[key] = option;
    }
    for (key in options) {
      option = options[key];
      if (arrayKeys.indexOf(key) > -1) {
        if (typeof option === 'function') {
          option = [option];
        }
        for (let i = 0, len = option.length; i < len; i++) {
          data.settings[key].push(option[i]);
        }
      } else {
        data.settings[key] = option;
      }
    }

    propertiesToRemove = ['onContainerCreate', 'queue'];
    for (let j = 0, len = propertiesToRemove.length; j < len; j++) {
      delete data.settings[propertiesToRemove[j]];
    }

    buildNotification(data);
    if (data.settings.onInit.length) {
      onInitHandlers = data.settings.onInit;
      for (let k = 0, len = onInitHandlers.length; k < len; k++) {
        onInit = onInitHandlers[k];
        onInit(data);
      }
    }
  }

  /**
   * Initialize the containers for the plug-in
   * @param {Object} options to associate with the notification containers
   */
  function initContainers(options) {
    var body = document.getElementsByTagName('body')[0],
      containerClasses = [`${options.namespace}-container`],
      containerStr = "",
      beforeContainerModules,
      insideContainerModules,
      afterContainerModules,
      containerPositions,
      containers,
      onContainerCreateHandlers;

    body.dataset.notific8s = 0;

    beforeContainerModules = notific8RegisteredModules.beforeContainer;
    for (let i = 0, len = beforeContainerModules.length; i < len; i++) {
      let module = beforeContainerModules[i],
        moduleResults = module.callbackMethod(notific8Defaults);
      containerClasses = containerClasses.concat(moduleResults.classes);
      containerStr += moduleResults.html;
    }

    containerStr += '<div class="$classes $pos">';
    insideContainerModules = notific8RegisteredModules.insideContainer;
    for (let j = 0, len = insideContainerModules.length; j < len; j++) {
      let module = insideContainerModules[j],
        moduleResults = module.callbackMethod(notific8Defaults);
      containerClasses = containerClasses.concat(moduleResults.classes);
      containerStr += moduleResults.html;
    }
    containerStr += '</div>';

    afterContainerModules = notific8RegisteredModules.afterContainer;
    for (let k = 0, len = afterContainerModules.length; k < len; k++) {
      let module = afterContainerModules[k],
        moduleResults = module.callbackMethod(notific8Defaults);
      containerClasses = containerClasses.concat(moduleResults.classes);
      containerStr += moduleResults.html;
    }

    containerPositions = ['top right', 'top left', 'bottom right', 'bottom left'];
    for (let m = 0, len = containerPositions.length; m < len; m++) {
      let position = containerPositions[m],
        modifiedContainerStr = containerStr.replace('$pos', position).replace('$classes', containerClasses.join(' ')),
        tempDoc = document.implementation.createHTMLDocument('tempDoc');
      tempDoc.body.innerHTML = modifiedContainerStr;
      document.body.appendChild(tempDoc.body.firstChild);
    }

    containers = document.getElementsByClassName(containerClasses[0]);
    for (let o = 0, len = containers.length; o < len; o++) {
      let container = containers[o];
      container.style.zIndex = notific8Defaults.zindex;

      onContainerCreateHandlers = notific8ContainerHandlers.onContainerCreate;
      for (let p = 0, len = onContainerCreateHandlers.length; p < len; p++) {
        let handler = onContainerCreateHandlers[p];
        handler(container, options);
      }

      container.addEventListener("click", function(event) {
        let data,
          target = event.target,
          notification = target.parentElement,
          notificationClass = `${options.namespace}-notification`;

        if (notification.className.split(' ').indexOf(notificationClass) === -1) {
          return;
        }

        data = notific8DataStore[notification.id];
        closeNotification(notification.id, data);
      });
    }
  }

  /**
   * Make sure that the edge options are ok
   * @param {Object} options options for building the notification
   */
  function checkEdges(options) {
    options.verticalEdge = (options.verticalEdge || notific8Defaults.verticalEdge).toLowerCase();
    options.horizontalEdge = (options.horizontalEdge || notific8Defaults.horizontalEdge).toLowerCase();
    if (['left', 'right'].indexOf(options.verticalEdge) === -1) {
      options.verticalEdge = notific8Defaults.verticalEdge;
    }
    if (['top', 'bottom'].indexOf(options.horizontalEdge) === -1) {
      options.horizontalEdge = notific8Defaults.horizontalEdge;
    }
  }

  /**
   * Displays an error message to the console and throws an error
   * @param {String} message the error message to display
   */
  function errorMessage(message) {
    console.error(message);
    throw new Error(message);
  }

  /**
   * Register a module for use in the system
   * @param {String} moduleName       name of the module to register
   * @param {String} position         position of the module's excution
   * @param {Object} defaultOptions   default options for the module
   * @param {Function} callbackMethod method to call for the module
   */
  function registerModule(moduleName, position, defaultOptions, callbackMethod) {
    let defaultValue,
      module,
      option,
      modulesRegisteredToPosition,
      validPositions;

    if (typeof moduleName !== 'string' || moduleName.trim() === '') {
      errorMessage("moduleName should be a string");
    }
    validPositions = ['beforeContent', 'afterContent', 'beforeContainer', 'afterContainer', 'insideContainer'];
    if (typeof position !== 'string' || validPositions.indexOf(position) === -1) {
      errorMessage("position should be a string");
    }
    if (typeof defaultOptions !== 'object') {
      errorMessage("defaultOptions should be an object");
    }
    if (typeof callbackMethod !== 'function') {
      errorMessage("callbackMethod should be an function");
    }
    modulesRegisteredToPosition = notific8RegisteredModules[position];
    for (let i = 0, len = modulesRegisteredToPosition.length; i < len; i++) {
      module = modulesRegisteredToPosition[i];
      if (module.moduleName === moduleName) {
        errorMessage(`Module '${moduleName}' has already been registered`);
      }
    }
    for (option in defaultOptions) {
      defaultValue = defaultOptions[option];
      notific8Defaults[option] = defaultValue;
    }
    return notific8RegisteredModules[position].push({
      moduleName: moduleName,
      callbackMethod: callbackMethod
    });
  }

  /**
   * Generates a unique name to assocate with the notification
   * Solution found as an answer on StackOverflow:
   * http://stackoverflow.com/a/2117523/5870787
   * @return {String} string generateiond for the notification
   */
  function generateUniqueId() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      let r, v;
      r = Math.random() * 16 | 0;
      v = c === 'x' ? r : r & 0x3 | 0x8;

      return v.toString(16);
    });
  }

  /**
   * Public method for the notification that drives the plug-in
   * @param  {String} message  message for the notification or notific8 method to call
   * @param  {Options} options options for the notification or notific8 method called
   * @return {Mixed}
   */
  return function(message, options) {
    let callbackMethod,
      containerClass,
      defaultOptions,
      moduleName,
      notificationClass,
      num,
      position;

    if (typeof message !== "string") {
      errorMessage("notific8 takes a string message as the first parameter");
    }
    if (options === undefined) {
      options = {};
    }
    if (typeof options === 'object' && !options.hasOwnProperty('namespace') && message !== 'zindex') {
      options.namespace = 'notific8';
    }

    switch (message) {
      case "configure":
      case "config":
        return configure(options);
      case "zindex":
        return zindex(options);
      case "destroy":
        return destroy(options);
      case "remove":
        return remove(options);
      case "removeFromQueue":
        return removeFromQueue(options);
      case "registerModule":
        if (arguments.length !== 5) {
          errorMessage("Registering a module requires the parameters moduleName, position, defaultOptions, and callbackMethod.");
        }
        message = arguments[0];
        [ , moduleName, position, defaultOptions, callbackMethod ] = arguments;
        return registerModule(moduleName, position, defaultOptions, callbackMethod);
      default:
        containerClass = options.namespace + "-container";
        if (document.getElementsByClassName(containerClass).length === 0) {
          initContainers(options);
        }
        checkEdges(options);
        notificationClass = options.namespace + "-notification";
        num = document.getElementsByClassName(notificationClass).length;
        if (!options.notificationName) {
          options.notificationName = generateUniqueId();
        }
        if (!notific8Defaults.queue || num === 0) {
          init(message, options);
        } else {
          notific8Queue.push({
            message: message,
            options: options
          });
        }
        return options.notificationName;
    }
  };
})();

/**
 * @author Will Steinmetz
 * Notification Javascript plug-in - jQuery wrapper
 * Copyright (c)2013-2016, Will Steinmetz
 * Licensed under the BSD license.
 * http://opensource.org/licenses/BSD-3-Clause
 */
(function($) {
  /**
   * wrapper since this plug-in is called without selecting an element first
   * @param string message
   * @param object options
   */
  $.notific8 = function(message, options) {
    notific8(message, options);
  };
})(jQuery);



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

			+ '<div class="icon-box text-center">'
				+ '<span style="color:' + params.color + '" class="fa fa-exclamation-circle"> </span>'
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

			if(res.success && res.success == true){
				var redirect_text = res.full ? 'search' : 'profile' ;
				showNotification('You are now logged in! Redirecting to ' + redirect_text + ' page...', {
					onClose : function(){
						
						if (res.full)
						{
							window.location = "/meeting/create";							
						} else {
							window.location = "/profile";	
						}
					}
				});

			} else {
				showNotification('Login failed!');
			}
		}
	});
}

function showLoginForm()
{
	$("#formResetPassword").hide();
	$("#formLogin").slideDown();
	$("#loginFormPopupTitle").html("Login to your account");
}

function showResetPasswordForm()
{
	$("#formLogin").hide();
	$("#formResetPassword").slideDown();
	$("#loginFormPopupTitle").html("Request a new password");
}

// On document ready
$(function(){
	initEvents();
});

function invite(meeting_id, users_id)
{
	$(this).text('Sent');
	$(this).removeClass('btn-invite');
	$.ajax({
		url: '/save_invite',
		type: 'POST',
		data: {
			'_token': $('meta[name="csrf-token"]').attr('content'),
			'metting_id' : meeting_id,
			'users_id' : users_id
		},
		dataType: 'JSON',
		success: function (data) {
			window.location.reload();
		}
	});
}

// 
function initEvents()
{
	// $(".modal-dialog").niceScroll({'cursorcolor':"#b0afae", 'railpadding' : {'top' : 50, 'left' : 5}});
	
	$("input , select, textarea").on('keypress', function(){
		$(this).removeClass("input-invalid");
	});

	// When any bootstrap modal is opened hide menu bar...
	$(".modal").on("show.bs.modal", function () {
		//$(".navbar").hide();
	});
  
	$(".modal").on("hide.bs.modal", function(){
		$(".navbar").show();
	});


	$("#formRegister").on('submit', function(){

		$("#btnRegister").addClass("disabled").attr('disabled', 'disabled');

		var registrationErrorMessage = function(res){

			$("#btnRegister").removeClass("disabled").removeAttr('disabled');

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
		
		showNotification("We are checking your registration details");

		$.ajax({
			type : "POST",
			url : '/api/user/ajax_register',
			data : pData,
			success : function(res){
				
				if(res.success){

					$("#formRegister input").val("");

					showNotification("You are now registered!<br/>You will be signed in a few moments...", {
						onClose : function(){
							window.location = "/profile";
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
// console.log('res1:');
// console.log(res);

					var errors = laravelValidateErrorsToArray(res.responseJSON);
					showNotification("ERROR: Can't reset password" + errors);
				}
			},
			error : function(res){
				var errors = laravelValidateErrorsToArray(res.responseJSON);
// console.log('res2:');
// console.log(res);
				showNotification("ERROR: Can't reset password " + errors);
			}
		});

		return false;
	});

  $("#terms_cond").change(function(){

    if( ! $("#terms_cond").prop("checked"))
    {
     $(".btn-register").addClass("disabled").attr('disabled', 'disabled');
    }
    else
    {
     $(".btn-register").removeClass("disabled").removeAttr('disabled');
    }
  })


	$('#modalLogin').on('hidden.bs.modal', function (e) {
		showLoginForm();
	})

	//$('#modalLogin').on('hide.bs.modal', show_navbar);
	$('#modalLogin').on('hide.bs.modal', function(){
		$("#formResetPassword").hide();
	});
	//$('#modalRegister').on('hide.bs.modal', show_navbar);

	//$('#modalLogin').on('show.bs.modal', hide_navbar);
	//$('#modalRegister').on('show.bs.modal', hide_navbar);
	
	// =================== MJS SELECT ======================	
	$(".wrapper-select").click(function(){
		$(".mjs-select").removeClass("check");
		$(this).find(".mjs-select").addClass("check");
		
		$('html').click(function(e){
			if( ! $(e.target).hasClass("wrapper-select"))
			{
				$(this).find(".mjs-select").removeClass("check");
			}
		});
		
	  });
	  


    $('#terms_cond_modal').on('hidden.bs.modal', function (e) {
      $("#modalRegister").modal("show");
    })


	$(".mjs-option").click(function(){
		
		  $(this).parent().find(".mjs-option").removeClass("selected");
		  $(this).parent().find(".mjs-value").val($(this).text());
		  $(this).parent().parent().find(".wrapper-header").text($(this).text());
		  var cellback = $(this).parent().find(".mjs-value").attr('data-cellback');
		  
		  if (window[cellback])
		  {
			  window[cellback]($(this).attr('data-option') ? $(this).attr('data-option') : $(this).text());
		  }
		  
		  $(this).parent().parent().find(".wrapper-header")
		  $(this).addClass("selected");
		  setTimeout(function(){		   
			$(".mjs-select").removeClass("check");
		  }, 100);
	  });
	  // =================== MJS SELECT ======================	
	
	// ===================== SELECT LANG ======================
	$(".lang-menu .lng-item").click(function(){
		console.log($(this).parent().find('.lang-btn'));
		$('.lang-btn').text($(this).text().slice(0, 2));
	});
	// ===================== SELECT LANG ======================
	
} //initEvents()

function hide_navbar()
{
	$(".navbar").hide();
}

function show_navbar()
{
	$(".navbar").show();
}
/*! =========================================================
 * bootstrap-slider.js
 *
 * Maintainers:
 *		Kyle Kemp
 *			- Twitter: @seiyria
 *			- Github:  seiyria
 *		Rohit Kalkur
 *			- Twitter: @Rovolutionary
 *			- Github:  rovolution
 *
 * =========================================================
 *
 * bootstrap-slider is released under the MIT License
 * Copyright (c) 2017 Kyle Kemp, Rohit Kalkur, and contributors
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 * ========================================================= */


/**
 * Bridget makes jQuery widgets
 * v1.0.1
 * MIT license
 */
const windowIsDefined = (typeof window === "object");


(function(factory) {
	if(typeof define === "function" && define.amd) {
		define(["jquery"], factory);
	}
	else if(typeof module === "object" && module.exports) {
		var jQuery;
		try {
			jQuery = require("jquery");
		}
		catch (err) {
			jQuery = null;
		}
		module.exports = factory(jQuery);
	}
	else if(window) {
		window.Slider = factory(window.jQuery);
	}
}(function($) {
	// Constants
	const NAMESPACE_MAIN = 'slider';
	const NAMESPACE_ALTERNATE = 'bootstrapSlider';

	// Polyfill console methods
	if (windowIsDefined && !window.console) {
		window.console = {};
	}
	if (windowIsDefined && !window.console.log) {
		window.console.log = function () { };
	}
	if (windowIsDefined && !window.console.warn) {
		window.console.warn = function () { };
	}

	// Reference to Slider constructor
	var Slider;


	(function( $ ) {

		'use strict';

		// -------------------------- utils -------------------------- //

		var slice = Array.prototype.slice;

		function noop() {}

		// -------------------------- definition -------------------------- //

		function defineBridget( $ ) {

			// bail if no jQuery
			if ( !$ ) {
				return;
			}

			// -------------------------- addOptionMethod -------------------------- //

			/**
			 * adds option method -> $().plugin('option', {...})
			 * @param {Function} PluginClass - constructor class
			 */
			function addOptionMethod( PluginClass ) {
				// don't overwrite original option method
				if ( PluginClass.prototype.option ) {
					return;
				}

			  // option setter
			  PluginClass.prototype.option = function( opts ) {
			    // bail out if not an object
			    if ( !$.isPlainObject( opts ) ){
			      return;
			    }
			    this.options = $.extend( true, this.options, opts );
			  };
			}


			// -------------------------- plugin bridge -------------------------- //

			// helper function for logging errors
			// $.error breaks jQuery chaining
			var logError = typeof console === 'undefined' ? noop :
			  function( message ) {
			    console.error( message );
			  };

			/**
			 * jQuery plugin bridge, access methods like $elem.plugin('method')
			 * @param {String} namespace - plugin name
			 * @param {Function} PluginClass - constructor class
			 */
			function bridge( namespace, PluginClass ) {
			  // add to jQuery fn namespace
			  $.fn[ namespace ] = function( options ) {
			    if ( typeof options === 'string' ) {
			      // call plugin method when first argument is a string
			      // get arguments for method
			      var args = slice.call( arguments, 1 );

			      for ( var i=0, len = this.length; i < len; i++ ) {
			        var elem = this[i];
			        var instance = $.data( elem, namespace );
			        if ( !instance ) {
			          logError( "cannot call methods on " + namespace + " prior to initialization; " +
			            "attempted to call '" + options + "'" );
			          continue;
			        }
			        if ( !$.isFunction( instance[options] ) || options.charAt(0) === '_' ) {
			          logError( "no such method '" + options + "' for " + namespace + " instance" );
			          continue;
			        }

			        // trigger method with arguments
			        var returnValue = instance[ options ].apply( instance, args);

			        // break look and return first value if provided
			        if ( returnValue !== undefined && returnValue !== instance) {
			          return returnValue;
			        }
			      }
			      // return this if no return value
			      return this;
			    } else {
			      var objects = this.map( function() {
			        var instance = $.data( this, namespace );
			        if ( instance ) {
			          // apply options & init
			          instance.option( options );
			          instance._init();
			        } else {
			          // initialize new instance
			          instance = new PluginClass( this, options );
			          $.data( this, namespace, instance );
			        }
			        return $(this);
			      });

			      if(!objects || objects.length > 1) {
			      	return objects;
			      } else {
			      	return objects[0];
			      }
			    }
			  };

			}

			// -------------------------- bridget -------------------------- //

			/**
			 * converts a Prototypical class into a proper jQuery plugin
			 *   the class must have a ._init method
			 * @param {String} namespace - plugin name, used in $().pluginName
			 * @param {Function} PluginClass - constructor class
			 */
			$.bridget = function( namespace, PluginClass ) {
			  addOptionMethod( PluginClass );
			  bridge( namespace, PluginClass );
			};

			return $.bridget;

		}

	  	// get jquery from browser global
	  	defineBridget( $ );

	})( $ );


	/*************************************************

			BOOTSTRAP-SLIDER SOURCE CODE

	**************************************************/

	(function($) {

		var ErrorMsgs = {
			formatInvalidInputErrorMsg : function(input) {
				return "Invalid input value '" + input + "' passed in";
			},
			callingContextNotSliderInstance : "Calling context element does not have instance of Slider bound to it. Check your code to make sure the JQuery object returned from the call to the slider() initializer is calling the method"
		};

		var SliderScale = {
			linear: {
				toValue: function(percentage) {
					var rawValue = percentage/100 * (this.options.max - this.options.min);
					var shouldAdjustWithBase = true;
					if (this.options.ticks_positions.length > 0) {
						var minv, maxv, minp, maxp = 0;
						for (var i = 1; i < this.options.ticks_positions.length; i++) {
							if (percentage <= this.options.ticks_positions[i]) {
								minv = this.options.ticks[i-1];
								minp = this.options.ticks_positions[i-1];
								maxv = this.options.ticks[i];
								maxp = this.options.ticks_positions[i];

								break;
							}
						}
						var partialPercentage = (percentage - minp) / (maxp - minp);
						rawValue = minv + partialPercentage * (maxv - minv);
						shouldAdjustWithBase = false;
					}

					var adjustment = shouldAdjustWithBase ? this.options.min : 0;
					var value = adjustment + Math.round(rawValue / this.options.step) * this.options.step;
					if (value < this.options.min) {
						return this.options.min;
					} else if (value > this.options.max) {
						return this.options.max;
					} else {
						return value;
					}
				},
				toPercentage: function(value) {
					if (this.options.max === this.options.min) {
						return 0;
					}

					if (this.options.ticks_positions.length > 0) {
						var minv, maxv, minp, maxp = 0;
						for (var i = 0; i < this.options.ticks.length; i++) {
							if (value  <= this.options.ticks[i]) {
								minv = (i > 0) ? this.options.ticks[i-1] : 0;
								minp = (i > 0) ? this.options.ticks_positions[i-1] : 0;
								maxv = this.options.ticks[i];
								maxp = this.options.ticks_positions[i];

								break;
							}
						}
						if (i > 0) {
							var partialPercentage = (value - minv) / (maxv - minv);
							return minp + partialPercentage * (maxp - minp);
						}
					}

					return 100 * (value - this.options.min) / (this.options.max - this.options.min);
				}
			},

			logarithmic: {
				/* Based on http://stackoverflow.com/questions/846221/logarithmic-slider */
				toValue: function(percentage) {
					var min = (this.options.min === 0) ? 0 : Math.log(this.options.min);
					var max = Math.log(this.options.max);
					var value = Math.exp(min + (max - min) * percentage / 100);
					value = this.options.min + Math.round((value - this.options.min) / this.options.step) * this.options.step;
					/* Rounding to the nearest step could exceed the min or
					 * max, so clip to those values. */
					if (value < this.options.min) {
						return this.options.min;
					} else if (value > this.options.max) {
						return this.options.max;
					} else {
						return value;
					}
				},
				toPercentage: function(value) {
					if (this.options.max === this.options.min) {
						return 0;
					} else {
						var max = Math.log(this.options.max);
						var min = this.options.min === 0 ? 0 : Math.log(this.options.min);
						var v = value === 0 ? 0 : Math.log(value);
						return 100 * (v - min) / (max - min);
					}
				}
			}
		};


		/*************************************************

							CONSTRUCTOR

		**************************************************/
		Slider = function(element, options) {
			createNewSlider.call(this, element, options);
			return this;
		};

		function createNewSlider(element, options) {

			/*
				The internal state object is used to store data about the current 'state' of slider.
				This includes values such as the `value`, `enabled`, etc...
			*/
			this._state = {
				value: null,
				enabled: null,
				offset: null,
				size: null,
				percentage: null,
				inDrag: false,
				over: false
			};

			// The objects used to store the reference to the tick methods if ticks_tooltip is on
			this.ticksCallbackMap = {};
			this.handleCallbackMap = {};

			if(typeof element === "string") {
				this.element = document.querySelector(element);
			} else if(element instanceof HTMLElement) {
				this.element = element;
			}

			/*************************************************

							Process Options

			**************************************************/
			options = options ? options : {};
			var optionTypes = Object.keys(this.defaultOptions);

			for(var i = 0; i < optionTypes.length; i++) {
				var optName = optionTypes[i];

				// First check if an option was passed in via the constructor
				var val = options[optName];
				// If no data attrib, then check data atrributes
				val = (typeof val !== 'undefined') ? val : getDataAttrib(this.element, optName);
				// Finally, if nothing was specified, use the defaults
				val = (val !== null) ? val : this.defaultOptions[optName];

				// Set all options on the instance of the Slider
				if(!this.options) {
					this.options = {};
				}
				this.options[optName] = val;
			}

			// Check options.rtl
			if(this.options.rtl==='auto'){
				this.options.rtl = window.getComputedStyle(this.element).direction==='rtl';
			}

			/*
				Validate `tooltip_position` against 'orientation`
				- if `tooltip_position` is incompatible with orientation, swith it to a default compatible with specified `orientation`
					-- default for "vertical" -> "right", "left" if rtl
					-- default for "horizontal" -> "top"
			*/
			if(this.options.orientation === "vertical" && (this.options.tooltip_position === "top" || this.options.tooltip_position === "bottom")) {
				if(this.options.rtl) {
					this.options.tooltip_position = "left";
				}else{
					this.options.tooltip_position = "right";
				}
			}
			else if(this.options.orientation === "horizontal" && (this.options.tooltip_position === "left" || this.options.tooltip_position === "right")) {

				this.options.tooltip_position	= "top";

			}

			function getDataAttrib(element, optName) {
				var dataName = "data-slider-" + optName.replace(/_/g, '-');
				var dataValString = element.getAttribute(dataName);

				try {
					return JSON.parse(dataValString);
				}
				catch(err) {
					return dataValString;
				}
			}

			/*************************************************

							Create Markup

			**************************************************/

			var origWidth = this.element.style.width;
			var updateSlider = false;
			var parent = this.element.parentNode;
			var sliderTrackSelection;
			var sliderTrackLow, sliderTrackHigh;
			var sliderMinHandle;
			var sliderMaxHandle;

			if (this.sliderElem) {
				updateSlider = true;
			} else {
				/* Create elements needed for slider */
				this.sliderElem = document.createElement("div");
				this.sliderElem.className = "slider";

				/* Create slider track elements */
				var sliderTrack = document.createElement("div");
				sliderTrack.className = "slider-track";

				sliderTrackLow = document.createElement("div");
				sliderTrackLow.className = "slider-track-low";

				sliderTrackSelection = document.createElement("div");
				sliderTrackSelection.className = "slider-selection";

				sliderTrackHigh = document.createElement("div");
				sliderTrackHigh.className = "slider-track-high";

				sliderMinHandle = document.createElement("div");
				sliderMinHandle.className = "slider-handle min-slider-handle";
				sliderMinHandle.setAttribute('role', 'slider');
				sliderMinHandle.setAttribute('aria-valuemin', this.options.min);
				sliderMinHandle.setAttribute('aria-valuemax', this.options.max);
				
				pre_sliderMinHandle = document.createElement("span");
				pre_sliderMinHandle.className = "age_min";
				pre_sliderMinHandle.id = "labelAgeMin";
				//pre_sliderMinHandle.innerHTML = "18";
				sliderMinHandle.appendChild(pre_sliderMinHandle);
				

				sliderMaxHandle = document.createElement("div");
				sliderMaxHandle.className = "slider-handle max-slider-handle";
				sliderMaxHandle.setAttribute('role', 'slider');
				sliderMaxHandle.setAttribute('aria-valuemin', this.options.min);
				sliderMaxHandle.setAttribute('aria-valuemax', this.options.max);
				
				pre_sliderMaxHandle = document.createElement("span");
				pre_sliderMaxHandle.className = "age_max";
				pre_sliderMaxHandle.id = "labelAgeMax";
				//pre_sliderMaxHandle.innerHTML = "99";
				sliderMaxHandle.appendChild(pre_sliderMaxHandle);
				
				sliderTrack.appendChild(sliderTrackLow);
				sliderTrack.appendChild(sliderTrackSelection);
				sliderTrack.appendChild(sliderTrackHigh);

				/* Create highlight range elements */
				this.rangeHighlightElements = [];
				if (Array.isArray(this.options.rangeHighlights) && this.options.rangeHighlights.length > 0) {
					for (let j = 0; j < this.options.rangeHighlights.length; j++) {

						var rangeHighlightElement = document.createElement("div");
						rangeHighlightElement.className = "slider-rangeHighlight slider-selection";

						this.rangeHighlightElements.push(rangeHighlightElement);
						sliderTrack.appendChild(rangeHighlightElement);
					}
				}

				/* Add aria-labelledby to handle's */
				var isLabelledbyArray = Array.isArray(this.options.labelledby);
				if (isLabelledbyArray && this.options.labelledby[0]) {
					sliderMinHandle.setAttribute('aria-labelledby', this.options.labelledby[0]);
				}
				if (isLabelledbyArray && this.options.labelledby[1]) {
					sliderMaxHandle.setAttribute('aria-labelledby', this.options.labelledby[1]);
				}
				if (!isLabelledbyArray && this.options.labelledby) {
					sliderMinHandle.setAttribute('aria-labelledby', this.options.labelledby);
					sliderMaxHandle.setAttribute('aria-labelledby', this.options.labelledby);
				}

				/* Create ticks */
				this.ticks = [];
				if (Array.isArray(this.options.ticks) && this.options.ticks.length > 0) {
					this.ticksContainer = document.createElement('div');
					this.ticksContainer.className = 'slider-tick-container';

					for (i = 0; i < this.options.ticks.length; i++) {
						var tick = document.createElement('div');
						tick.className = 'slider-tick';
						if (this.options.ticks_tooltip) {
							var tickListenerReference = this._addTickListener();
							var enterCallback = tickListenerReference.addMouseEnter(this, tick, i);
							var leaveCallback = tickListenerReference.addMouseLeave(this, tick);

							this.ticksCallbackMap[i] = {
								mouseEnter: enterCallback,
								mouseLeave: leaveCallback
							};
						}
						this.ticks.push(tick);
						this.ticksContainer.appendChild(tick);
					}

					sliderTrackSelection.className += " tick-slider-selection";
				}

				this.tickLabels = [];
				if (Array.isArray(this.options.ticks_labels) && this.options.ticks_labels.length > 0) {
					this.tickLabelContainer = document.createElement('div');
					this.tickLabelContainer.className = 'slider-tick-label-container';

					for (i = 0; i < this.options.ticks_labels.length; i++) {
						var label = document.createElement('div');
						var noTickPositionsSpecified = this.options.ticks_positions.length === 0;
						var tickLabelsIndex = (this.options.reversed && noTickPositionsSpecified) ? (this.options.ticks_labels.length - (i + 1)) : i;
						label.className = 'slider-tick-label';
						label.innerHTML = this.options.ticks_labels[tickLabelsIndex];

						this.tickLabels.push(label);
						this.tickLabelContainer.appendChild(label);
					}
				}

				const createAndAppendTooltipSubElements = function(tooltipElem) {
					var arrow = document.createElement("div");
					arrow.className = "tooltip-arrow";

					var inner = document.createElement("div");
					inner.className = "tooltip-inner";

					tooltipElem.appendChild(arrow);
					tooltipElem.appendChild(inner);
				};

				/* Create tooltip elements */
				const sliderTooltip = document.createElement("div");
				sliderTooltip.className = "tooltip tooltip-main";
				sliderTooltip.setAttribute('role', 'presentation');
				createAndAppendTooltipSubElements(sliderTooltip);

				const sliderTooltipMin = document.createElement("div");
				sliderTooltipMin.className = "tooltip tooltip-min";
				sliderTooltipMin.setAttribute('role', 'presentation');
				createAndAppendTooltipSubElements(sliderTooltipMin);

				const sliderTooltipMax = document.createElement("div");
				sliderTooltipMax.className = "tooltip tooltip-max";
				sliderTooltipMax.setAttribute('role', 'presentation');
				createAndAppendTooltipSubElements(sliderTooltipMax);

				/* Append components to sliderElem */
				this.sliderElem.appendChild(sliderTrack);
				this.sliderElem.appendChild(sliderTooltip);
				this.sliderElem.appendChild(sliderTooltipMin);
				this.sliderElem.appendChild(sliderTooltipMax);

				if (this.tickLabelContainer) {
					this.sliderElem.appendChild(this.tickLabelContainer);
				}
				if (this.ticksContainer) {
					this.sliderElem.appendChild(this.ticksContainer);
				}

				this.sliderElem.appendChild(sliderMinHandle);
				this.sliderElem.appendChild(sliderMaxHandle);

				/* Append slider element to parent container, right before the original <input> element */
				parent.insertBefore(this.sliderElem, this.element);

				/* Hide original <input> element */
				this.element.style.display = "none";
			}
			/* If JQuery exists, cache JQ references */
			if($) {
				this.$element = $(this.element);
				this.$sliderElem = $(this.sliderElem);
			}

			/*************************************************

								Setup

			**************************************************/
			this.eventToCallbackMap = {};
			this.sliderElem.id = this.options.id;

			this.touchCapable = 'ontouchstart' in window || (window.DocumentTouch && document instanceof window.DocumentTouch);

			this.touchX = 0;
			this.touchY = 0;

			this.tooltip = this.sliderElem.querySelector('.tooltip-main');
			this.tooltipInner = this.tooltip.querySelector('.tooltip-inner');

			this.tooltip_min = this.sliderElem.querySelector('.tooltip-min');
			this.tooltipInner_min = this.tooltip_min.querySelector('.tooltip-inner');

			this.tooltip_max = this.sliderElem.querySelector('.tooltip-max');
			this.tooltipInner_max= this.tooltip_max.querySelector('.tooltip-inner');

			if (SliderScale[this.options.scale]) {
				this.options.scale = SliderScale[this.options.scale];
			}

			if (updateSlider === true) {
				// Reset classes
				this._removeClass(this.sliderElem, 'slider-horizontal');
				this._removeClass(this.sliderElem, 'slider-vertical');
				this._removeClass(this.sliderElem, 'slider-rtl');
				this._removeClass(this.tooltip, 'hide');
				this._removeClass(this.tooltip_min, 'hide');
				this._removeClass(this.tooltip_max, 'hide');

				// Undo existing inline styles for track
				["left", "right", "top", "width", "height"].forEach(function(prop) {
					this._removeProperty(this.trackLow, prop);
					this._removeProperty(this.trackSelection, prop);
					this._removeProperty(this.trackHigh, prop);
				}, this);

				// Undo inline styles on handles
				[this.handle1, this.handle2].forEach(function(handle) {
					this._removeProperty(handle, 'left');
					this._removeProperty(handle, 'right');
					this._removeProperty(handle, 'top');
				}, this);

				// Undo inline styles and classes on tooltips
				[this.tooltip, this.tooltip_min, this.tooltip_max].forEach(function(tooltip) {
					this._removeProperty(tooltip, 'left');
					this._removeProperty(tooltip, 'right');
					this._removeProperty(tooltip, 'top');
					this._removeProperty(tooltip, 'margin-left');
					this._removeProperty(tooltip, 'margin-right');
					this._removeProperty(tooltip, 'margin-top');

					this._removeClass(tooltip, 'right');
					this._removeClass(tooltip, 'left');
					this._removeClass(tooltip, 'top');
				}, this);
			}

			if(this.options.orientation === 'vertical') {
				this._addClass(this.sliderElem,'slider-vertical');
				this.stylePos = 'top';
				this.mousePos = 'pageY';
				this.sizePos = 'offsetHeight';
			} else {
				this._addClass(this.sliderElem, 'slider-horizontal');
				this.sliderElem.style.width = origWidth;
				this.options.orientation = 'horizontal';
				if(this.options.rtl) {
					this.stylePos = 'right';
				} else {
					this.stylePos = 'left';
				}
				this.mousePos = 'pageX';
				this.sizePos = 'offsetWidth';
			}
			// specific rtl class
			if (this.options.rtl) {
				this._addClass(this.sliderElem, 'slider-rtl');
			}
			this._setTooltipPosition();
			/* In case ticks are specified, overwrite the min and max bounds */
			if (Array.isArray(this.options.ticks) && this.options.ticks.length > 0) {
					this.options.max = Math.max.apply(Math, this.options.ticks);
					this.options.min = Math.min.apply(Math, this.options.ticks);
			}

			if (Array.isArray(this.options.value)) {
				this.options.range = true;
				this._state.value = this.options.value;
			}
			else if (this.options.range) {
				// User wants a range, but value is not an array
				this._state.value = [this.options.value, this.options.max];
			}
			else {
				this._state.value = this.options.value;
			}

			this.trackLow = sliderTrackLow || this.trackLow;
			this.trackSelection = sliderTrackSelection || this.trackSelection;
			this.trackHigh = sliderTrackHigh || this.trackHigh;

			if (this.options.selection === 'none') {
				this._addClass(this.trackLow, 'hide');
				this._addClass(this.trackSelection, 'hide');
				this._addClass(this.trackHigh, 'hide');
			}

			else if (this.options.selection === 'after' || this.options.selection === 'before') {
				this._removeClass(this.trackLow, 'hide');
				this._removeClass(this.trackSelection, 'hide');
				this._removeClass(this.trackHigh, 'hide');
			}

			this.handle1 = sliderMinHandle || this.handle1;
			this.handle2 = sliderMaxHandle || this.handle2;

			if (updateSlider === true) {
				// Reset classes
				this._removeClass(this.handle1, 'round triangle');
				this._removeClass(this.handle2, 'round triangle hide');

				for (i = 0; i < this.ticks.length; i++) {
					this._removeClass(this.ticks[i], 'round triangle hide');
				}
			}

			var availableHandleModifiers = ['round', 'triangle', 'custom'];
			var isValidHandleType = availableHandleModifiers.indexOf(this.options.handle) !== -1;
			if (isValidHandleType) {
				this._addClass(this.handle1, this.options.handle);
				this._addClass(this.handle2, this.options.handle);

				for (i = 0; i < this.ticks.length; i++) {
					this._addClass(this.ticks[i], this.options.handle);
				}
			}

			this._state.offset = this._offset(this.sliderElem);
			this._state.size = this.sliderElem[this.sizePos];
			this.setValue(this._state.value);

			/******************************************

						Bind Event Listeners

			******************************************/

			// Bind keyboard handlers
			this.handle1Keydown = this._keydown.bind(this, 0);
			this.handle1.addEventListener("keydown", this.handle1Keydown, false);

			this.handle2Keydown = this._keydown.bind(this, 1);
			this.handle2.addEventListener("keydown", this.handle2Keydown, false);

			this.mousedown = this._mousedown.bind(this);
			this.touchstart = this._touchstart.bind(this);
			this.touchmove = this._touchmove.bind(this);

			if (this.touchCapable) {
				// Test for passive event support
				let supportsPassive = false;
				try {
					let opts = Object.defineProperty({}, 'passive', {
						get: function() {
							supportsPassive = true;
						}
					});
					window.addEventListener("test", null, opts);
				} catch (e) {}
				// Use our detect's results. passive applied if supported, capture will be false either way.
				let eventOptions = supportsPassive ? { passive: true } : false;
				// Bind touch handlers
				this.sliderElem.addEventListener("touchstart", this.touchstart, eventOptions);
				this.sliderElem.addEventListener("touchmove", this.touchmove, eventOptions);
			}
			this.sliderElem.addEventListener("mousedown", this.mousedown, false);

			// Bind window handlers
			this.resize = this._resize.bind(this);
			window.addEventListener("resize", this.resize, false);


			// Bind tooltip-related handlers
			if(this.options.tooltip === 'hide') {
				this._addClass(this.tooltip, 'hide');
				this._addClass(this.tooltip_min, 'hide');
				this._addClass(this.tooltip_max, 'hide');
			}
			else if(this.options.tooltip === 'always') {
				this._showTooltip();
				this._alwaysShowTooltip = true;
			}
			else {
				this.showTooltip = this._showTooltip.bind(this);
				this.hideTooltip = this._hideTooltip.bind(this);

				if (this.options.ticks_tooltip) {
					var callbackHandle = this._addTickListener();
					//create handle1 listeners and store references in map
					var mouseEnter = callbackHandle.addMouseEnter(this, this.handle1);
					var mouseLeave = callbackHandle.addMouseLeave(this, this.handle1);
					this.handleCallbackMap.handle1 = {
						mouseEnter: mouseEnter,
						mouseLeave: mouseLeave
					};
					//create handle2 listeners and store references in map
					mouseEnter = callbackHandle.addMouseEnter(this, this.handle2);
					mouseLeave = callbackHandle.addMouseLeave(this, this.handle2);
					this.handleCallbackMap.handle2 = {
						mouseEnter: mouseEnter,
						mouseLeave: mouseLeave
					};
				} else {
					this.sliderElem.addEventListener("mouseenter", this.showTooltip, false);
					this.sliderElem.addEventListener("mouseleave", this.hideTooltip, false);
				}

				this.handle1.addEventListener("focus", this.showTooltip, false);
				this.handle1.addEventListener("blur", this.hideTooltip, false);

				this.handle2.addEventListener("focus", this.showTooltip, false);
				this.handle2.addEventListener("blur", this.hideTooltip, false);
			}

			if(this.options.enabled) {
				this.enable();
			} else {
				this.disable();
			}

		}

		/*************************************************

					INSTANCE PROPERTIES/METHODS

		- Any methods bound to the prototype are considered
		part of the plugin's `public` interface

		**************************************************/
		Slider.prototype = {
			_init: function() {}, // NOTE: Must exist to support bridget

			constructor: Slider,

			defaultOptions: {
				id: "",
				min: 0,
				max: 10,
				step: 1,
				precision: 0,
				orientation: 'horizontal',
				value: 5,
				range: false,
				selection: 'before',
				tooltip: 'show',
				tooltip_split: false,
				handle: 'round',
				reversed: false,
				rtl: 'auto',
				enabled: true,
				formatter: function(val) {
					if (Array.isArray(val)) {
						return val[0] + " : " + val[1];
					} else {
						return val;
					}
				},
				natural_arrow_keys: false,
				ticks: [],
				ticks_positions: [],
				ticks_labels: [],
				ticks_snap_bounds: 0,
				ticks_tooltip: false,
				scale: 'linear',
				focus: false,
				tooltip_position: null,
				labelledby: null,
				rangeHighlights: []
			},

			getElement: function() {
				return this.sliderElem;
			},

			getValue: function() {
				if (this.options.range) {
					return this._state.value;
				}
				else {
					return this._state.value[0];
				}
			},

			setValue: function(val, triggerSlideEvent, triggerChangeEvent) {
				if (!val) {
					val = 0;
				}
				var oldValue = this.getValue();
				this._state.value = this._validateInputValue(val);
				var applyPrecision = this._applyPrecision.bind(this);

				if (this.options.range) {
					this._state.value[0] = applyPrecision(this._state.value[0]);
					this._state.value[1] = applyPrecision(this._state.value[1]);

					this._state.value[0] = Math.max(this.options.min, Math.min(this.options.max, this._state.value[0]));
					this._state.value[1] = Math.max(this.options.min, Math.min(this.options.max, this._state.value[1]));
				}
				else {
					this._state.value = applyPrecision(this._state.value);
					this._state.value = [ Math.max(this.options.min, Math.min(this.options.max, this._state.value))];
					this._addClass(this.handle2, 'hide');
					if (this.options.selection === 'after') {
						this._state.value[1] = this.options.max;
					} else {
						this._state.value[1] = this.options.min;
					}
				}

				if (this.options.max > this.options.min) {
					this._state.percentage = [
						this._toPercentage(this._state.value[0]),
						this._toPercentage(this._state.value[1]),
						this.options.step * 100 / (this.options.max - this.options.min)
					];
				} else {
					this._state.percentage = [0, 0, 100];
				}

				this._layout();
				var newValue = this.options.range ? this._state.value : this._state.value[0];

				this._setDataVal(newValue);
				if(triggerSlideEvent === true) {
					this._trigger('slide', newValue);
				}
				if( (oldValue !== newValue) && (triggerChangeEvent === true) ) {
					this._trigger('change', {
						oldValue: oldValue,
						newValue: newValue
					});
				}

				return this;
			},

			destroy: function(){
				// Remove event handlers on slider elements
				this._removeSliderEventHandlers();

				// Remove the slider from the DOM
				this.sliderElem.parentNode.removeChild(this.sliderElem);
				/* Show original <input> element */
				this.element.style.display = "";

				// Clear out custom event bindings
				this._cleanUpEventCallbacksMap();

				// Remove data values
				this.element.removeAttribute("data");

				// Remove JQuery handlers/data
				if($) {
					this._unbindJQueryEventHandlers();
					this.$element.removeData('slider');
				}
			},

			disable: function() {
				this._state.enabled = false;
				this.handle1.removeAttribute("tabindex");
				this.handle2.removeAttribute("tabindex");
				this._addClass(this.sliderElem, 'slider-disabled');
				this._trigger('slideDisabled');

				return this;
			},

			enable: function() {
				this._state.enabled = true;
				this.handle1.setAttribute("tabindex", 0);
				this.handle2.setAttribute("tabindex", 0);
				this._removeClass(this.sliderElem, 'slider-disabled');
				this._trigger('slideEnabled');

				return this;
			},

			toggle: function() {
				if(this._state.enabled) {
					this.disable();
				} else {
					this.enable();
				}
				return this;
			},

			isEnabled: function() {
				return this._state.enabled;
			},

			on: function(evt, callback) {
				this._bindNonQueryEventHandler(evt, callback);
				return this;
			},

			off: function(evt, callback) {
				if($) {
					this.$element.off(evt, callback);
					this.$sliderElem.off(evt, callback);
				} else {
					this._unbindNonQueryEventHandler(evt, callback);
				}
			},

			getAttribute: function(attribute) {
				if(attribute) {
					return this.options[attribute];
				} else {
					return this.options;
				}
			},

			setAttribute: function(attribute, value) {
				this.options[attribute] = value;
				return this;
			},

			refresh: function() {
				this._removeSliderEventHandlers();
				createNewSlider.call(this, this.element, this.options);
				if($) {
					// Bind new instance of slider to the element
					$.data(this.element, 'slider', this);
				}
				return this;
			},

			relayout: function() {
				this._resize();
				this._layout();
				return this;
			},

			/******************************+

						HELPERS

			- Any method that is not part of the public interface.
			- Place it underneath this comment block and write its signature like so:

				_fnName : function() {...}

			********************************/
			_removeSliderEventHandlers: function() {
				// Remove keydown event listeners
				this.handle1.removeEventListener("keydown", this.handle1Keydown, false);
				this.handle2.removeEventListener("keydown", this.handle2Keydown, false);

				//remove the listeners from the ticks and handles if they had their own listeners
				if (this.options.ticks_tooltip) {
					var ticks = this.ticksContainer.getElementsByClassName('slider-tick');
					for(var i = 0; i < ticks.length; i++ ){
						ticks[i].removeEventListener('mouseenter', this.ticksCallbackMap[i].mouseEnter, false);
						ticks[i].removeEventListener('mouseleave', this.ticksCallbackMap[i].mouseLeave, false);
					}
					this.handle1.removeEventListener('mouseenter', this.handleCallbackMap.handle1.mouseEnter, false);
					this.handle2.removeEventListener('mouseenter', this.handleCallbackMap.handle2.mouseEnter, false);
					this.handle1.removeEventListener('mouseleave', this.handleCallbackMap.handle1.mouseLeave, false);
					this.handle2.removeEventListener('mouseleave', this.handleCallbackMap.handle2.mouseLeave, false);
				}

				this.handleCallbackMap = null;
				this.ticksCallbackMap = null;

				if (this.showTooltip) {
					this.handle1.removeEventListener("focus", this.showTooltip, false);
					this.handle2.removeEventListener("focus", this.showTooltip, false);
				}
				if (this.hideTooltip) {
					this.handle1.removeEventListener("blur", this.hideTooltip, false);
					this.handle2.removeEventListener("blur", this.hideTooltip, false);
				}

				// Remove event listeners from sliderElem
				if (this.showTooltip) {
					this.sliderElem.removeEventListener("mouseenter", this.showTooltip, false);
				}
				if (this.hideTooltip) {
					this.sliderElem.removeEventListener("mouseleave", this.hideTooltip, false);
				}
				this.sliderElem.removeEventListener("touchstart", this.touchstart, false);
				this.sliderElem.removeEventListener("touchmove", this.touchmove, false);
				this.sliderElem.removeEventListener("mousedown", this.mousedown, false);

				// Remove window event listener
				window.removeEventListener("resize", this.resize, false);
			},
			_bindNonQueryEventHandler: function(evt, callback) {
				if(this.eventToCallbackMap[evt] === undefined) {
					this.eventToCallbackMap[evt] = [];
				}
				this.eventToCallbackMap[evt].push(callback);
			},
			_unbindNonQueryEventHandler: function(evt, callback) {
				var callbacks = this.eventToCallbackMap[evt];
				if(callbacks !== undefined) {
					for (var i = 0; i < callbacks.length; i++) {
						if (callbacks[i] === callback) {
							callbacks.splice(i, 1);
							break;
						}
					}
				}
			},
			_cleanUpEventCallbacksMap: function() {
				var eventNames = Object.keys(this.eventToCallbackMap);
				for(var i = 0; i < eventNames.length; i++) {
					var eventName = eventNames[i];
					delete this.eventToCallbackMap[eventName];
				}
			},
			_showTooltip: function() {
				if (this.options.tooltip_split === false ){
					this._addClass(this.tooltip, 'in');
					this.tooltip_min.style.display = 'none';
					this.tooltip_max.style.display = 'none';
			    } else {
					this._addClass(this.tooltip_min, 'in');
					this._addClass(this.tooltip_max, 'in');
					this.tooltip.style.display = 'none';
				}
				this._state.over = true;
			},
			_hideTooltip: function() {
				if (this._state.inDrag === false && this.alwaysShowTooltip !== true) {
					this._removeClass(this.tooltip, 'in');
					this._removeClass(this.tooltip_min, 'in');
					this._removeClass(this.tooltip_max, 'in');
				}
				this._state.over = false;
			},
			_setToolTipOnMouseOver: function _setToolTipOnMouseOver(tempState){
				var formattedTooltipVal = this.options.formatter(!tempState ? this._state.value[0]: tempState.value[0]);
				var positionPercentages = !tempState ? getPositionPercentages(this._state, this.options.reversed) : getPositionPercentages(tempState, this.options.reversed);
				this._setText(this.tooltipInner, formattedTooltipVal);

				this.tooltip.style[this.stylePos] = `${positionPercentages[0]}%`;
				if (this.options.orientation === 'vertical') {
					this._css(this.tooltip, `margin-${this.stylePos}`, `${-this.tooltip.offsetHeight / 2}px`);
				} else {
					this._css(this.tooltip, `margin-${this.stylePos}`, `${-this.tooltip.offsetWidth / 2}px`);
				}

				function getPositionPercentages(state, reversed){
					if (reversed) {
						return [100 - state.percentage[0], this.options.range ? 100 - state.percentage[1] : state.percentage[1]];
					}
					return [state.percentage[0], state.percentage[1]];
				}
			},
			_addTickListener: function _addTickListener() {
				return {
					addMouseEnter: function(reference, tick, index){
						var enter = function(){
							var tempState = reference._state;
							var idString = index >= 0 ? index : this.attributes['aria-valuenow'].value;
							var hoverIndex = parseInt(idString, 10);
							tempState.value[0] = hoverIndex;
							tempState.percentage[0] = reference.options.ticks_positions[hoverIndex];
							reference._setToolTipOnMouseOver(tempState);
							reference._showTooltip();
						};
						tick.addEventListener("mouseenter", enter, false);
						return enter;
					},
					addMouseLeave: function(reference, tick){
						var leave = function(){
							reference._hideTooltip();
						};
						tick.addEventListener("mouseleave", leave, false);
						return leave;
					}
				};
			},
			_layout: function() {
				var positionPercentages;

				if(this.options.reversed) {
					positionPercentages = [ 100 - this._state.percentage[0], this.options.range ? 100 - this._state.percentage[1] : this._state.percentage[1]];
				}
				else {
					positionPercentages = [ this._state.percentage[0], this._state.percentage[1] ];
				}

				this.handle1.style[this.stylePos] = `${positionPercentages[0]}%`;
				this.handle1.setAttribute('aria-valuenow', this._state.value[0]);
				if (isNaN(this.options.formatter(this._state.value[0])) ) {
					this.handle1.setAttribute('aria-valuetext', this.options.formatter(this._state.value[0]));
				}

				this.handle2.style[this.stylePos] =`${positionPercentages[1]}%`;
				this.handle2.setAttribute('aria-valuenow', this._state.value[1]);
				if (isNaN(this.options.formatter(this._state.value[1])) ) {
					this.handle2.setAttribute('aria-valuetext', this.options.formatter(this._state.value[1]));
				}

				/* Position highlight range elements */
				if (this.rangeHighlightElements.length > 0 && Array.isArray(this.options.rangeHighlights) && this.options.rangeHighlights.length > 0) {
					for (let i = 0; i < this.options.rangeHighlights.length; i++) {
						var startPercent = this._toPercentage(this.options.rangeHighlights[i].start);
						var endPercent = this._toPercentage(this.options.rangeHighlights[i].end);

						if (this.options.reversed) {
							var sp = 100-endPercent;
							endPercent = 100-startPercent;
							startPercent = sp;
						}

						var currentRange = this._createHighlightRange(startPercent, endPercent);

						if (currentRange) {
							if (this.options.orientation === 'vertical') {
								this.rangeHighlightElements[i].style.top = `${currentRange.start}%`;
								this.rangeHighlightElements[i].style.height = `${currentRange.size}%`;
							} else {
								if(this.options.rtl){
									this.rangeHighlightElements[i].style.right = `${currentRange.start}%`;
								} else {
									this.rangeHighlightElements[i].style.left = `${currentRange.start}%`;
								}
								this.rangeHighlightElements[i].style.width = `${currentRange.size}%`;
							}
						} else {
							this.rangeHighlightElements[i].style.display = "none";
						}
					}
				}

				/* Position ticks and labels */
				if (Array.isArray(this.options.ticks) && this.options.ticks.length > 0) {

					var styleSize = this.options.orientation === 'vertical' ? 'height' : 'width';
					var styleMargin;
					if( this.options.orientation === 'vertical' ){
						styleMargin='marginTop';
					}else {
						if( this.options.rtl ){
							styleMargin='marginRight';
						} else {
							styleMargin='marginLeft';
						}
					}
					var labelSize = this._state.size / (this.options.ticks.length - 1);

					if (this.tickLabelContainer) {
						var extraMargin = 0;
						if (this.options.ticks_positions.length === 0) {
							if (this.options.orientation !== 'vertical') {
								this.tickLabelContainer.style[styleMargin] = `${ -labelSize/2 }px`;
							}

							extraMargin = this.tickLabelContainer.offsetHeight;
						} else {
							/* Chidren are position absolute, calculate height by finding the max offsetHeight of a child */
							for (i = 0 ; i < this.tickLabelContainer.childNodes.length; i++) {
								if (this.tickLabelContainer.childNodes[i].offsetHeight > extraMargin) {
									extraMargin = this.tickLabelContainer.childNodes[i].offsetHeight;
								}
							}
						}
						if (this.options.orientation === 'horizontal') {
							this.sliderElem.style.marginBottom = `${ extraMargin }px`;
						}
					}
					for (var i = 0; i < this.options.ticks.length; i++) {

						var percentage = this.options.ticks_positions[i] || this._toPercentage(this.options.ticks[i]);

						if (this.options.reversed) {
							percentage = 100 - percentage;
						}

						this.ticks[i].style[this.stylePos] = `${ percentage }%`;

						/* Set class labels to denote whether ticks are in the selection */
						this._removeClass(this.ticks[i], 'in-selection');
						if (!this.options.range) {
							if (this.options.selection === 'after' && percentage >= positionPercentages[0]){
								this._addClass(this.ticks[i], 'in-selection');
							} else if (this.options.selection === 'before' && percentage <= positionPercentages[0]) {
								this._addClass(this.ticks[i], 'in-selection');
							}
						} else if (percentage >= positionPercentages[0] && percentage <= positionPercentages[1]) {
							this._addClass(this.ticks[i], 'in-selection');
						}

						if (this.tickLabels[i]) {
							this.tickLabels[i].style[styleSize] = `${labelSize}px`;

							if (this.options.orientation !== 'vertical' && this.options.ticks_positions[i] !== undefined) {
								this.tickLabels[i].style.position = 'absolute';
								this.tickLabels[i].style[this.stylePos] = `${percentage}%`;
								this.tickLabels[i].style[styleMargin] = -labelSize/2 + 'px';
							} else if (this.options.orientation === 'vertical') {
								if(this.options.rtl){
									this.tickLabels[i].style['marginRight'] = `${this.sliderElem.offsetWidth }px`;
								}else{
									this.tickLabels[i].style['marginLeft'] = `${this.sliderElem.offsetWidth }px`;
								}
								this.tickLabelContainer.style[styleMargin] = this.sliderElem.offsetWidth / 2 * -1 + 'px';
							}
						}
					}
				}

				var formattedTooltipVal;

				if (this.options.range) {
					formattedTooltipVal = this.options.formatter(this._state.value);
					this._setText(this.tooltipInner, formattedTooltipVal);
					this.tooltip.style[this.stylePos] = `${ (positionPercentages[1] + positionPercentages[0])/2 }%`;

					if (this.options.orientation === 'vertical') {
						this._css(this.tooltip, `margin-${this.stylePos}`, `${ -this.tooltip.offsetHeight / 2 }px`);
					} else {
						this._css(this.tooltip, `margin-${this.stylePos}`, `${ -this.tooltip.offsetWidth / 2 }px`);
					}

					var innerTooltipMinText = this.options.formatter(this._state.value[0]);
					this._setText(this.tooltipInner_min, innerTooltipMinText);

					var innerTooltipMaxText = this.options.formatter(this._state.value[1]);
					this._setText(this.tooltipInner_max, innerTooltipMaxText);

					this.tooltip_min.style[this.stylePos] = `${ positionPercentages[0] }%`;

					if (this.options.orientation === 'vertical') {
						this._css(this.tooltip_min, `margin-${this.stylePos}`, `${ -this.tooltip_min.offsetHeight / 2  }px`);
					} else {
						this._css(this.tooltip_min, `margin-${this.stylePos}`, `${ -this.tooltip_min.offsetWidth / 2  }px`);
					}

					this.tooltip_max.style[this.stylePos] = `${ positionPercentages[1] }%`;

					if (this.options.orientation === 'vertical') {
						this._css(this.tooltip_max, `margin-${this.stylePos}`, `${ -this.tooltip_max.offsetHeight / 2 }px`);
					} else {
						this._css(this.tooltip_max, `margin-${this.stylePos}`, `${ -this.tooltip_max.offsetWidth / 2 }px`);
					}
				} else {
					formattedTooltipVal = this.options.formatter(this._state.value[0]);
					this._setText(this.tooltipInner, formattedTooltipVal);

					this.tooltip.style[this.stylePos] = `${ positionPercentages[0] }%`;
					if (this.options.orientation === 'vertical') {
						this._css(this.tooltip, `margin-${this.stylePos}`, `${ -this.tooltip.offsetHeight / 2 }px`);
					} else {
						this._css(this.tooltip, `margin-${this.stylePos}`, `${ -this.tooltip.offsetWidth / 2 }px`);
					}
				}

				if (this.options.orientation === 'vertical') {
					this.trackLow.style.top = '0';
					this.trackLow.style.height = Math.min(positionPercentages[0], positionPercentages[1]) +'%';

					this.trackSelection.style.top = Math.min(positionPercentages[0], positionPercentages[1]) +'%';
					this.trackSelection.style.height = Math.abs(positionPercentages[0] - positionPercentages[1]) +'%';

					this.trackHigh.style.bottom = '0';
					this.trackHigh.style.height = (100 - Math.min(positionPercentages[0], positionPercentages[1]) - Math.abs(positionPercentages[0] - positionPercentages[1])) +'%';
				}
				else {
					if(this.stylePos==='right') {
						this.trackLow.style.right = '0';
					} else {
						this.trackLow.style.left = '0';
					}
					this.trackLow.style.width = Math.min(positionPercentages[0], positionPercentages[1]) +'%';

					if(this.stylePos==='right') {
						this.trackSelection.style.right = Math.min(positionPercentages[0], positionPercentages[1]) + '%';
					} else {
						this.trackSelection.style.left = Math.min(positionPercentages[0], positionPercentages[1]) + '%';
					}
					this.trackSelection.style.width = Math.abs(positionPercentages[0] - positionPercentages[1]) +'%';

					if(this.stylePos==='right') {
						this.trackHigh.style.left = '0';
					} else {
						this.trackHigh.style.right = '0';
					}
					this.trackHigh.style.width = (100 - Math.min(positionPercentages[0], positionPercentages[1]) - Math.abs(positionPercentages[0] - positionPercentages[1])) +'%';

					var offset_min = this.tooltip_min.getBoundingClientRect();
					var offset_max = this.tooltip_max.getBoundingClientRect();

					if (this.options.tooltip_position === 'bottom') {
						if (offset_min.right > offset_max.left) {
							this._removeClass(this.tooltip_max, 'bottom');
							this._addClass(this.tooltip_max, 'top');
							this.tooltip_max.style.top = '';
							this.tooltip_max.style.bottom = 22 + 'px';
						} else {
							this._removeClass(this.tooltip_max, 'top');
							this._addClass(this.tooltip_max, 'bottom');
							this.tooltip_max.style.top = this.tooltip_min.style.top;
							this.tooltip_max.style.bottom = '';
						}
					} else {
						if (offset_min.right > offset_max.left) {
							this._removeClass(this.tooltip_max, 'top');
							this._addClass(this.tooltip_max, 'bottom');
							this.tooltip_max.style.top = 18 + 'px';
						} else {
							this._removeClass(this.tooltip_max, 'bottom');
							this._addClass(this.tooltip_max, 'top');
							this.tooltip_max.style.top = this.tooltip_min.style.top;
						}
					}
				}
			},
			_createHighlightRange: function (start, end) {
				if (this._isHighlightRange(start, end)) {
					if (start > end) {
						return {'start': end, 'size': start - end};
					}
					return {'start': start, 'size': end - start};
				}
				return null;
			},
			_isHighlightRange: function (start, end) {
				if (0 <= start && start <= 100 && 0 <= end && end <= 100) {
					return true;
				}
				else {
					return false;
				}
			},
			_resize: function (ev) {
				/*jshint unused:false*/
				this._state.offset = this._offset(this.sliderElem);
				this._state.size = this.sliderElem[this.sizePos];
				this._layout();
			},
			_removeProperty: function(element, prop) {
				if (element.style.removeProperty) {
				    element.style.removeProperty(prop);
				} else {
				    element.style.removeAttribute(prop);
				}
			},
			_mousedown: function(ev) {
				if(!this._state.enabled) {
					return false;
				}

				this._state.offset = this._offset(this.sliderElem);
				this._state.size = this.sliderElem[this.sizePos];

				var percentage = this._getPercentage(ev);

				if (this.options.range) {
					var diff1 = Math.abs(this._state.percentage[0] - percentage);
					var diff2 = Math.abs(this._state.percentage[1] - percentage);
					this._state.dragged = (diff1 < diff2) ? 0 : 1;
					this._adjustPercentageForRangeSliders(percentage);
				} else {
					this._state.dragged = 0;
				}

				this._state.percentage[this._state.dragged] = percentage;
				this._layout();

				if (this.touchCapable) {
					document.removeEventListener("touchmove", this.mousemove, false);
					document.removeEventListener("touchend", this.mouseup, false);
				}

				if(this.mousemove){
					document.removeEventListener("mousemove", this.mousemove, false);
				}
				if(this.mouseup){
					document.removeEventListener("mouseup", this.mouseup, false);
				}

				this.mousemove = this._mousemove.bind(this);
				this.mouseup = this._mouseup.bind(this);

				if (this.touchCapable) {
					// Touch: Bind touch events:
					document.addEventListener("touchmove", this.mousemove, false);
					document.addEventListener("touchend", this.mouseup, false);
				}
				// Bind mouse events:
				document.addEventListener("mousemove", this.mousemove, false);
				document.addEventListener("mouseup", this.mouseup, false);

				this._state.inDrag = true;
				var newValue = this._calculateValue();

				this._trigger('slideStart', newValue);

				this._setDataVal(newValue);
				this.setValue(newValue, false, true);

				ev.returnValue = false;

				if (this.options.focus) {
					this._triggerFocusOnHandle(this._state.dragged);
				}

				return true;
			},
			_touchstart: function(ev) {
				if (ev.changedTouches === undefined) {
					this._mousedown(ev);
					return;
				}

				var touch = ev.changedTouches[0];
				this.touchX = touch.pageX;
				this.touchY = touch.pageY;
			},
			_triggerFocusOnHandle: function(handleIdx) {
				if(handleIdx === 0) {
					this.handle1.focus();
				}
				if(handleIdx === 1) {
					this.handle2.focus();
				}
			},
			_keydown: function(handleIdx, ev) {
				if(!this._state.enabled) {
					return false;
				}

				var dir;
				switch (ev.keyCode) {
					case 37: // left
					case 40: // down
						dir = -1;
						break;
					case 39: // right
					case 38: // up
						dir = 1;
						break;
				}
				if (!dir) {
					return;
				}

				// use natural arrow keys instead of from min to max
				if (this.options.natural_arrow_keys) {
					var ifVerticalAndNotReversed = (this.options.orientation === 'vertical' && !this.options.reversed);
					var ifHorizontalAndReversed = (this.options.orientation === 'horizontal' && this.options.reversed); // @todo control with rtl

					if (ifVerticalAndNotReversed || ifHorizontalAndReversed) {
						dir = -dir;
					}
				}

				var val = this._state.value[handleIdx] + dir * this.options.step;
				if (this.options.range) {
					val = [ (!handleIdx) ? val : this._state.value[0],
						    ( handleIdx) ? val : this._state.value[1]];
				}

				this._trigger('slideStart', val);
				this._setDataVal(val);
				this.setValue(val, true, true);

				this._setDataVal(val);
				this._trigger('slideStop', val);
				this._layout();

				this._pauseEvent(ev);

				return false;
			},
			_pauseEvent: function(ev) {
				if(ev.stopPropagation) {
					ev.stopPropagation();
				}
				if(ev.preventDefault) {
					ev.preventDefault();
				}
				ev.cancelBubble=true;
				ev.returnValue=false;
			},
			_mousemove: function(ev) {
				if(!this._state.enabled) {
					return false;
				}

				var percentage = this._getPercentage(ev);
				this._adjustPercentageForRangeSliders(percentage);
				this._state.percentage[this._state.dragged] = percentage;
				this._layout();

				var val = this._calculateValue(true);
				this.setValue(val, true, true);

				return false;
			},
			_touchmove: function(ev) {
				if (ev.changedTouches === undefined) {
					return;
				}

				var touch = ev.changedTouches[0];

				var xDiff = touch.pageX - this.touchX;
				var yDiff = touch.pageY - this.touchY;

				if (!this._state.inDrag) {
					// Vertical Slider
					if (this.options.orientation === 'vertical' && (xDiff <= 5 && xDiff >= -5) && (yDiff >=15 || yDiff <= -15)) {
						this._mousedown(ev);
					}
					// Horizontal slider.
					else if ((yDiff <= 5 && yDiff >= -5) && (xDiff >= 15 || xDiff <= -15)) {
						this._mousedown(ev);
					}
				}
			},
			_adjustPercentageForRangeSliders: function(percentage) {
				if (this.options.range) {
					var precision = this._getNumDigitsAfterDecimalPlace(percentage);
					precision = precision ? precision - 1 : 0;
					var percentageWithAdjustedPrecision = this._applyToFixedAndParseFloat(percentage, precision);
					if (this._state.dragged === 0 && this._applyToFixedAndParseFloat(this._state.percentage[1], precision) < percentageWithAdjustedPrecision) {
						this._state.percentage[0] = this._state.percentage[1];
						this._state.dragged = 1;
					} else if (this._state.dragged === 1 && this._applyToFixedAndParseFloat(this._state.percentage[0], precision) > percentageWithAdjustedPrecision) {
						this._state.percentage[1] = this._state.percentage[0];
						this._state.dragged = 0;
					}
				}
			},
			_mouseup: function() {
				if(!this._state.enabled) {
					return false;
				}
				if (this.touchCapable) {
					// Touch: Unbind touch event handlers:
					document.removeEventListener("touchmove", this.mousemove, false);
					document.removeEventListener("touchend", this.mouseup, false);
				}
				// Unbind mouse event handlers:
				document.removeEventListener("mousemove", this.mousemove, false);
				document.removeEventListener("mouseup", this.mouseup, false);

				this._state.inDrag = false;
				if (this._state.over === false) {
					this._hideTooltip();
				}
				var val = this._calculateValue(true);

				this._layout();
				this._setDataVal(val);
				this._trigger('slideStop', val);

				return false;
			},
			_calculateValue: function(snapToClosestTick) {
				var val;
				if (this.options.range) {
					val = [this.options.min,this.options.max];
					if (this._state.percentage[0] !== 0){
						val[0] = this._toValue(this._state.percentage[0]);
						val[0] = this._applyPrecision(val[0]);
					}
					if (this._state.percentage[1] !== 100){
						val[1] = this._toValue(this._state.percentage[1]);
						val[1] = this._applyPrecision(val[1]);
					}
				} else {
					val = this._toValue(this._state.percentage[0]);
					val = parseFloat(val);
					val = this._applyPrecision(val);
				}

				if (snapToClosestTick) {
					var min = [val, Infinity];
					for (var i = 0; i < this.options.ticks.length; i++) {
						var diff = Math.abs(this.options.ticks[i] - val);
						if (diff <= min[1]) {
							min = [this.options.ticks[i], diff];
						}
					}
					if (min[1] <= this.options.ticks_snap_bounds) {
						return min[0];
					}
				}

				return val;
			},
			_applyPrecision: function(val) {
				var precision = this.options.precision || this._getNumDigitsAfterDecimalPlace(this.options.step);
				return this._applyToFixedAndParseFloat(val, precision);
			},
			_getNumDigitsAfterDecimalPlace: function(num) {
				var match = (''+num).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
				if (!match) { return 0; }
				return Math.max(0, (match[1] ? match[1].length : 0) - (match[2] ? +match[2] : 0));
			},
			_applyToFixedAndParseFloat: function(num, toFixedInput) {
				var truncatedNum = num.toFixed(toFixedInput);
				return parseFloat(truncatedNum);
			},
			/*
				Credits to Mike Samuel for the following method!
				Source: http://stackoverflow.com/questions/10454518/javascript-how-to-retrieve-the-number-of-decimals-of-a-string-number
			*/
			_getPercentage: function(ev) {
				if (this.touchCapable && (ev.type === 'touchstart' || ev.type === 'touchmove')) {
					ev = ev.touches[0];
				}

				var eventPosition = ev[this.mousePos];
				var sliderOffset = this._state.offset[this.stylePos];
				var distanceToSlide = eventPosition - sliderOffset;
				if(this.stylePos==='right') {
					distanceToSlide = -distanceToSlide;
				}
				// Calculate what percent of the length the slider handle has slid
				var percentage = (distanceToSlide / this._state.size) * 100;
				percentage = Math.round(percentage / this._state.percentage[2]) * this._state.percentage[2];
				if (this.options.reversed) {
					percentage = 100 - percentage;
				}

				// Make sure the percent is within the bounds of the slider.
				// 0% corresponds to the 'min' value of the slide
				// 100% corresponds to the 'max' value of the slide
				return Math.max(0, Math.min(100, percentage));
			},
			_validateInputValue: function(val) {
				if (!isNaN(+val)) {
					return +val;
				} else if (Array.isArray(val)) {
					this._validateArray(val);
					return val;
				} else {
					throw new Error(ErrorMsgs.formatInvalidInputErrorMsg(val));
				}
			},
			_validateArray: function(val) {
				for(var i = 0; i < val.length; i++) {
					var input =  val[i];
					if (typeof input !== 'number') { throw new Error( ErrorMsgs.formatInvalidInputErrorMsg(input) ); }
				}
			},
			_setDataVal: function(val) {
				this.element.setAttribute('data-value', val);
				this.element.setAttribute('value', val);
				this.element.value = val;
			},
			_trigger: function(evt, val) {
				val = (val || val === 0) ? val : undefined;

				var callbackFnArray = this.eventToCallbackMap[evt];
				if(callbackFnArray && callbackFnArray.length) {
					for(var i = 0; i < callbackFnArray.length; i++) {
						var callbackFn = callbackFnArray[i];
						callbackFn(val);
					}
				}

				/* If JQuery exists, trigger JQuery events */
				if($) {
					this._triggerJQueryEvent(evt, val);
				}
			},
			_triggerJQueryEvent: function(evt, val) {
				var eventData = {
					type: evt,
					value: val
				};
				this.$element.trigger(eventData);
				this.$sliderElem.trigger(eventData);
			},
			_unbindJQueryEventHandlers: function() {
				this.$element.off();
				this.$sliderElem.off();
			},
			_setText: function(element, text) {
				if(typeof element.textContent !== "undefined") {
					element.textContent = text;
				} else if(typeof element.innerText !== "undefined") {
					element.innerText = text;
				}
			},
			_removeClass: function(element, classString) {
				var classes = classString.split(" ");
				var newClasses = element.className;

				for(var i = 0; i < classes.length; i++) {
					var classTag = classes[i];
					var regex = new RegExp("(?:\\s|^)" + classTag + "(?:\\s|$)");
					newClasses = newClasses.replace(regex, " ");
				}

				element.className = newClasses.trim();
			},
			_addClass: function(element, classString) {
				var classes = classString.split(" ");
				var newClasses = element.className;

				for(var i = 0; i < classes.length; i++) {
					var classTag = classes[i];
					var regex = new RegExp("(?:\\s|^)" + classTag + "(?:\\s|$)");
					var ifClassExists = regex.test(newClasses);

					if(!ifClassExists) {
						newClasses += " " + classTag;
					}
				}

				element.className = newClasses.trim();
			},
			_offsetLeft: function(obj){
				return obj.getBoundingClientRect().left;
			},
			_offsetRight: function(obj){
				return obj.getBoundingClientRect().right;
			},
			_offsetTop: function(obj){
				var offsetTop = obj.offsetTop;
				while((obj = obj.offsetParent) && !isNaN(obj.offsetTop)){
					offsetTop += obj.offsetTop;
					if( obj.tagName !== 'BODY') {
						offsetTop -= obj.scrollTop;
					}
				}
				return offsetTop;
			},
			_offset: function (obj) {
				return {
					left: this._offsetLeft(obj),
					right: this._offsetRight(obj),
					top: this._offsetTop(obj)
				};
		    },
			_css: function(elementRef, styleName, value) {
				if ($) {
					$.style(elementRef, styleName, value);
				} else {
					var style = styleName.replace(/^-ms-/, "ms-").replace(/-([\da-z])/gi, function (all, letter) {
						return letter.toUpperCase();
					});
					elementRef.style[style] = value;
				}
			},
			_toValue: function(percentage) {
				return this.options.scale.toValue.apply(this, [percentage]);
			},
			_toPercentage: function(value) {
				return this.options.scale.toPercentage.apply(this, [value]);
			},
			_setTooltipPosition: function(){
				var tooltips = [this.tooltip, this.tooltip_min, this.tooltip_max];
				if (this.options.orientation === 'vertical'){
					var tooltipPos;
					if(this.options.tooltip_position) {
						tooltipPos = this.options.tooltip_position;
					} else {
						if(this.options.rtl) {
							tooltipPos = 'left';
						} else {
							tooltipPos = 'right';
						}
					}
					var oppositeSide = (tooltipPos === 'left') ? 'right' : 'left';
					tooltips.forEach(function(tooltip){
						this._addClass(tooltip, tooltipPos);
						tooltip.style[oppositeSide] = '100%';
					}.bind(this));
				} else if(this.options.tooltip_position === 'bottom') {
					tooltips.forEach(function(tooltip){
						this._addClass(tooltip, 'bottom');
						tooltip.style.top = 22 + 'px';
					}.bind(this));
				} else {
					tooltips.forEach(function(tooltip){
						this._addClass(tooltip, 'top');
						tooltip.style.top = -this.tooltip.outerHeight - 14 + 'px';
					}.bind(this));
				}
			}
		};

		/*********************************

			Attach to global namespace

		*********************************/
		if($) {
			let autoRegisterNamespace;

			if (!$.fn.slider) {
				$.bridget(NAMESPACE_MAIN, Slider);
				autoRegisterNamespace = NAMESPACE_MAIN;
			}
			else {
				if (windowIsDefined) {
					window.console.warn("bootstrap-slider.js - WARNING: $.fn.slider namespace is already bound. Use the $.fn.bootstrapSlider namespace instead.");
				}
				autoRegisterNamespace = NAMESPACE_ALTERNATE;
			}
			$.bridget(NAMESPACE_ALTERNATE, Slider);

			// Auto-Register data-provide="slider" Elements
			$(function() {
				$("input[data-provide=slider]")[autoRegisterNamespace]();
			});
		}

	})( $ );

	return Slider;
}));
//# sourceMappingURL=all.js.map


