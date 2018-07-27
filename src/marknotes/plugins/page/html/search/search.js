/**
 * Configure Search features
 * @link http://projects.sergiodinislopes.pt/flexdatalist
 */
marknotes.arrPluginsFct.push("fnPluginTaskSearch_init");
marknotes.arrPluginsFct.push("fnPluginTaskSearch_afterDisplay");

/**
 * Initialize the search
 */
function fnPluginTaskSearch_init() {
	try {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log("	  Plugin Page html - Search - Initialization");
			console.log("		 This function will be called only once");
		}
		/*<!-- endbuild -->*/

		// This function should only be fired once
		// So, now, remove it from the arrPluginsFct array
		marknotes.arrPluginsFct.splice(
			marknotes.arrPluginsFct.indexOf("fnPluginTaskSearch_init"),
			1
		);

		fnPluginTaskSearch_SetWidth();

		fnPluginTaskSearch_InitializeOptions();

		fnPluginTaskSearch_Flexdatalist();
	} catch (err) {
		console.warn(err.message);
	}

	return true;
}

/**
 * Dynamically set the width of the search field
 */
function fnPluginTaskSearch_SetWidth() {
	try {
		// Calculate the width of the search box
		$width = window.innerWidth - $(".main-sidebar").width();
		$width = $width - 500; // $('.navbar-custom-menu').width();

		// Get place for other DOM elements
		$width = $width - 100;

		// Not too big...
		if (parseInt($width) > 500) {
			$width = 500;
		}

		$("#divSearch").css("max-width", $width + "px");
	} catch (error) {}

	return true;
}

/**
 * Initialize buttons
 */
function fnPluginTaskSearch_InitializeOptions() {
	try {
		// Start the search
		// @TODO - Not working : by clicking on the search button
		// the search should be started just like we've pressed the Enter key
		// in the search field
		/*$(".search_button").click(function() {
			var e = jQuery.Event("keypress", {
				which: 13
			});
			$("#search-flexdatalist").trigger(e);
		});*/

		// When marknotes.search.disable_plugins is set on True
		// don't enable the search_plugins button : the search task willn't
		// fire plugins and will then be faster
		if ($("#search_folder").length > 0) {
			$("#search_folder").prop(
				"checked",
				marknotes.search.restrict_folder !== "."
			);
			$("#search_folder").click(function() {
				fnPluginTaskSearch_clickOptionFolder();
			});
		}

		// When marknotes.search.disable_cache is set on True
		// enable the search_refresh button : the search task willn't
		// use the cache on the server

		if ($("#search_refresh").length > 0) {
			if (marknotes.settings.cache == 1) {
				// The refresh action is only needed if the cache feature
				// has been enabled.
				// marknotes.settings.cache is set in interface.php
				$("#search_refresh")
					.parent()
					.show();
				$("#search_refresh").prop(
					"checked",
					marknotes.search.disable_cache
				);
				$("#search_refresh").click(function() {
					fnPluginTaskSearch_clickOptionCache();
				});
			}
		}

		// When marknotes.search.disable_plugins is set on True
		// don't enable the search_plugins button : the search task willn't
		// fire plugins and will then be faster

		if ($("#search_plugins").length > 0) {
			$("#search_plugins").prop(
				"checked",
				!marknotes.search.disable_plugins
			);
			$("#search_plugins").click(function() {
				fnPluginTaskSearch_clickOptionPlugins();
			});
		}
	} catch (error) {}

	return true;
}

/**
 * The AJAX request should be updated on each changes
 */
function fnPluginTaskSearch_resetSearchURL() {
	// Reinitialize the treeview to search only on the selected folder

	$restrict_folder = marknotes.search.restrict_folder;

	if ($restrict_folder !== ".") {
		$restrict_folder = window.btoa(
			encodeURIComponent(JSON.stringify(marknotes.search.restrict_folder))
		);
	}

	$("#TOC").jstree(true).settings.search.ajax.data = {
		restrict_folder: $restrict_folder,
		disable_cache: marknotes.search.disable_cache ? 1 : 0,
		disable_plugins: marknotes.search.disable_plugins ? 1 : 0
	};

	return true;
}

/**
 * Handle the click on the Search - Disable cache button
 */
function fnPluginTaskSearch_clickOptionCache() {
	marknotes.search.disable_cache = !marknotes.search.disable_cache;

	fnPluginTaskSearch_resetSearchURL();

	if ($("#search_refresh").is(":checked")) {
		// Check : don't use the cache
		$text = $.i18n("search_disable_cache_ON");
	} else {
		$text = $.i18n("search_disable_cache_OFF");
	}

	Noty({ message: $.i18n($text), type: "info" });

	return true;
}

/**
 * Handle the click on the Search - Disable plugins button
 */
function fnPluginTaskSearch_clickOptionPlugins() {
	marknotes.search.disable_plugins = !marknotes.search.disable_plugins;

	fnPluginTaskSearch_resetSearchURL();

	if ($("#search_plugins").is(":checked")) {
		// Check = plugins will be fired
		$text = $.i18n("search_disable_plugins_OFF");
	} else {
		$text = $.i18n("search_disable_plugins_ON");
	}

	Noty({ message: $.i18n($text), type: "info" });

	return true;
}

/**
 * The user has clicked on the search advanced button : show a list of
 * folder so we can restrict the search action only on these folders
 */
