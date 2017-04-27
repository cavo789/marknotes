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
					scrollY: "50vh", // 50%
					scrollCollapse: true,
					info: true,
					lengthMenu: [
						[10, 25, 50, -1],
						[10, 25, 50, "All"]
					],
					language: {
						decimal: '.',
						thousands: ',',
						url: marknotes.webroot + 'libs/DataTables/' + marknotes.settings.language + '.json'
					}
				});
			});
		}
	} catch (err) {
		console.warn(err.message);
	}

	return true;

}
