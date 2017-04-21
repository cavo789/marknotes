$arrPluginsFct.push("fnPluginHTMLBootstrap");

function fnPluginHTMLBootstrap() {
	try {
		$("table").each(function () {

			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log('Plugin html - bootstrap - Add classes to tables');
			}
			/*<!-- endbuild -->*/

			$(this).addClass('table table-striped table-bordered table-hover');
		});
	} catch (err) {
		console.warn(err.message);
	}

	return true;

}
