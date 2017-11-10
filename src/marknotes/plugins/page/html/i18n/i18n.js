/**
 * This script will initialize and load supported languages
 *
 * This plugin will create the marknotes.arri18nFct variable so,
 * other plugins can push functions in it (like the datatable.js file)
 *
 * Functions mentionned in marknotes.arri18nFct will be called
 * automatically when i18n has finish to load translation files so when
 * translation is ready to be used
 */
marknotes.arrPluginsFct.push("fnPluginHTMLi18n");

//
marknotes.arri18nFct = [];

/**
 * Translate content
 *
 * @returns {undefined}
 */
function fnPluginHTMLi18n() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - i18n');
	}
	/*<!-- endbuild -->*/

	try {
		if ($.isFunction($.fn.i18n)) {
			var set_locale_to = function (locale) {
				if (typeof locale == 'undefined') locale = marknotes.settings.locale;
				if (locale) $.i18n().locale = locale;
			};

			$.i18n().load({
				'en': marknotes.webroot + 'languages/marknotes-en.json',
				'fr': marknotes.webroot + 'languages/marknotes-fr.json'
			}).done(function () {
				set_locale_to(url('?language'));
				runi18nFunctions();
			});
		}
	} catch (err) {
		console.warn(err.message);
	}

	return true;

}

/**
 * The array marknotes.arri18nFct is a global array and will be initialized by
 * the differents plugins (like DataTables, ...) and will contains functions name.
 *
 * For instance : the file
 * /marknotes/plugins/page/html/datatables/datatables.js contains this line :
 *    marknotes.arri18nFct.push("PluginBfnPluginHTMLDataTablesootstrap");
 *
 * This to tell to this code that the fnPluginHTMLDataTables function should
 * be fired once the i18n plugin has finish to load languages.  So, let's do it
 */
function runi18nFunctions() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('--- Start - Running i18n functions ---');
		console.log(marknotes.arri18nFct);
	}
	/*<!-- endbuild -->*/

	try {

		// Duplicate the marknotes.arri18nFct array (use slice() for this)
		// because some functions like f.i. fnTranslateInterface()
		// (in the interface.php template)
		// should be called only once and, that function, remove its entry from the
		// marknotes.arri18nFct array.
		// Be sure to process every items so copy the array

		$arrFct = marknotes.arri18nFct.slice();

		var $j = $arrFct.length;
		for (var $i = 0, $j = $arrFct.length; $i < $j; $i++) {

			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log('   call ' + ($i + 1) + '/' + $j + ' : ' + $arrFct[$i]);
			}
			/*<!-- endbuild -->*/

			fn = window[$arrFct[$i]];

			if (typeof fn === "function") {
				// Call the function and tells the i18n is the caller
				var params = {};
				params.caller = 'i18n';
				fn(params);
			}
		}
	} catch (err) {
		console.warn(err.message);
	}

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('--- End - Running i18n functions ---');
	}
	/*<!-- endbuild -->*/

	return true;

}
