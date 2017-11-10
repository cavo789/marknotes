/**
 * Called when the user click on the erase button, from the treeview area
 */
function fnPluginTaskListFilesKillExport() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page Erase');
	}
	/*<!-- endbuild -->*/

	$.post(
		"index.php", {
			"task": "task.listfiles.erase"
		},
		function (data) {

			Noty({
				message: data.message,
				type: (data.status == '1' ? 'success' : 'warning')
			});

		}
	);

	return true;
}