function fnPluginTaskSearch_clickOptionFolder() {
	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log("	  Plugin Page html - Search - Retrieve HTML of the form");
	}
	/*<!-- endbuild -->*/

	$.ajax({
		beforeSend: function() {
			// Remove the form if already present
			if ($("#modal-box").length) {
				$("#modal-box").remove();
			}
		},
		type: marknotes.settings.debug ? "GET" : "POST",
		url: "index.php",
		data: "task=task.search.getfolders",
		dataType: "json",
		success: function(data) {
			if (data.hasOwnProperty("form")) {
				// The result of the task 'task.search.getfolders' is a HTML
				// string
				// Add that form to the parent of the content DOM element
				$("#CONTENT")
					.parent()
					.append(data["form"]);
				// And show the search advanced  form.
				fnPluginTaskShowAdvancedSearchForm();
			} else {
				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.error(
						"	  Invalid JSON returned by task.search.getfolders"
					);
				}
				/*<!-- endbuild -->*/
			}
		}
	});

	return true;
}

/**
 * The HTML of the advanced form has been loaded
 */
function fnPluginTaskShowAdvancedSearchForm() {
	//Fade in the Popup
	$("#modal-box").fadeIn(300);
	$("#cbxFolderList").focus();

	try {
		$("#cbxFolderList").val(marknotes.search.restrict_folder);
	} catch (e) {
	} finally {
	}

	// Set the center alignment padding + border see css style
	var popMargTop = ($("#modal-box").height() + 24) / 2;
	var popMargLeft = ($("#modal-box").width() + 24) / 2;

	$("#modal-box").css({
		"margin-top": -popMargTop,
		"margin-left": -popMargLeft
	});

	// Add the mask to body
	$("body").append('<div id="mask"></div>');
	$("#mask").fadeIn(300);

	$("#modal-box .submit").click(function(e) {
		$("#mask, .modal-popup").fadeOut(300, function() {
			$("#mask").remove();
		});

		$("#search_folder").prop(
			"checked",
			marknotes.search.restrict_folder !== "."
		);

		fnPluginTaskSearch_resetSearchURL();
	});
}

/**
 * initialize the search area, thanks to
 * the Flexdatalist plugin
 * @link http://projects.sergiodinislopes.pt/flexdatalist/#options
 */
function fnPluginTaskSearch_Flexdatalist() {
	try {
		if ($.isFunction($.fn.flexdatalist)) {
			$(".flexdatalist").flexdatalist({
				cache: true,
				focusFirstResult: true,
				multiple: true,
				noResultsText: "",
				searchContain: true,
				searchIn: "name",
				data: "tags.json",
				minLength: 3,
				toggleSelected: true,
				valueProperty: "id",
				selectionRequired: false,
				visibleProperties: ["name"],
				requestType: marknotes.settings.debug ? "GET" : "POST"
			});

			$(".flexdatalist").on("change:flexdatalist", function(
				event,
				set,
				options
			) {
				if ($.isFunction($.fn.jstree)) {
					$("#TOC")
						.jstree(true)
						.show_all();
					$("#TOC").jstree("search", $("#search").val());
				} // if ($.isFunction($.fn.jstree))
			});

			// Interface : put the cursor immediatly
			// in the edit box
			try {
				$("#search").focus();
			} catch (err) {
				console.warn(err.message);
			}
		} // if ($.isFunction($.fn.flexdatalist))
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
		console.log("	  Plugin Page html - Search - A note has been displayed");
	}
	/*<!-- endbuild -->*/

	if ($.isFunction($.fn.highlight)) {
		// Get the searched keywords.
		// Apply the restriction on the size.
		var $searchKeywords = $("#search")
			.val()
			.substr(0, marknotes.search.max_width)
			.trim();

		if ($searchKeywords !== "") {
			$arrKeywords = $searchKeywords.split(",");

			for (var i = 0; i < $arrKeywords.length; i++) {
				$highlight = $arrKeywords[i];

				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.log("Highlighting " + $highlight);
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
		console.log("	  Plugin Page html - Search - Add an entry");
	}
	/*<!-- endbuild -->*/

	$bReset = $entry.reset === "undefined" ? false : $entry.reset;

	$current = $("#search")
		.val()
		.trim();

	if ($current !== "" && $bReset === false) {
		// Append the new keyword only when bReset
		// is not set or set to False
		var values = $current.split(",");
		values.push($entry.keyword);
		$("#search").val(values.join(","));
	} else {
		if ($.isFunction($.fn.flexdatalist)) {
			// @TODO
			// Should work but no...
			// http://projects.sergiodinislopes.pt/flexdatalist/
			// The add method should add a new keyword but here
			// the key isn't added to the flexdatalist entry
			//$('#search').flexdatalist('add', $entry.keyword);
		}
	}

	if ($.isFunction($.fn.jstree)) {
		$("#TOC")
			.jstree(true)
			.show_all();
		$("#TOC").jstree("search", $entry.keyword);
	}

	return true;
}

/**
 * Rerun the search but avoid to use the cache
 * This by setting the disable_cache=1 parameter on the querystring
 */
function fnPluginTaskSearchClearCache() {
	// Remember the old URL
	var $old = marknotes.search.disable_cache;

	// Disable the cache
	marknotes.search.disable_cache = 1;
	fnPluginTaskSearch_resetSearchURL();

	$("#TOC")
		.jstree(true)
		.show_all();
	$("#TOC").jstree("search", $("#search").val());

	// Reset it
	marknotes.search.disable_cache = $old;
	fnPluginTaskSearch_resetSearchURL();

	return true;
}
