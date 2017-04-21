$arrPluginsFct.push("fnPluginHTMLPrism");

function fnPluginHTMLPrism() {

	if (typeof Prism === 'object') {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('Plugin html - Prism');
		}
		/*<!-- endbuild -->*/

		Prism.highlightAll();
	}
}
