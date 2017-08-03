marknotes.arrPluginsFct.push("fnPluginHTMLDataTable");

/**
 * Add the "table" class to any <table>
 *
 * @returns {undefined}
 */
function fnPluginHTMLDataTable() {

	try {

		if ($.isFunction($.fn.DataTable)) {

			$("table").each(function () {

				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.log('Plugin html - DataTables');
				}
				/*<!-- endbuild -->*/

				$(this).addClass('display');
				$(this).DataTable({
					 "fixedHeader": false,
					"scrollY": "50vh", // 50%
					"scrollCollapse": true,
					"info": true,
					"order": [], // No ordering by default
					"dom": "<'dtmn-Buttons'B><'dtmn-Find'f><'dtmn-List'l>rt<'dtmn-Bottom'pi>",  // https://datatables.net/reference/option/dom
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
			});
		}
	} catch (err) {
		console.warn(err.message);
	}

	return true;

}
