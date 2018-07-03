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

	// Quick replace
	regex = /[“”]/gm;
	text = text.replace(regex, '"');
	regex = /[’]/gm;
	text = text.replace(regex, "'");

	// Remove empty style property
	regex = /style=" *"/gm;
	text = text.replace(regex, '');

	// Remove the "par_" id added by the anchor plugin
	// Replace thus "<p id="par_25">any text</p>" by
	// <p>any text</p>  (only work on the opening tag)
	regex = /<p id="par_\d{1,}">/gm;
	text = text.replace(regex, '<p>');

	// Remove empty paragraphs
	regex = /<p><\/p>/gm;
	text = text.replace(regex, '');

	// remove the lazyload class (added by the optimize
	// content plugin)
	regex = /img src="(.*)" class=" *lazyloaded" data-src="[^"]*"/gm;
	text = text.replace(regex, 'img src="$1"');

	// Remove the microdata tag perhaps added by
	// the microdata content plugin
	regex = /<span class="microdata"><span .*><span itemprop=".*">(.*)<\/span><\/span><\/span>/gm;
	text = text.replace(regex, "$1");

	// Remove the noopener noreferrer attributes for anchor
	regex = /(<a .*)(rel="noopener noreferrer")([^>]*>)/gm;
	text = text.replace(regex, "$1$3");

	// Remove the noopener attributes for anchor
	regex = /(<a .*)(rel="noopener")([^>]*>)/gm;
	text = text.replace(regex, "$1$3");

	// Remove unneeded classes
	regex = /(<a .*)(class="linkified")([^>]*>)/gm;
	text = text.replace(regex, "$1$3");

	// Remove data attributes for tables
	regex = /(<table .*)(data-datatables-enable="[0|1]")(.*)>/gm;
	text = text.replace(regex, "$1$3");

	// Remove Table-of-content added classes
	regex = /(<li .*)(class="toc\d")(>.*<\/li>)/gm;
	text = text.replace(regex, "$1$3");

	// Remove unneeded classes
	regex = /<div class="table-responsive">/gm;
	text = text.replace(regex, "<div>");

	// This character is a "empty square" (an invalid space char)
	// Replace by a space
	regex = / /gm;
	text = text.replace(regex, " ");

	// Remove empty space between HTML tags. Such spaces are just
	// unneeded
	regex = /\>(\s+)\</gm;
	text = text.replace(regex, "><");

	// Last thing, remove the space that is perhaps just before
	// the end tag of an HTML tag (f.i. <a href="..." > or <li >)
	regex = /(<[^>]*) >/gm;
	text = text.replace(regex, "$1>");

	// Ok, now, we've a super cleaned string (as far as possible)
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
