/**
 * Open the login dialog box
 * @link http://www.alessioatzeni.com/blog/login-box-modal-dialog-window-with-css-and-jquery/
 */
function fnPluginTaskLogout() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Login - Log out');
	}
	/*<!-- endbuild -->*/

	$.post(
		"index.php", {
			"task": "task.login.logout"
		},
		function (data) {

			Noty({
				message: $.i18n('logged_out'),
				type: 'success'
			});

			location.reload();
		}
	);

	return true;
}

function fnPluginTaskShowForm() {

	//Fade in the Popup
	$('#login-box').fadeIn(300);
	$('#username').focus();

	//Set the center alignment padding + border see css style
	var popMargTop = ($('#login-box').height() + 24) / 2;
	var popMargLeft = ($('#login-box').width() + 24) / 2;

	$('#login-box').css({
		'margin-top': -popMargTop,
		'margin-left': -popMargLeft
	});

	// Add the mask to body
	$('body').append('<div id="mask"></div>');
	$('#mask').fadeIn(300);

	$('a.close, #mask').click(function () {
		$('#mask, .login-popup').fadeOut(300, function () {
			$('#mask').remove();
		});
	});

	$("#password").keyup(function (event) {
		if (event.keyCode == 13) {
			$("#login-box .submit").click();
		}
	});

	$('#login-box .submit').click(function () {
		var $login = $('#username').val();
		var $password = $('#password').val();

		if (($login === null) || ($login === '') || ($password === null) || ($password === '')) {
			Noty({
				message: $.i18n('login_error'),
				type: 'error'
			});

			$('#username').addClass("errorLogin");
			$('#password').addClass("errorLogin");
		} else {
			// Ok, try to connect
			$login = window.btoa(encodeURIComponent(JSON.stringify($login.trim())));
			$password = window.btoa(encodeURIComponent(JSON.stringify($password.trim())));

			$.post(
				"index.php", {
					"task": "task.login.login",
					"username": $login,
					"password": $password
				},
				function (data) {

					var $status = false;

					if (data.hasOwnProperty('status')) {
						$status = (data.status === 1 ? true : false);
					}

					if ($status) {
						$('#mask , .login-popup').fadeOut(300, function () {
							$('#mask').remove();
						});
						Noty({
							message: $.i18n('login_success'),
							type: 'success'
						});

						// Reload the page because html plugins can be different when the
						// user is logged or not (f.i. add extra features in the Treeview)
						// so a reload is needed
						location.reload();
					} else {
						Noty({
							message: $.i18n('login_error'),
							type: 'error'
						});
						$('#username').addClass("errorLogin");
						$('#password').addClass("errorLogin");
					}
				}
			);
		}

	});

}

function fnPluginTaskLogin() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Login - Log in');
	}
	/*<!-- endbuild -->*/

	$.ajax({
		beforeSend: function () {
			// Remove the form if already present
			if ($('#login-box').length) {
				$('#login-box').remove();
			}
		},
		type: "POST",
		url: "index.php",
		data: "task=task.login.getform",
		dataType: "json",
		success: function (data) {
			if (data.hasOwnProperty("form")) {
				// The result of the task 'task.login.getform' is a HTML
				// string : the login screen.
				// Add that form to the parent of the content DOM element
				$("#CONTENT").parent().append(data['form']);
				// And show the form.
				fnPluginTaskShowForm();
			} else {
				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.error('      Invalid JSON returned by the login.getform task');
				}
				/*<!-- endbuild -->*/

			}
		}
	});

	return false;

}
