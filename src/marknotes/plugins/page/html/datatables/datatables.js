marknotes.arrPluginsFct.push("fnPluginHTMLDataTables");

// If the i18n plugin is loaded, assign fnPluginHTMLDataTable to
// the list of functions to call when language's files are loaded
// So, here, the datatable plugin can be internationalized
if (typeof marknotes.arri18nFct !== "undefined") {
	marknotes.arri18nFct.push("fnPluginHTMLDataTables");
}

var msgCopy = "";
var msgcopyTitle = "";
var msgcopyKeys = "";
var msgcopySuccessMany = "";
var msgcopySuccessOne = "";

/**
 * Add Datatables features to each table in the document.
 *
 * The params parameter will be initialized by marknotes.js or
 * by the i18n plugin i.e. the function who called this one.
 *
 * @returns boolean
 */
function fnPluginHTMLDataTables(params) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - DataTables');
	}
	/*<!-- endbuild -->*/

	try {
		if ($.isFunction($.fn.DataTable)) {

			$("table").each(function () {

				/** When the plugin Datatables is enabled, this plugin will
				 * add a data-datatables-enable="1" attribute to each table.
				 * This is done in the Plugins\Content\HTML\DataTables::doIt()
				 * subroutine.
				 *
				 * This, because, once this datatable.js script is loaded,
				 * it's well possible that for a specific note, the datatable
				 * plugin is disabled in the settings.json file for that
				 * note (or folder).
				 *
				 * So, don't run the code below always but only when the
				 * data-datatables-enable attribute is equal to 1.
				 */
				$enable = 0;

				if ($(this).attr("data-datatables-enable")) {
					$enable = parseInt($(this).data('datatables-enable'));
				}

				// Only tables haved the datatables-enable="1" attribute
				if ($enable === 1) {
					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						console.log('         Is enabled for this note');
					}
					/*<!-- endbuild -->*/

					// Check if i18n is loaded => show the table only
					// in english or offers internationalization feature
					if (!$.isFunction($.fn.i18n)) {
						// Not loaded; only in english

						msgCopy = "Copy";
						msgcopyTitle = "Copy to clipboard";
						msgcopyKeys = "Press <i>ctrl</i> or <i>\u2318</i> + <i>C</i> to copy the table data<br>to your system clipboard.<br><br>To cancel, click this message or press escape.";
						msgcopySuccessMany = "Copied %d rows to clipboard";
						msgcopySuccessOne = "Copied one row to clipboard";

						// Initialize datatables
						initTable(this);

					} else { // if (!$.isFunction($.fn.i18n))

						if (typeof params !== 'undefined') {
							// Does the caller is the i18n plugin ?

							if (params.caller == "i18n") {
								// Yes ==> so i18n is now initialized
								// Get the translated text

								msgCopy = $.i18n('datatable_copy');
								msgcopyTitle = $.i18n('datatable_copyTitle');
								msgcopyKeys = $.i18n('datatable_copyKeys');
								msgcopySuccessMany = $.i18n('datatable_copySuccess_Many');
								msgcopySuccessOne = $.i18n('datatable_copySuccess_One');

								// Initialize datatables
								initTable(this);
							} // if (params.caller == "i18n")
						} // if (typeof params !== 'undefined')
					} // if (!$.isFunction($.fn.i18n))

				} else { // if ($enable==1)
					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						console.log('         Not enabled for this note');
					}
					/*<!-- endbuild -->*/
				}
			});
		}
	} catch (err) {
		console.warn(err.message);
	}

	return true;

}

/*
 * Initialize tables
 */
function initTable(obj) {

	$(obj).addClass('display');
	$(obj).DataTable({
		"fixedHeader": true,
		// So column's headings will scroll with
		// "scrollX": "100%",
		// vertical height : not greater than 50% of the client height
		"scrollY": "50vh",
		"scrollCollapse": true,
		"info": true,
		"order": [], // No ordering by default
		"dom": "<'dtmn-Buttons'B><'dtmn-Find'f><'dtmn-List'l>rt<'dtmn-Bottom'pi>", // https://datatables.net/reference/option/dom
		"buttons": [
		   "copyHtml5"
		],
		"lengthMenu": [
			[-1, 5, 10, 25, 50],
			["All", 5, 10, 25, 50]
		],
		"language": {
			"buttons": {
				//https://datatables.net/extensions/buttons/examples/html5/copyi18n.html
				"copy": msgCopy,
				"copyTitle": msgcopyTitle,
				"copyKeys": msgcopyKeys,
				"copySuccess": {
					"_": msgcopySuccessMany,
					"1": msgcopySuccessOne
				}
			},
			"decimal": '.',
			"thousands": ',',
			"url": marknotes.webroot + 'marknotes/plugins/page/html/datatables/libs/datatables/' + marknotes.settings.language + '.json'
		}
	});

	return true;

}
