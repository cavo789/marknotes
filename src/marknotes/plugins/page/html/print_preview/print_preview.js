/**
 * @link https://github.com/jasonday/printThis
 */
function fnPluginHTMLPrintPreview() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Print preview');
	}
	/*<!-- endbuild -->*/

	// See options https://github.com/jasonday/printThis
	if (marknotes.note.url == '') {
		// The user click on the Reveal button but should first select
		// a note in the treeview
		Noty({
			message: $.i18n('error_select_first'),
			type: 'error'
		});
	} else {
		$('article').printThis();
	}
	return true;

}
