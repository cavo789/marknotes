marknotes.arrPluginsFct.push("fnPluginHTMLPrism");

function fnPluginHTMLPrism() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Prism - highlightAll');
	}
	/*<!-- endbuild -->*/

	if (typeof Prism === 'object') {
		// @link https://github.com/PrismJS/prism
		Prism.highlightAll();
	}
}
