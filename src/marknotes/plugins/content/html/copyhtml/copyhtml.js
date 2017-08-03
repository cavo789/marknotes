/**
 * Copy the html's note content in the clipboard so, f.i., we can paste it then in an email
 */

 function copyHTMLSource(text){
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
   element.textContent = text;
   document.body.appendChild(element);
   selectElementText(element);
   document.execCommand('copy');
   element.remove();
 }

function fnPluginButtonCopyHTML() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('Plugin html - CopyHTML');
	}
	/*<!-- endbuild -->*/

	copyHTMLSource($('#note_content').html());

	Noty({
		message: marknotes.message.copy_html_done,
		type: 'success'
	});

	return true;
}
