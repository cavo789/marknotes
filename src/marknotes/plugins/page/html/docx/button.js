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

	// When the user has clicked on a note from the treeview, the jstree_init()
	// function (from jstree.js) has initialized the marknotes.note.file
	// variable to 'objNode.data.file' i.e. the data-file info of that
	// node and that info is set by the PHP listFiles task to the relative
	// filename of the note without any extension (so, f.i.
	// folder/documentation/marknotes and not the full file like
	// http://localhost/docs/folder/documentation/marknotes.html)
	if (marknotes.note.file == '') {
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

			// Googoose will not export the note's title if
			// that title is hidden so, check if we need to show
			// it => show it, export and hide it back
			var $bVisible = $('#CONTENT h1').is(':visible');
			if (!$bVisible) $('#CONTENT h1').show();
			$(document).googoose(o);
			if (!$bVisible) $('#CONTENT h1').hide();

		} catch (e) {
			console.warn('Error when trying to convert with Googoose : [' + e.message + ']');
		}
	}

	return true;

}
