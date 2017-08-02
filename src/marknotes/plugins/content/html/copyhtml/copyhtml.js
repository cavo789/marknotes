/**
 * Copy the html's note content in the clipboard so, f.i., we can paste it then in an email
 */

function fnPluginButtonCopyHTML() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('Plugin html - CopyHTML');
	}
	/*<!-- endbuild -->*/

	// Initialize the Copy into the clipboard button, See https://clipboardjs.com/

	if (typeof Clipboard === 'function') {

		if (Clipboard.isSupported()) {
			console.info('copy html 1');
			var clipboard = new Clipboard('#icon_copyhtml');
			console.info('copy html 2');
			clipboard.on('success', function (e) {
				console.info('copy html 3');
				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.info('Action:', e.action);
					console.info('Text:', e.text);
					console.info('Trigger:', e.trigger);
				}
				/*<!-- endbuild -->*/

				Noty({
					message: marknotes.message.copy_html_done,
					type: 'success'
				});

				e.clearSelection();
			});

		} // if (Clipboard.isSupported())

	} else {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.error('Plugin html - CopyHTML - Clipboard not initialized / loaded');
		}
		/*<!-- endbuild -->*/
	}

	return true;
}
