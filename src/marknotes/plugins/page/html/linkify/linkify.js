marknotes.arrPluginsFct.push("fnPluginHTMLLinkify");

function fnPluginHTMLLinkify() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Linkify');
	}
	/*<!-- endbuild -->*/

	// Try to detect email, urls, ... not yet in a <a> tag and so ... linkify them
	// @link https://github.com/SoapBox/linkifyjs
	if ($.isFunction($.fn.linkify)) {
		if ($.isFunction($.fn.linkify)) {
			$('article > p').linkify({
				target: "_blank",
				// http://soapbox.github.io/linkifyjs/docs/options.html#ignoretags
				ignoreTags: [
					'code',
					'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
					'pre',
					'script',
					'style'
  				]
			});
		}
	}

}
