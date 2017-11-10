// This function will be called when a note is displayed
// fnPluginContentTag is called when the user has clicked on a "tag"

marknotes.arrPluginsFct.push("fnPluginContentTag");

function fnPluginContentTag(params) {

	// marknotes.js will call this function and set params which is a JSON
	// and will contains the "clicked" tag in params['param']

	tag = '';

	if (typeof params !== 'undefined') {
		if (params.hasOwnProperty('param')) {
			tag = params.param;

			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log('      Plugin Page html - Tags - Filter on ' + tag);
			}
			/*<!-- endbuild -->*/
		}

		// Not sure that the search plugin is enabled
		if (tag !== '') {
			try {
				fnPluginTaskSearch_addSearchEntry({
					keyword: params['param'],
					reset: true
				});
			} catch (err) {
				console.warn(err.message);
			}
		}
	}

	return true;
}
