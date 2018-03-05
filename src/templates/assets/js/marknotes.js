$(document).ready(function () {

	var $currentURL = location.protocol + '//' + location.host;

	$('a[href^="http:"], a[href^="https:"]')
		.not('[href^="' + $currentURL + '/"]')
		.attr('target', '_blank')
		.attr('rel', 'noopener');

	// Last part : the array marknotes.arrPluginsFct is a global
	// array and will be initialized by
	// the differents plugins (like Bootstrap, DataTable, ...)
	// and will contains functions name.
	//
	// For instance : the file /marknotes/plugins/page/html/bootstrap/bootstrap.js
	// contains this line :
	// 		marknotes.arrPluginsFct.push("PluginBootstrap");
	//
	// This to tell to this code that the PluginBootstrap function should
	// be fired once the note is displayd.  So, let's do it

	try {
		for (var i = 0, len = marknotes.arrPluginsFct.length; i < len; i++) {
			// As explained here : https://www.sitepoint.com/call-javascript-function-string-without-using-eval/
			fn = window[marknotes.arrPluginsFct[i]];

			if (typeof fn === "function") fn();

		}
	} catch (err) {
		console.warn(err.message);
	}

});
