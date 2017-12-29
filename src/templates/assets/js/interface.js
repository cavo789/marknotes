$(document).ready(function () {

	$(".sidebar-toggle").click(function (e) {
		$('.content-headerFixed').toggleClass('content-headerFixed-left');
	});

	// By clicking anywhere on the document, collapse the sidebar
	// (the sidebar should be closed when f.i. the user click in the note itself)
	//$('body,html').click(function (e) {
	//	$('.control-sidebar').removeClass('control-sidebar-open');
	//});

	// By showing the sidebar with options (at the right of the screen),
	// the sidebar should "push" the note's content i.e. make place for
	// its own content and not, as by default, show it's content "over"
	// the note's content.
	// (seems very complicult way to do this)
	//
	// --------
	// BUG IF ENABLED : By clicking on a button in the right sidebar,
	// the sidebar should be closed. This is the initializeTasks()
	// function() in marknotes.js. But, if the line here below are
	// uncommented (and ideally should be), then the "hide on click"
	// won't work anymore
	// --------
	//
	//$('[data-toggle="control-sidebar"]').controlSidebar()
	//var $controlSidebar = $('[data-toggle="control-sidebar"]').data('lte.controlsidebar')
	// Set the data-slide option to false ==> push the content
	//$controlSidebar.options.slide = false;

	initializeTasks();

	if (typeof marknotes.arri18nFct !== "undefined") {
		marknotes.arri18nFct.push("fnTranslateInterface");
	}

	// scrollDir is a function added by the Scrolldir library
	// @link : https://github.com/dollarshaveclub/scrolldir
	//scrollDir({ direction: 'up' }); // change the default to "UP"

});

// Called by the i18n plugin when that plugin is enabled
function fnTranslateInterface(params) {
	try {

		// This function should only be fired once
		// So, now, remove it from the arri18nFct array
		marknotes.arri18nFct.splice(marknotes.arri18nFct.indexOf('fnTranslateInterface'), 1);

		$('#mnLogo').prop('title', $.i18n('app_download'));
		$('#mnWebsite').prop('href', $.i18n('app_website'));
		$('body').i18n();
	} catch (e) {
		console.warn(e.message);
	}

	return true;
}
