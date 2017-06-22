function fnPluginContentTag() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('... filter on [' + $tag + ']');
	}
	/*<!-- endbuild -->*/

   // Not sure that the search plugin is enabled
	try {
		fnPluginTaskSearch_addSearchEntry({
			keyword: $tag,
			reset: true
		});
	} catch (err) {
		console.warn(err.message);
	}

	return true;
}

$(document).ready(function () {

	if ($.isFunction($.fn.flexdatalist)) {

		// Add automatic filtering if defined in the settings.json file
		if (typeof marknotes.plugins !== 'undefined') {
			if (marknotes.plugins.tags.auto_tags !== '') {
				addSearchEntry({
					keyword: marknotes.plugins.tags.auto_tags
				});
			}
		}
	} // if ($.isFunction($.fn.flexdatalist))
});
