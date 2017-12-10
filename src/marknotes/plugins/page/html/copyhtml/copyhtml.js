/**
 * Copy the html's note content in the clipboard so, f.i., we can paste it then in an email
 */

function copyHTMLSource(text) {

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

	// The 'text' variable contains the HTML string.
	// Make a few cleaning

	// Remove empty style property
	regex = /style=" *"/gm;
	text = text.replace(regex, '');

	// remove the lazyload class (added by the optimize
	// content plugin)
	regex = /img src="(.*)" class=" *lazyloaded" data-src="[^"]*"/gm;
	text = text.replace(regex, 'img src="$1"');

	// Remove the microdata tag perhaps added by
	// the microdata content plugin
	regex = /<span class="microdata"><span .*><span itemprop=".*">(.*)<\/span><\/span><\/span>/gm;
	text = text.replace(regex, "$1");

	// Ok, now, we've a cleaned string (as far as possible)
	// Put it into a DOM element and, then, copy it to the clipboard
	var element = document.createElement('DIV');
	element.textContent = text;
	document.body.appendChild(element);

	selectElementText(element);
	document.execCommand('copy');
	element.remove();

}

function fnPluginButtonCopyHTML() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - CopyHTML');
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
		// Remove unneeded DOM objects there was added by,
		// f.i., the DataTable Plugin

		try {
			var $arrElem = ['dtmn-Bottom', 'dtmn-Buttons', 'dtmn-Find', 'dtmn-List', 'dataTables_scrollHead'];
			var $j = $arrElem.length;
			var $elements = null;

			for (var $i = 0; $i < $j; $i++) {
				$elements = document.getElementById('note_content').parentNode.getElementsByClassName($arrElem[$i]);

				while ($elements.length > 0) {
					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						console.log('	removing DOM child ' + $arrElem[$i]);
					}
					/*<!-- endbuild -->*/

					$elements[0].parentNode.removeChild($elements[0]);
				}
			}
		} catch (e) {}

		// In case of the heading 1 isn't visible (it's the
		// case with the AdminLTE template), be sure to make
		// it visible in the copied HTML
		var $show = $('article h1').css('display');
		$('article h1').show();
		copyHTMLSource($('article').html());
		$('article h1').css('display', $show);

		Noty({
			message: $.i18n('copy_html_done'),
			type: 'success'
		});
	}

	return true;
}
