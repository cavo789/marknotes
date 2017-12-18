marknotes.arrPluginsFct.push("fnPluginHTMLUpdate");

function fnPluginHTMLUpdate() {
	// Idea is to detect the current version and the version on
	// github and to change the color of the icon
	// so the user knows he can start an update
	//$('#icon_update').css('color','#ff8c4e');
	//document.getElementById('icon_update').title = $.i18n('update_newer');
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

	// Redirect to the install script
	Noty({
		message: $.i18n('update_redirect'),
		type: 'info'
	});

	try {
		ajaxify({
			task: 'task.update.run',
			callback: 'fnPluginTaskUpdateDoIt($data, data);'
		});
	} catch (err) {
		console.warn(err.message);
	}

	return true;
}

/**
 * $data is the parameters sent with the ajax request
 * data is the answer of the task.update.run call
 */
function fnPluginTaskUpdateDoIt($data, data) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('In fnPluginTaskUpdateDoIt function');
	}
	/*<!-- endbuild -->*/

	if (data.hasOwnProperty('status')) {
		if (parseInt(data.status) === 1) {
			location.href = 'install.php';
		}

	}


	return true;
}
