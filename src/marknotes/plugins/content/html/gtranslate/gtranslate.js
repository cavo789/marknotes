function googleTranslateElementInit() {
	new google.translate.TranslateElement({
		pageLanguage: marknotes.settings.language,
		layout: google.translate.TranslateElement.FloatPosition.TOP_LEFT,
		autoDisplay: false /* Don't show the bar if the user's browser support the note's language */
	}, 'google_translate_element');
}
