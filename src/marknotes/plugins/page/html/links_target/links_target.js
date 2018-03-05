marknotes.arrPluginsFct.push("fnPluginHTMLLinksTarget");
/**
 * Force links found in the note's content to be opened in a new window
 * @returns {Boolean}
 */
function fnPluginHTMLLinksTarget() {

	if (marknotes.note.url !== '') {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	fnPluginHTMLLinksTarget - Open links in a new window');
		}
		/*<!-- endbuild -->*/
		//var $currentURL = location.protocol + '//' + location.host;

		$('article a[href^="http:"], article a[href^="https:"]')
			//.not('[href^="' + $currentURL + '/"]')
			.attr('target', '_blank').attr('rel','noopener');
	}

	return true;

}
