/**
 * fnPluginButtonClipboard
 * ------------------------
 * Copy the note content in the clipboard so, f.i., we can paste it then in an email
 * Copy html tags so layout is copied too
 * @link https://clipboardjs.com/
 *
 * fnPluginButtonClipboardLinkNote
 * ------------------------
 * Copy just the URL to the note (full, like http://localhost/notes/docs/note.html)
 */
function fnPluginButtonClipboard() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - clipboard - Initialize');
	}
	/*<!-- endbuild -->*/

	if (marknotes.note.url == '') {
		// The user click on the Reveal button but should first select
		// a note in the treeview
		Noty({
			message: $.i18n('error_select_first'),
			type: 'error'
		});
	} else {
		if (typeof Clipboard === 'function') {
			if (Clipboard.isSupported()) {
				console.log('1');
				var clipboard = new Clipboard('#icon_clipboard');
				console.log('2');
				clipboard.on('success', function (e) {
					console.log('3');
					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						console.info('Action:', e.action);
						console.info('Text:', e.text);
						console.info('Trigger:', e.trigger);
					}
					/*<!-- endbuild -->*/

					Noty({
						message: $.i18n('copy_clipboard_done'),
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
	}

	return true;
}

/**
 * Copy the URL of the note in the clipboard
 */
function fnPluginButtonClipboardLinkNote() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - clipboard - Copy URL of the note in the clipboard');
	}
	/*<!-- endbuild -->*/

	if (marknotes.note.url == '') {
		// The user click on the Reveal button but should first select
		// a note in the treeview
		Noty({
			message: $.i18n('error_select_first'),
			type: 'error'
		});
	} else {
		function selectElementText(element) {

			if (document.selection) {
				var range = document.body.createTextRange();
				range.moveToElementText(element);
				range.select();
			} else if (window.getSelection) {
				var range = document.createRange();
				range.selectNode(element);
				window.getSelection().removeAllRanges();
				window.getSelection().addRange(range);
			}
		}

		var element = document.createElement('DIV');
		element.textContent = marknotes.note.url;
		document.body.appendChild(element);
		selectElementText(element);
		document.execCommand('copy');
		element.remove();

		Noty({
			message: $.i18n('copy_link_done', marknotes.note.url),
			type: 'success'
		});
	}
}
