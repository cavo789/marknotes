$arrPluginsFct.push("fnPluginHTMLLinkify");

function fnPluginHTMLLinkify() {

	// Try to detect email, urls, ... not yet in a <a> tag and so ... linkify them
	if ($.isFunction($.fn.linkify)) {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('Plugin html - Linkify');
		}
		/*<!-- endbuild -->*/

		$('page').linkify();
	}

}
