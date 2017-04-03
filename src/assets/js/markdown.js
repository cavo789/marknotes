/* global markdown, custominiFiles, customafterDisplay */

/*  Allow to easily access to querystring parameter like alert(QueryString.ParamName); */
var QueryString = function () {
	// This function is anonymous, is executed immediately and
	// the return value is assigned to QueryString!
	var query_string = {};
	var query = window.location.search.substring(1);
	var vars = query.split("&");
	for (var i = 0; i < vars.length; i++) {
		var pair = vars[i].split("=");
		// If first entry with this name
		if (typeof query_string[pair[0]] === "undefined") {
			query_string[pair[0]] = decodeURIComponent(pair[1]);
			// If second entry with this name
		} else if (typeof query_string[pair[0]] === "string") {
			var arr = [query_string[pair[0]], decodeURIComponent(pair[1])];
			query_string[pair[0]] = arr;
			// If third or later entry with this name
		} else {
			query_string[pair[0]].push(decodeURIComponent(pair[1]));
		}
	}
	return query_string;
}();

// http://stackoverflow.com/a/11247412
// Check if an array contains a specific value
Array.prototype.contains = function (v) {
	for (var i = 0; i < this.length; i++) {
		if (this[i] === v) {
			return true;
		}
	}
	return false;
};
// Extract unique values of an array
Array.prototype.unique = function () {
	var arr = [];
	for (var i = 0; i < this.length; i++) {
		if (!arr.contains(this[i])) {
			arr.push(this[i]);
		}
	}
	return arr;
};

// http://stackoverflow.com/a/2593661/1065340
RegExp.quote = function (str) {
	return (str + '')
		.replace(/[.?*+^$[\]\\(){}|-]/g, "\\$&");
};

$(document)
	.ready(function () {

		if (markdown.autoload === 1) {

			// On page entry, get the list of .md files on the server
			if (markdown.hasOwnProperty('message')) {
				Noty({
					message: markdown.message.loading_tree,
					type: 'info'
				});
			}

			ajaxify({
				task: 'listFiles',
				dataType: 'json',
				callback: 'initFiles(data)',
				useStore: markdown.settings.use_localcache
			});

			// Size correctly depending on screen resolution
			$('#TDM')
				.css('max-height', $(window)
					.height() - 30);
			$('#TDM')
				.css('min-height', $(window)
					.height() - 30);

			// Maximise the width of the table of contents i.e. the array with the list of files
			//$('#TOC').css('width', $('#TDM').width()-5);
			//$('#search').css('width', $('#TDM').width()-5);

			$('#CONTENT')
				.css('max-height', $(window)
					.height() - 10);
			$('#CONTENT')
				.css('min-height', $(window)
					.height() - 10);
			//$('#CONTENT').css('width', $('#CONTENT').width()-5);

			$('#search')
				.change(function (e) {
					$('#TOC')
						.jstree(true)
						.show_all();
					$('#TOC')
						.jstree('search', $(this)
							.val());
				});
		} // if (markdown.autoload === 1)
	});

/**
 * Run an ajax query
 * @param {json} $params
 *      task = which task should be fired
 *      param = (optional) parameter to provide for the calling task
 *      callback = (optional) Function to call once the ajax call is successfully done
 *
 * @returns {undefined}
 */
