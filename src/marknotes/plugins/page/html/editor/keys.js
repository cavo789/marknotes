/**
 * Thanks to https://github.com/madrobby/keymaster, catch somes
 * keyboard events to start functions like CTRL-S for saving the
 * editor's content
 */

/* keymaster.js should be loaded */

try {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Editor - keys');
	}
	/*<!-- endbuild -->*/

	// By default keymaster.js isn't active on textarea, change this
	// setting here.
	// https://github.com/madrobby/keymaster#filter-key-presses
	key.filter = function(event){
		var tagName = (event.target || event.srcElement).tagName;
		return !(tagName == "INPUT" || tagName == "SELECT");
	}

	// Catch CTRL-S
	key("ctrl+s", function()
		{
			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log('	  CTRL-S pressed, save the file');
			}
			/*<!-- endbuild -->*/
			try {
				// TODO - Need to call the editor save button
				// Problem with SimpleMDE is the button_save() button
				// can't be called outside the simplemde() constructor.
				// Need something else...
				//buttonSave(filename, simplemde.value());
			} catch (e) {
			} finally {
			}

			// Prevent standard default browser event
			return false;
		}
	);
} catch (e) {
} finally {
}
