marknotes.arrPluginsFct.push("fnPluginHTMLShare");

/**
 * Handle the click on the Share toolbar
 * Provide sharing functionnalities
 *
 * @returns {undefined}
 */
function fnPluginHTMLShare() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Share');
	}
	/*<!-- endbuild -->*/

	if ($.isFunction($.fn.toolbar)) {
		$("#toolbar-share")
			.toolbar({
				content: "#toolbar-share-options",
				position: "bottom",
				event: "click",
				style: "default",
				hideOnClick: true
			});
	}

	$("[data-share]").click(function (event) {

		event.stopPropagation();
		event.stopImmediatePropagation();

		var $share = $(this).data('share');

		var $options = 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=700';
		var $url = marknotes.note.url;

		switch ($share) {
		case 'facebook':
			window.open('https://www.facebook.com/dialog/feed?app_id=277546802408059&amp;display=popup&amp;link=' + $url, $options);
			break;
		case 'google':
			window.open('https://plus.google.com/share?url=' + $url, $options);
			break;
		case 'linkedin':
			window.open('https://www.linkedin.com/shareArticle?mini=true&url=' + $url, $options);
			break;
		case 'mail':
			window.open('mailto:?subject=' + $url, $options);
			break;
		case 'twitter':
			window.open('https://twitter.com/share?url=' + $url, $options);
			break;
		};

	});
}