function ajaxify($params) {

	var $data = {};
	$data.task = (typeof $params.task === 'undefined') ? '' : $params.task;
	$data.param = (typeof $params.param === 'undefined') ? '' : $params.param;

	/*<!-- build:debug -->*/
	if (markdown.settings.debug) {
		console.log('ajaxify - Task=' + $data.task);
		console.log($params);
	}
	/*<!-- endbuild -->*/

	// Allow to use the navigator's localStorage ? By default, get the settings
	var $useStore = markdown.settings.use_localcache;

	// Then, if allowed, check the useStore parameter, if specified, get it's value.
	if ($useStore && (typeof $params.useStore !== 'undefined')) {
		$useStore = $params.useStore;
	}

	var $bAjax = true;

	// useStore should only be processed for specifics tasks like listFiles, display, ... but not all tasks
	var $arrUseStore = ['display', 'listFiles'];

	if (($useStore) && (jQuery.inArray($data.task, $arrUseStore) !== -1)) {
		// Using the cache system provided by store.js

		try {
			if (typeof store.get('task_' + $data.task) !== 'undefined') {
				$bAjax = false;
				data = store.get('task_' + $data.task);
				/*<!-- build:debug -->*/
				if (markdown.settings.debug) {
					console.log('Using localStorage to retrieve the previous result for [' + $data.task + ']');
				}
				/*<!-- endbuild -->*/
			}
		} catch (err) {
			console.warn(err.message);
		}
	} // if ($useStore)

	if ($bAjax) {
		$params.dataType = (typeof $params.dataType === 'undefined') ? 'html' : $params.dataType;

		if (typeof $params.param2 !== 'undefined') {
			$data.param2 = $params.param2;
		}
		if (typeof $params.param3 !== 'undefined') {
			$data.param3 = $params.param3;
		}

		var $target = '#' + (($params.target === 'undefined') ? 'TDM' : $params.target);

		$.ajax({
			beforeSend: function () {

				if (markdown.hasOwnProperty('message')) {
					$($target)
						.html('<div><span class="ajax_loading">&nbsp;</span><span style="font-style:italic;font-size:1.5em;">' + markdown.message.pleasewait + '</span></div>');
				}
			}, // beforeSend()
			async: true,
			cache: false,
			type: (markdown.settings.debug ? 'GET' : 'POST'),
			url: markdown.url,
			data: $data,
			datatype: $params.dataType,
			success: function (data) {

				if ($useStore) {
					store.set('task_' + $data.task, data);
				}

				if ($params.dataType === 'html') {

					/*<!-- build:debug -->*/
					if (markdown.settings.debug) {
						console.log('Output the result into target area');
					}
					/*<!-- endbuild -->*/

					$($target)
						.html(data);
				}

				/* jshint ignore:start */
				var $callback = ($params.callback === undefined) ? '' : $params.callback;
				if ($callback !== '') {
					/*<!-- build:debug -->*/
					if (markdown.settings.debug) {
						console.log('Run the callback function : ' + $params.callback);
					}
					/*<!-- endbuild -->*/
					eval($callback);
				}
				/* jshint ignore:end */
			}
		}); // $.ajax()
	} else {
		/* jshint ignore:start */
		var $callback = ($params.callback === undefined) ? '' : $params.callback;
		if ($callback !== '') {
			eval($callback);
		}
		/* jshint ignore:end */
	}

}

/**
 * Add a new entry in the search box (append and not replace)
 *
 * @param {json} $entry
 *      keyword           : the value to add in the search area
 *      reset (optional)  : if true, the search area will be resetted before (so only search for the new keyword)
 *
 * @returns {Boolean}
 */
function addSearchEntry($entry) {

	$bReset = (($entry.reset === 'undefined') ? false : $entry.reset);

	$current = $('#search')
		.val()
		.trim();

	if (($current !== '') && ($bReset === false)) {
		// Append the new keyword only when bReset is not set or set to False
		var values = $current.split(',');
		values.push($entry.keyword);
		$('#search')
			.val(values.join(','));
	} else {
		$('#search')
			.val($entry.keyword);
	}

	return true;

}

/**
 * The ajax request has returned the list of files.  Build the table and initialize the #TOC DOM object
 *
 * @param {json} $data  The return of the JSON returned by index.php?task=listFiles
 * @returns {Boolean}
 */
