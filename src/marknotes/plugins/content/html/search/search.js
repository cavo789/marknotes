marknotes.arrPluginsFct.push("fnPluginTaskSearch_init");

/**
 * Initialize the search
 */
function fnPluginTaskSearch_init() {

	try {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('      fnPluginTaskSearch_init - This function will be called only once');
		}
		/*<!-- endbuild -->*/

		// This function should only be fired once
		// So, now, remove it from the arrPluginsFct array
		marknotes.arrPluginsFct.splice(marknotes.arrPluginsFct.indexOf('fnPluginTaskSearch_init'), 1);

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('      fnPluginTaskSearch_init has been removed from marknotes.arrPluginsFct');
		}
		/*<!-- endbuild -->*/

		if ($.isFunction($.fn.jstree)) {

			$('#search').change(function (e) {
				console.log("Searching for " + $(this).val());
				$('#TOC').jstree(true).show_all();
				$('#TOC').jstree('search', $(this).val());
			});

		} // if ($.isFunction($.fn.jstree))

		// initialize the search area, thanks to the Flexdatalist plugin

		if ($.isFunction($.fn.flexdatalist)) {

			$('.flexdatalist').flexdatalist({
				toggleSelected: true,
				minLength: 3,
				valueProperty: 'id',
				selectionRequired: false,
				visibleProperties: ["name"],
				searchIn: 'name',
				data: 'tag.json',
				focusFirstResult: true,
				noResultsText: marknotes.message.search_no_result,
				requestType: (marknotes.settings.debug ? 'get' : 'post')
			});

		} // if ($.isFunction($.fn.flexdatalist))

		$('#search').css('width', $('#sidebar').width() - 5);
		$('.flexdatalist-multiple').css('width', $('.flexdatalist-multiple').parent().width() - 10).show();

		// Interface : put the cursor immediatly in the edit box
		try {
			$('#search-flexdatalist').focus();
		} catch (err) {
			console.warn(err.message);
		}

	} catch (err) {
		console.warn(err.message);
	}

	return true;

}

function fnPluginTaskSearch_afterDisplay() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('fnPluginTaskSearch_afterDisplay - A note has been displayed');
	}
	/*<!-- endbuild -->*/

	/*<!-- build:debug -->*/
	// Don't focus !!!
	// Problem is that the view will always be resetted on the search field so the
	// treeview will always display the first items (just like we press then Home key)
	//$('#search-flexdatalist').focus();
	/*<!-- endbuild -->*/

	if ($.isFunction($.fn.highlight)) {
		// Get the searched keywords.  Apply the restriction on the size.
		var $searchKeywords = $('#search').val().substr(0, marknotes.settings.search_max_width).trim();

		if ($searchKeywords !== '') {

			$arrKeywords = $searchKeywords.split(',');

			for (var i = 0; i < $arrKeywords.length; i++) {

				$highlight = $arrKeywords[i];

				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.log('Highlighting ' + $highlight);
				}
				/*<!-- endbuild -->*/

				$("#CONTENT").highlight($highlight);

			} // for

		} // if ($searchKeywords !== '')

	} // if ($.isFunction($.fn.highlight))

	return true;
}

/**
 * Add a new entry in the search box (append and not replace)
 * Called by the tags plugin
 *
 * @param {json} $entry
 *      keyword           : the value to add in the search area
 *      reset (optional)  : if true, the search area will be resetted before
 *                          (so only search for the new keyword)
 *
 * @returns {Boolean}
 */
function fnPluginTaskSearch_addSearchEntry($entry) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('fnPluginTaskSearch_addSearchEntry');
	}
	/*<!-- endbuild -->*/

	$bReset = (($entry.reset === 'undefined') ? false : $entry.reset);

	$current = $('#search').val().trim();

	if (($current !== '') && ($bReset === false)) {
		// Append the new keyword only when bReset is not set or set to False
		var values = $current.split(',');
		values.push($entry.keyword);
		$('#search').val(values.join(','));
	} else {
		$('#search').val($entry.keyword);
	}

	return true;

}
