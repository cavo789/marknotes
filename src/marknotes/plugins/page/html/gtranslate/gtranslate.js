/**
 * This function is automatically called by Google Translate;
 * no need to foresee something.
 * The call is done by including a link to the translate script :
 *
 *    translate.google.com/translate_a/element.js?cb=googleTranslateElementInit
 *
 * This is done in the addJS() function of the gtranslate.php file
 */

function googleTranslateElementInit() {
	new google.translate.TranslateElement({
		pageLanguage: marknotes.settings.language,
		layout: google.translate.TranslateElement.FloatPosition.TOP_LEFT,
		autoDisplay: false /* Don't show the bar if the user's browser support the note's language */
	}, 'google_translate_element');
}