function initFiles($data) {

	var $msg = '';

	if (typeof $data === 'undefined') {
		$msg = markdown.message.json_error;
		Noty({
			message: $msg.replace('%s', 'listFiles'),
			type: 'error'
		});
		return false;
	}

	/*<!-- build:debug -->*/
	//if (markdown.settings.debug) console.log($data.count);
	/*<!-- endbuild -->*/

	try {
		if ($data.hasOwnProperty('count')) {
			// Display the number of returned files
			$msg = markdown.message.filesfound;
			Noty({
				message: $msg.replace('%s', $data.count),
				type: 'notification'
			});
		}
	} catch (err) {
		console.warn(err.message);
		/*<!-- build:debug -->*/
		if (markdown.settings.debug) {
			Noty({
				message: err.message,
				type: 'error'
			});
		}
		/*<!-- endbuild -->*/
	}

	jstree_init($data);

	// initialize the search area, thanks to the Flexdatalist plugin

	if ($.isFunction($.fn.flexdatalist)) {
		$('.flexdatalist')
			.flexdatalist({
				toggleSelected: true,
				minLength: 3,
				valueProperty: 'id',
				selectionRequired: false,
				visibleProperties: ["name", "type"],
				searchIn: 'name',
				data: 'index.php?task=tags',
				focusFirstResult: true,
				noResultsText: markdown.message.search_no_result,
				requestType: (markdown.settings.debug ? 'get' : 'post')
			});

		// Add automatic filtering if defined in the settings.json file
		if (markdown.settings.auto_tags !== '') {
			addSearchEntry({
				keyword: markdown.settings.auto_tags
			});
		}
	} // if ($.isFunction($.fn.flexdatalist))

	$('#search')
		.css('width', $('#TDM')
			.width() - 5);
	$('.flexdatalist-multiple')
		.css('width', $('.flexdatalist-multiple')
			.parent()
			.width() - 10)
		.show();

	// Interface : put the cursor immediatly in the edit box
	try {
		$('#search-flexdatalist')
			.focus();
	} catch (err) {
		console.warn(err.message);
	}

	// See if the custominiFiles() function has been defined and if so, call it
	try {
		if (typeof custominiFiles !== 'undefined' && $.isFunction(custominiFiles)) {
			custominiFiles();
		}
	} catch (err) {
		console.warn(err.message);
		/*<!-- build:debug -->*/
		if (markdown.settings.debug) {
			Noty({
				message: err.message,
				type: 'error'
			});
		}
		/*<!-- endbuild -->*/
	}

	return true;

}

/**
 * Initialize each action buttons of the displayed note.
 * These buttons should :
 *    - Have an id starting with "icon_xxxx" (f.i. id="icon_preview")
 *    - Have a data-task attribute           (f.i. data-task="preview")
 *
 * @returns {undefined}
 */
function initializeTasks() {

	// Initialise print preview plugin, should be done before clicking on the button
	if ($.isFunction($.fn.printPreview)) {
		try {
			$('[data-task="printer"]')
				.printPreview();
		} catch (err) {
			console.warn(err.message);
		}
	}

	// Get all DOM objects having a data-task attribute
	$("[data-task]")
		.click(function (event) {
			event.preventDefault();
			event.stopPropagation()();

			var $task = $(this)
				.data('task');

			var $fname = ($(this)
				.attr('data-file') ? $(this)
				.data('file') : '');
			var $tag = ($(this)
				.attr('data-tag') ? $(this)
				.data('tag')
				.replace('\\', '/') : '');

			var $arrNoCrypt = ['pdf', 'slideshow', 'window'];
			if (($fname !== '') && (jQuery.inArray($task, $arrNoCrypt) === -1)) {
				// Don't base64 the filename when the tasks are 'slideshow' or 'window'
				$fname = window.btoa(encodeURIComponent(JSON.stringify($fname)));
			}

			/*<!-- build:debug -->*/
			if (markdown.settings.debug) {
				console.log('Running task [' + $task + '] for [' + $fname + ']');
			}
			/*<!-- endbuild -->*/

			switch ($task) {

			case 'clear':

				ajaxify({
					task: $task,
					callback: 'cleanCache();',
					target: 'CONTENT'
				});
				break;

			case 'clipboard':

				// Initialize the Copy into the clipboard button, See https://clipboardjs.com/

				if (typeof Clipboard === 'function') {
					var clipboard = new Clipboard('*[data-task="clipboard"]');
					clipboard.on('success', function (e) {
						e.clearSelection();
					});
					Noty({
						message: markdown.message.copy_clipboard_done,
						type: 'success'
					});
				} else {
					$(this)
						.remove();
				}
				break;

			case 'display':

				// Display the file by calling the Ajax function. Display its content in the CONTENT DOM element

				ajaxify({
					task: $task,
					param: $fname,
					callback: 'afterDisplay($data.param)',
					target: 'CONTENT'
				});
				break;

			case 'edit':

				ajaxify({
					task: $task,
					param: $fname,
					callback: 'afterEdit($data.param)',
					target: 'CONTENT'
				});

				break;

			case 'fullscreen':

				toggleFullScreen();

				break;

			case 'link_note':

				// Initialize the Copy into the clipboard button, See https://clipboardjs.com/

				if (typeof Clipboard === 'function') {
					new Clipboard('*[data-task="link_note"]');
					Noty({
						message: markdown.message.copy_link_done,
						type: 'success'
					});
				} else {
					$(this)
						.remove();
				}
				break;

			case 'pdf':

				window.open($fname);
				break;

			case 'printer':
				break;

			case 'slideshow':

				window.open($fname); // $fname is something like folder/subfolder/hotes.html?format=slides i.e. with the ?format=slides parameter
				break;

			case 'tag':

				/*<!-- build:debug -->*/
				if (markdown.settings.debug) {
					console.log('... filter on [' + $tag + ']');
				}
				/*<!-- endbuild -->*/

				addSearchEntry({
					keyword: $tag,
					reset: true
				});
				break;

			case 'timeline':

				window.open(markdown.webroot + 'timeline.html');
				break;

			case 'window':

				window.open($fname);
				break;

			default:

				console.warn('Sorry, unknown task [' + $task + ']');

			} // switch($task)

		}); // $("[data-task]").click(function()

	return true;

}

