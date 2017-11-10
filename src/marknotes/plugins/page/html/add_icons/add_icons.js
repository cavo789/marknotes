marknotes.arrPluginsFct.push("fnPluginHTMLAddIcons");

function fnPluginHTMLAddIcons() {

	if (marknotes.note.url !== '') {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.info('      Plugin Page html - add_icons');
		}
		/*<!-- endbuild -->*/

		try {

			var icon_regex = "";
			var icon_icon = "";

			jQuery.each(json_add_icons, function (i) {

				icon_regex = json_add_icons[i].pattern;
				icon_icon = json_add_icons[i].icon;

				/*<!-- build:debug -->*/
				//if (marknotes.settings.debug) {
				//	console.info('         Scan for ' + icon_regex + ' and add ' +
				//		icon_icon + ' if found');
				//}
				/*<!-- endbuild -->*/

				// article for HTML rendering or slides for slideshows
				$("article a, .slides a").each(function () {
					$href = $(this).attr("href");

					if (($href !== '#') && ($href !== 'undefined')) {
						reg = new RegExp(icon_regex, "g");
						if (reg.test($href)) {

							/*<!-- build:debug -->*/
							if (marknotes.settings.debug) {
								console.info('            FOUND : ' + $href);
							}
							/*<!-- endbuild -->*/

							$sAnchor = '<i class="' + icon_icon + '" aria-hidden="true"></i>';

							if (position_add_icons == 'before') {
								$sAnchor += '&nbsp;' + $(this).text();
							} else {
								$sAnchor = $(this).text() + '&nbsp;' + $sAnchor;
							}

							$(this).html($sAnchor);
						}

						/*
						(/\.(log|md|markdown|txt)$/i.test($href)) {
						// LOG - Open it in a new windows and not in the current one
						$sAnchor += '<i class="icon_file fa fa-file-text-o" aria-hidden="true"></i>';
						$(this).html($sAnchor).addClass('download-link').attr('target', '_blank');
						*/
					}

				});

			});

		} catch (err) {
			console.warn(err.message);
		}
	} // if (marknotes.note.url !== '')

	return true;
}
