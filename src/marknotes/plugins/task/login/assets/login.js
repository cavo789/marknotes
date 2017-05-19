/**
 * Open the login dialog box
 * @link http://www.alessioatzeni.com/blog/login-box-modal-dialog-window-with-css-and-jquery/
 */
function fnPluginTaskLogin() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('Showing the login form in fnPluginTaskLogin()');
	}
	/*<!-- endbuild -->*/

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
		$('#mask , .login-popup').fadeOut(300, function () {
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
				message: marknotes.message.incorrect_login,
				type: 'error'
			});

			$('#username').addClass("errorLogin");
			$('#password').addClass("errorLogin");

		} else {

			// Ok, try to connect
			$login = window.btoa(encodeURIComponent(JSON.stringify($login.trim())));
			$password = window.btoa(encodeURIComponent(JSON.stringify($password.trim())));

			$.post("index.php", {
					task: 'login',
					'username': $login,
					'password': $password
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
							message: marknotes.message.login_success,
							type: 'success'
						});
					} else {

						Noty({
							message: marknotes.message.incorrect_login,
							type: 'error'
						});
						$('#username').addClass("errorLogin");
						$('#password').addClass("errorLogin");

					}
				}
			);
		}

	});

	return false;
}
