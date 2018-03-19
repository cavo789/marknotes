$(document).ready(function () {

	introJs.fn.oncomplete(function() {
		fnPluginHTMLIntroDone();
	});

	introJs().setOption('showProgress', true).start();
});

/**
 * This function is called when the user has see the wizard
 * until the last step and he has clicked on the "Done" button.
 *
 * This function will call an ajax request and will disable the
 * intro plugin so he will not be fired anymore
 */
function fnPluginHTMLIntroDone() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.error('fnPluginHTMLIntroDone - Disable Intro.js');
	}
	/*<!-- endbuild -->*/

	$.ajax({
		type: (marknotes.settings.debug ? 'GET' : 'POST'),
		url: "index.php",
		data: "task=task.introjs.disable",
		dataType: "json",
		success: function (data) {
			Noty({
				message: data.message,
				type: (data.status == 1 ? 'success' : 'error')
			});
		}
	});

	return true;
}
