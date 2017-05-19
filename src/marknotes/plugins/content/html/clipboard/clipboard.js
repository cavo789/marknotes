/**
 * Copy the note content in the clipboard so, f.i., we can paste it then in an email
 * Copy html tags so layout is copied too
 */

function fnPluginButtonClipboard() {

	// Initialize the Copy into the clipboard button, See https://clipboardjs.com/

	if (typeof Clipboard === 'function') {

		if (Clipboard.isSupported()) {

			var clipboard = new Clipboard('#icon_clipboard');

			clipboard.on('success', function (e) {

				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.info('Action:', e.action);
					console.info('Text:', e.text);
					console.info('Trigger:', e.trigger);
				}
				/*<!-- endbuild -->*/

				Noty({
					message: marknotes.message.copy_clipboard_done,
					type: 'success'
				});

				e.clearSelection();
			});

			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				clipboard.on('error', function (e) {
					console.error('Action:', e.action);
					console.error('Trigger:', e.trigger);
				});
			}
			/*<!-- endbuild -->*/
		} // if (Clipboard.isSupported())
	}

	return true;
}

/**
 * Copy the URL to the displayed note in the clipboard
 */
function fnPluginButtonClipboardLinkNote() {

	if (typeof Clipboard === 'function') {

		if (Clipboard.isSupported()) {

			new Clipboard('#icon_link_note');
			Noty({
				message: marknotes.message.copy_link_done,
				type: 'success'
			});
		}
	}
}
