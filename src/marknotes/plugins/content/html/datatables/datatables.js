marknotes.arrPluginsFct.push("fnPluginHTMLDataTable");

/**
 * Add the "table" class to any <table>
 *
 * @returns {undefined}
 */
function fnPluginHTMLDataTable() {

	try {

		if ($.isFunction($.fn.DataTable)) {

			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log('Plugin html - DataTables');
			}
			/*<!-- endbuild -->*/

			$("table").each(function () {

				// When the plugin Datatables is enabled, this plugin will add a
				// data-datatables-enable="1" attribute to each table. This is done in the
				// MarkNotes\Plugins\Content\HTML\DataTables::doIt() subroutine.
				//
				// This, because, once this datatable.js script is loaded, it's well possible
				// that for a specific note, the datatable plugin is disabled in the settings.json
				// file for that note (or folder).
				//
				// So, don't run the code below always but only when the data-datatables-enable
				// attribute is equal to 1.

				$enable = 0;

				if ($(this).attr("data-datatables-enable")) {
					$enable = $(this).data('datatables-enable');
				}

				if ($enable == 1) {
					$(this).addClass('display');
					$(this).DataTable({
						"fixedHeader": false,
						"scrollY": "50vh", // 50%
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
								"copy": marknotes.message.datatable_copy,
								"copyTitle": marknotes.message.datatable_copyTitle,
								"copyKeys": marknotes.message.datatable_copyKeys,
								"copySuccess": {
									"_": marknotes.message.datatable_copySuccess_Many,
									"1": marknotes.message.datatable_copySuccess_One
								}
							},
							"decimal": '.',
							"thousands": ',',
							"url": marknotes.webroot + 'libs/DataTables/' + marknotes.settings.language + '.json'
						}
					});
				} // if ($enable==1)
			});
		}
	} catch (err) {
		console.warn(err.message);
	}

	return true;

}
