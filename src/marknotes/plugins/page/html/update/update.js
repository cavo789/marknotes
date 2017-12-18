marknotes.arrPluginsFct.push("fnPluginHTMLUpdate");

function fnPluginHTMLUpdate() {
	// Idea is to detect the current version and the version on
	// github and to change the color of the icon
	// so the user knows he can start an update
	$('#icon_update').css('color','#ff8c4e');

	document.getElementById('icon_update').title = $.i18n('update_newer');
}

/**
 * Called when the user click on the update button
 */
function fnPluginTaskUpdate() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page Update');
	}
	/*<!-- endbuild -->*/

	$.post(
		"index.php", {
			"task": "task.update.run"
		}
	);

	return true;
}
