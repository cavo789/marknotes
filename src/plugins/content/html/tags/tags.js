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