/**
 * Empty the localStorage cache and the session on the server; reload then the page
 */
function cleanCache() {

	// Empty the localStorage too
	if (markdown.settings.use_localcache) {
		try {
			store.clearAll();
		} catch (err) {
			console.warn(err.message);
		}
	}

	location.reload();

	Noty({
		message: markdown.message.settings_clean_done,
		type: 'success'
	});

	return;

}

/**
 * If a note contains a link to an another note, use ajax and not normal links
 * @returns {Boolean}
 */
function replaceLinksToOtherNotes() {

	try {
		/*<!-- build:debug -->*/
		if (markdown.settings.debug) {
			console.log(' ... Replace internal links to notes (function replaceLinksToOtherNotes())');
		}
		/*<!-- endbuild -->*/

		var $text = $('#CONTENT')
			.html();

		// Retrieve the URL of this page but only the host and script name, no querystring parameter (f.i. "http://localhost:8080/notes/index.php")
		var $currentURL = location.protocol + '//' + location.host + location.pathname;

		// Define a regex for matching every links in the displayed note pointing to that URL
		var RegEx = new RegExp('<a href=[\'|"]' + RegExp.quote($currentURL) + '\?.*>(.*)<\/a>', 'i');

		var $nodes = RegEx.exec(RegExp.quote($text));

		var $param = [];
		var $fname = '';

		while ($nodes !== null) {
			$param = $nodes[0].match(/param=(.*)['|"]/); // Retrieve the "param" parameter which is the encrypted filename that should be displayed
			$fname = JSON.parse(decodeURIComponent(window.atob($param[1])));

			$sNodes = '<span class="note" title="' + markdown.message.display_that_note + '" data-task="display" data-file="' + $fname + '">' + $nodes[1] + '</span>';

			$text = $text.replace($nodes[0], $sNodes);

			$nodes = RegEx.exec($text);
		} // while

		// Set the new page content
		$('#CONTENT')
			.html($text);
	} catch (err) {
		console.warn(err.message);
	}

	return;

}

/**
 * Try to find tags i.ex. §some_tag  (the tag is prefixed by the § character because # is meaningfull in markdown
 *
 * @returns {undefined}
 */
function addLinksToTags() {

	/*<!-- build:debug -->*/
	if (markdown.settings.debug) {
		console.log(' ... add links to tags (function addLinksToTags())');
	}
	/*<!-- endbuild -->*/

	var $text = $('#CONTENT')
		.html();

	// markdown.settings.prefix_tag is set by markdown.php and, by default, equal to §
	// Every words prefixed by § will be considered as a tag just like "#word" in social network.
	// The # character is used by markdown language so, use an another one.
	try {
		// Explanation of the regex
		//
		// ( |,|;|\\.|\\n|\\r|\\t)*       Before : Allowed characters before the tag : a space, comma, dot comma, dot, carriage return, linefeed or tab, one or more (f.i. a carriage return and a linefeed are matched)
		// markdown.settings.prefix_tag   Symbol : Match the § character
		// ([a-zA-Z0-9]+)                 Tag    : a word composed of letters and figures, can also contains dot (like ".htaccess")
		// ( |,|;|\\.|\\n|\\r|\\t|$)      Afeter : Allowed characters after the tag : space, comma, dot comma, dot, carriage return, linefeed or tab


		var RegEx = new RegExp('( |,|;|\\.|\\n|\\r|\\t)*' + markdown.settings.prefix_tag + '([(\\&amp;)\\.a-zA-Z0-9\\_\\-]+)( |,|;|\\.|\\n|\\r|\\t)*', 'i');
		/*<!-- build:debug -->*/
		if (markdown.settings.debug) {
			console.log('     RegEx for finding tags : ' + RegEx);
		}
		/*<!-- endbuild -->*/

		var $tags = RegEx.exec($text);

		while ($tags !== null) {
			/*<!-- build:debug -->*/
			if (markdown.settings.debug) {
				console.log("     Process tag " + $tags[0]);
			}
			/*<!-- endbuild -->*/

			$sTags =
				(($tags[1] !== undefined) ? $tags[1] : '') + // Before the span
				'<span class="tag" title="' + markdown.message.apply_filter_tag + '" data-task="tag" data-tag="' + $tags[2] + '">' + $tags[2] + '</span>' + // The span for tagging the word
				(($tags[3] !== undefined) ? $tags[3] : ''); // After the span

			try {
				// The tag can, perhaps, contains special characters like an ending parenthese so quote the tag
				$text = $text.replace(new RegExp(RegExp.quote($tags[0]), "g"), $sTags);
				$tags = RegEx.exec($text);
			} catch (err) {
				$tags = null;
				console.warn(err.lineNumber + '----' + err.message);
			}
		} // while

		// Set the new page content
		$('#CONTENT')
			.html($text);
	} catch (err) {
		console.warn(err.message);
	}

	return;
}

/**
 * Force links that points on the same server (localhost) to be opened in a new window
 * @returns {Boolean}
 */
function forceNewWindow() {

	/*<!-- build:debug -->*/
	if (markdown.settings.debug) {
		console.log(' ... force new window by clicking on links pointing to an another server (function forceNewWindow())');
	}
	/*<!-- endbuild -->*/

	var $currentURL = location.protocol + '//' + location.host;

	$('a[href^="http:"], a[href^="https:"]')
		.not('[href^="' + $currentURL + '/"]')
		.attr('target', '_blank');

	return true;

}

/**
 * Add icons to .pdf, .xls, .doc, ... hyperlinks and for some extensions (like log, md, pdf, txt, ...) force to open in a new window
 */
function addIcons() {

	/*<!-- build:debug -->*/
	if (markdown.settings.debug) {
		console.log(' ... add icons to some filetype (function addIcons())');
	}
	/*<!-- endbuild -->*/

	try {
		$("a")
			.each(function () {

				$href = $(this)
					.attr("href");
				$sAnchor = $(this)
					.text();

				if (/\.doc[x]?$/i.test($href)) {
					// Word document
					$sAnchor += '<i class="icon_file fa fa-file-word-o" aria-hidden="true"></i>';
					$(this)
						.html($sAnchor)
						.addClass('download');
				} else if (/\.(log|md|markdown|txt)$/i.test($href)) {
					// LOG - Open it in a new windows and not in the current one
					$sAnchor += '<i class="icon_file fa fa-file-text-o" aria-hidden="true"></i>';
					$(this)
						.html($sAnchor)
						.addClass('download-link')
						.attr('target', '_blank');
				} else if (/\.pdf$/i.test($href)) {
					// PDF - Open it in a new windows and not in the current one
					$sAnchor += '<i class="icon_file fa fa-file-pdf-o" aria-hidden="true"></i>';
					$(this)
						.html($sAnchor)
						.addClass('download-link')
						.attr('target', '_blank');
				} else if (/\.ppt[x]?$/i.test($href)) {
					// Powerpoint
					$sAnchor += '<i class="icon_file fa fa-file-powerpoint-o" aria-hidden="true"></i>';
					$(this)
						.html($sAnchor)
						.addClass('download-link');
				} else if (/\.xls[m|x]?$/i.test($href)) {
					// Excel
					$sAnchor += '<i class="icon_file fa fa-file-excel-o" aria-hidden="true"></i>';
					$(this)
						.html($sAnchor)
						.addClass('download-link');
				} else if (/\.(7z|gzip|tar|zip)$/i.test($href)) {
					// Archive
					$sAnchor += '<i class="icon_file fa fa-file-archive-o" aria-hidden="true"></i>';
					$(this)
						.html($sAnchor)
						.addClass('download-link');
				}

			});
	} catch (err) {
		console.warn(err.message);
	}

	return true;

}

/**
 * Add the "table" class to any <table>
 *
 * @returns {undefined}
 */
function NiceTable() {

	/*<!-- build:debug -->*/
	if (markdown.settings.debug) {
		console.log(' ... NiceTable : set style to Bootstrap and use the DataTable jQuery Plugin (function NiceTable())');
	}
	/*<!-- endbuild -->*/

	try {
		$("table")
			.each(function () {
				$(this)
					.addClass('table table-striped table-hover table-bordered');

				if ($.isFunction($.fn.DataTable)) {
					$(this)
						.addClass('display');
					$(this)
						.DataTable({
							scrollY: "50vh", // 50%
							scrollCollapse: true,
							info: true,
							//order: [[ 0, "asc" ],[ 1, "asc" ],[ 2, "asc" ],[ 3, "asc" ]],
							lengthMenu: [
								[10, 25, 50, -1],
								[10, 25, 50, "All"]
							],
							language: {
								decimal: '.',
								thousands: ',',
								url: 'libs/DataTables/' + markdown.settings.language + '.json'
							}
						});
				}
			});
	} catch (err) {
		console.warn(err.message);
	}

	return true;

}

/**
 * Called after the ajax "display" request, the file is almost displayed
 */
function afterDisplay($fname) {

	try {

		/*<!-- build:debug -->*/
		if (markdown.settings.debug) {
			console.log('In function afterDisplay()');
		}
		/*<!-- endbuild -->*/

		// Remove functionnalities if jQuery librairies are not loaded
		if (typeof Clipboard !== 'function') {
			$('[data-task="clipboard"]')
				.remove();
		}

		if (!$.isFunction($.fn.printPreview)) {
			$('[data-task="printer"]')
				.remove();
		}

		// Try to detect email, urls, ... not yet in a <a> tag and so ... linkify them
		if ($.isFunction($.fn.linkify)) {
			/*<!-- build:debug -->*/
			if (markdown.settings.debug) {
				console.log(' ... linkify plain text (in function afterDisplay())');
			}
			/*<!-- endbuild -->*/
			$('page')
				.linkify();
		}

		if (typeof Prism === 'object') {
			Prism.highlightAll();
		}

		// If a note contains a link to an another note, use ajax and not normal links
		replaceLinksToOtherNotes();

		// Add links to tags
		addLinksToTags();

		// Force links that points on the same server (localhost) to be opened in a new window
		forceNewWindow();

		// Add icons to .pdf, .xls, .doc, ... hyperlinks
		addIcons();

		// Make table nicer
		NiceTable();

		// Initialize each action buttons of the displayed note
		initializeTasks();

		// Retrieve the heading 1 from the loaded file
		var $title = $('#CONTENT h1')
			.text();
		if ($title !== '') {
			$('title')
				.text($title);
		}

		$fname = $('div.filename')
			.text();
		if ($fname !== '') {
			$('#footer')
				.html('<strong style="text-transform:uppercase;">' + $fname + '</strong>');
		}

		// Interface : put the cursor immediatly in the edit box
		try {
			$('#search')
				.focus();

			// Get the searched keywords.  Apply the restriction on the size.
			var $searchKeywords = $('#search')
				.val()
				.substr(0, markdown.settings.search_max_width)
				.trim();

			if ($searchKeywords !== '') {
				if ($.isFunction($.fn.highlight)) {
					$("#CONTENT")
						.highlight($searchKeywords);
				}
			}
		} catch (err) {
			/*<!-- build:debug -->*/
			if (markdown.settings.debug) {
				console.warn(err.message);
			}
			/*<!-- endbuild -->*/
		}

		// See if the customafterDisplay() function has been defined and if so, call it
		if (typeof customafterDisplay !== 'undefined' && $.isFunction(customafterDisplay)) {
			customafterDisplay($fname);
		}
	} catch (err) {
		console.warn(err.message);
	}

	// Just for esthetics purposes
	$('#CONTENT')
		.fadeOut(1)
		.fadeIn(3);

	return true;

}

/**
 * EDIT MODE - Render the textarea in a nice editor
 *
 * @param {type} $fname   Filename
 * @returns {Boolean}
 */
function afterEdit($fname) {

	/*<!-- build:debug -->*/
	if (markdown.settings.debug) {
		console.log('In function afterEdit()');
	}
	/*<!-- endbuild -->*/

	// Create the Simple Markdown Editor
	// @link https://github.com/NextStepWebs/simplemde-markdown-editor

	var simplemde = new SimpleMDE({
		autoDownloadFontAwesome: false,
		autofocus: true,
		element: document.getElementById("sourceMarkDown"),
		indentWithTabs: false,
		codeSyntaxHighlighting: false,
		toolbar: [{
				// Add a custom button for saving
				name: "Save",
				action: function customFunction(editor) {
					buttonSave($fname, simplemde.value());
				},
				className: "fa fa-floppy-o",
				title: markdown.message.button_save
			},
			{
				// Encrypt
				name: "Encrypt",
				action: function customFunction(editor) {
					buttonEncrypt(editor);
				},
				className: "fa fa-user-secret",
				title: markdown.message.button_encrypt
			},
			"|",
			{
				// Add a custom button for saving
				name: "Exit",
				action: function customFunction(editor) {
					$('#sourceMarkDown')
						.parent()
						.hide();
					ajaxify({
						task: 'display',
						param: $fname,
						callback: 'afterDisplay($data.param)',
						target: 'CONTENT'
					});
				},
				className: "fa fa-sign-out",
				title: markdown.message.button_exit_edit_mode
			},
			"|", "preview", "side-by-side", "fullscreen", "|",
			"bold", "italic", "strikethrough", "|", "heading", "heading-smaller", "heading-bigger", "|", "heading-1", "heading-2", "heading-3", "|",
			"code", "quote", "unordered-list", "ordered-list", "clean-block", "|", "link", "image", "table", "horizontal-rule"
		] // toolbar
	});

	$('.editor-toolbar')
		.addClass('fa-2x');

	return true;

}

/**
 * EDIT MODE - Save the new content.  Called by the "Save" button of the simplemde editor, initialized in the afterEdit function)
 *
 * @param {type} $fname        Filename
 * @param {type} $markdown     The new content
 * @returns {boolean}
 */
function buttonSave($fname, $markdown) {

	var $data = {};
	$data.task = 'save';
	$data.param = $fname;
	$data.markdown = window.btoa(encodeURIComponent(JSON.stringify($markdown)));

	$.ajax({
		async: true,
		type: 'POST',
		url: markdown.url,
		data: $data,
		datatype: 'json',
		success: function (data) {
			Noty({
				message: data.status.message,
				type: (data.status.success == 1 ? 'success' : 'error')
			});
		}
	}); // $.ajax()

	return true;

}

/**
 * EDIT MODE - Encrypt the selection.  Add the <encrypt> tag
 *
 * @returns {boolean}
 */
function buttonEncrypt(editor) {

	var cm = editor.codemirror;
	var output = '';
	var selectedText = cm.getSelection();
	var text = selectedText || 'your_confidential_info';

	output = '<encrypt>' + text + '</encrypt>';
	cm.replaceSelection(output);

}

/**
 *
 * @param {json} $params
 *      message : the message to display
 *      type    : success, error, warning, information, notification
 *
 * @returns {undefined}
 */
function Noty($params) {

	if ($.isFunction($.fn.noty)) {
		if ($params.message === '') {
			return false;
		}

		$type = (($params.type === 'undefined') ? 'info' : $params.type);

		// More options, see http://ned.im/noty/options.html
		var n = noty({
			text: $params.message,
			theme: 'relax',
			timeout: 2400,
			layout: 'bottomRight',
			type: $type
		}); // noty()
	}

}
