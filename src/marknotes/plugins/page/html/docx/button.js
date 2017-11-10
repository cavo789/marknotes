/**
 * Relies on Googoose for the basic exportation to Winword.
 * This javascript file is loaded by marknotes only when Pandoc isn't
 * correctly installed.
 *
 * The fnPluginHTMLDOCX will be called by the "Docx" button of the content's
 * toolbar
 / @link https://github.com/aadel112/googoose
 */
function fnPluginHTMLDOCX() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - DOCX');
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
		// The extension should be .DOC and not .DOCX
		var fname = marknotes.note.basename + '.doc';

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('         Generate ' + fname);
		}
		/*<!-- endbuild -->*/

		// See https://github.com/aadel112/googoose#options for
		// explanation of the options
		var o = {
			area: 'article', // The content is inside the article tag
			lang: marknotes.settings.locale, // language of the content
			filename: fname // name of the file, will be the one of the downloaded file
		};

		try {
			$(document).googoose(o);
		} catch (e) {
			console.warn('Error when trying to convert with Googoose : [' + e.message + ']');
		}
	}

	return true;

}
