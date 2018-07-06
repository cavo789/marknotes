/**
 * Configure flexdatalist
 * @link http://projects.sergiodinislopes.pt/flexdatalist
 */
marknotes.arrPluginsFct.push("fnPluginTaskSearch_init");
marknotes.arrPluginsFct.push("fnPluginTaskSearch_afterDisplay");

/**
 * Initialize the search
 * @link http://projects.sergiodinislopes.pt/flexdatalist/#options
 */
function fnPluginTaskSearch_init() {

	try {

		// Calculate the width of the search box
		$width = window.innerWidth - $('.main-sidebar').width();
		$width = $width - 500; // $('.navbar-custom-menu').width();

		// Get place for other DOM elements
		$width = $width - 100;

		// Not too big...
		if ($width>500) {
			$widht=500;
		}

		$('#divSearch').css("max-width", $width +"px");

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Page html - Search - Initialization');
			console.log('		 This function will be called only once');
		}
		/*<!-- endbuild -->*/

		if ($('#search-advanced-btn').length > 0) {
			$("#search-advanced-btn").click(function() {
				fnPluginTaskSearchAdvanced();
			});
		}

		$("#search-btn").click(function() {
			fnPluginTaskSearchStartSearch();
		});

		// This function should only be fired once
		// So, now, remove it from the arrPluginsFct array
		marknotes.arrPluginsFct.splice(marknotes.arrPluginsFct.indexOf('fnPluginTaskSearch_init'), 1);

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('		 fnPluginTaskSearch_init has been removed from marknotes.arrPluginsFct');
		}
		/*<!-- endbuild -->*/

		// initialize the search area, thanks to
		// the Flexdatalist plugin
		// @link http://projects.sergiodinislopes.pt/flexdatalist/#options
		try {
			if ($.isFunction($.fn.flexdatalist)) {
				$('.flexdatalist').flexdatalist({
					cache: true,
					focusFirstResult: true,
					multiple: true,
					noResultsText: $.i18n('search_no_result'),
					searchContain: true,
					searchIn: 'name',
					data: 'tags.json',
					minLength: 3,
					toggleSelected: true,
					valueProperty: 'id',
					selectionRequired: false,
					visibleProperties: ['name'],
					requestType: (marknotes.settings.debug ? 'get' : 'post')
				});

				$('.flexdatalist').on('change:flexdatalist', function (event, set, options) {
					if ($.isFunction($.fn.jstree)) {
						$('#TOC').jstree(true).show_all();
						$('#TOC').jstree('search', $('#search').val());
					} // if ($.isFunction($.fn.jstree))
				});

				// Interface : put the cursor immediatly
				// in the edit box
				try {
					$('#search').focus();
				} catch (err) {
					console.warn(err.message);
				}

			} // if ($.isFunction($.fn.flexdatalist))

		} catch (err) {
			console.warn(err.message);
		}
	} catch (err) {
		console.warn(err.message);
	}

	return true;
}

/**
 * A search has been done and now the note is being displayed
 */
function fnPluginTaskSearch_afterDisplay() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Search - A note has been displayed');
	}
	/*<!-- endbuild -->*/

	/*<!-- build:debug -->*/
	// Don't focus !!!
	// Problem is that the view will always be resetted on
	// the search field so the treeview will always display the first
	// items (just like we press then Home key)
	//$('#search-flexdatalist').focus();
	/*<!-- endbuild -->*/

	if ($.isFunction($.fn.highlight)) {
		// Get the searched keywords.
		// Apply the restriction on the size.
		var $searchKeywords = $('#search').val().substr(0, marknotes.search.max_width).trim();

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
 *	  keyword			: the value to add in the search area
 *	  reset (optional)  : if true, the search area will be resetted
 *							before (so only search for the new
 *							keyword)
 *
 * @returns {Boolean}
 */
function fnPluginTaskSearch_addSearchEntry($entry) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Search - Add an entry');
	}
	/*<!-- endbuild -->*/

	$bReset = (($entry.reset === 'undefined') ? false : $entry.reset);

	$current = $('#search').val().trim();

	if (($current !== '') && ($bReset === false)) {
		// Append the new keyword only when bReset
		// is not set or set to False
		var values = $current.split(',');
		values.push($entry.keyword);
		$('#search').val(values.join(','));
	} else {
		if ($.isFunction($.fn.flexdatalist)) {
			// TODO
			// Should work but no...
			// http://projects.sergiodinislopes.pt/flexdatalist/
			// The add method should add a new keyword but here
			// the key isn't added to the flexdatalist entry
			//$('#search').flexdatalist('add', $entry.keyword);
		}
	}

	if ($.isFunction($.fn.jstree)) {
		$('#TOC').jstree(true).show_all();
		$('#TOC').jstree('search', $entry.keyword);
	}

	return true;
}

/**
 * Rerun the search but avoid to use the cache
 * This by setting the cache=0 parameter on the querystring
 */
function fnPluginTaskSearchClearCache() {
	// Remember the old URL
	var oldSearchURL = $('#TOC').jstree(true).settings.search.ajax.url;

	$('#TOC').jstree(true).show_all();
	$('#TOC').jstree(true).settings.search.ajax.url = oldSearchURL + '?cache=0';
	$('#TOC').jstree('search', $('#search').val());

	$('#TOC').jstree(true).settings.search.ajax.url = oldSearchURL;

	return true;
}

// The user has clicked on the search advanced button : show a list of
// folder so we can restrict the search action only on these folders
function fnPluginTaskSearchAdvanced() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Search - Advanced form');
	}
	/*<!-- endbuild -->*/

	$.ajax({
		beforeSend: function () {
			// Remove the form if already present
			if ($('#modal-box').length) {
				$('#modal-box').remove();
			}
		},
		type: "POST",
		url: "index.php",
		data: "task=task.search.getfolders",
		dataType: "json",
		success: function (data) {
			if (data.hasOwnProperty("form")) {
				// The result of the task 'task.search.getfolders' is a HTML
				// string
				// Add that form to the parent of the content DOM element
				$("#CONTENT").parent().append(data['form']);
				// And show the search advanced  form.
				fnPluginTaskShowAdvancedSearchForm();
			} else {
				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.error('	  Invalid JSON returned by task.search.getfolders');
				}
				/*<!-- endbuild -->*/

			}
		}
	});

	return true;
}

function fnPluginTaskShowAdvancedSearchForm() {

	//Fade in the Popup
	$('#modal-box').fadeIn(300);
	$('#cbxFolderList').focus();

	try {
		$("#cbxFolderList").val(marknotes.search.restrict_folder);
		$('#chkDisablePlugins').prop('checked', marknotes.search.disable_plugins);
	} catch (e) {
	} finally {
	}

	//Set the center alignment padding + border see css style
	var popMargTop = ($('#modal-box').height() + 24) / 2;
	var popMargLeft = ($('#modal-box').width() + 24) / 2;

	$('#modal-box').css({
		'margin-top': -popMargTop,
		'margin-left': -popMargLeft
	});

	// Add the mask to body
	$('body').append('<div id="mask"></div>');
	$('#mask').fadeIn(300);

	$('#modal-box .submit').click(function (e) {
		$('#mask, .modal-popup').fadeOut(300, function () {
			$('#mask').remove();
		});
	});

}

// When the user click on the Search button and doesn't press the Enter key
// Start the search
function fnPluginTaskSearchStartSearch() {
	alert('RunSearch');
$('#search').flexdatalist('add', 'add_me');
alert($('#search').flexdatalist('value'));
		$('#TOC').jstree(true).show_all();
	$('#TOC').jstree('search', $('#search').val());
}
