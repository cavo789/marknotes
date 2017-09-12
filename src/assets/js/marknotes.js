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

$(document).ready(function () {

	if (marknotes.autoload === 1) {

		if ($.isFunction($.fn.toolbar)) {

			// http://paulkinzett.github.io/toolbar/
			$("#toolbar-app")
				.toolbar({
					"content": "#toolbar-app-options",
					"position": "bottom",
					"event": "click",
					"style": "default",
					"hideOnClick": true
				});
		}

		// Be sure that this plugin is well part of the current Prism installation and is loaded
		if (Prism.plugins.NormalizeWhitespace) {
			Prism.plugins.NormalizeWhitespace.setDefaults({
				'break-lines': 120 // Soft wrap after 120 chars
			});
		}

		// On page entry, get the list of .md files on the server
		if (marknotes.hasOwnProperty('message')) {
			Noty({
				message: marknotes.message.loading_tree,
				type: 'info'
			});
		}

		ajaxify({
			task: 'listFiles',
			dataType: 'json',
			callback: 'initFiles(data)',
			useStore: marknotes.settings.use_localcache
		});

	} // if (marknotes.autoload === 1)

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

	if (typeof $params.filename !== 'undefined') {

		// Retrieve the filename that should be displayed (like sitemap.xml f.i.)
		$data.filename = $params.filename;

		if ($data.task === '') {
			// A filename has been specified (like f.i. timeline.json).
			// So, the task in this case is "getFile" i.e. just access to a file
			$data.task = 'getFile';
		}

		if (typeof $params.dataType === 'undefined') {

			// Derive the data type based on the extension
			// http://stackoverflow.com/a/680982

			var re = /(?:\.([^.]+))?$/;
			$params.dataType = re.exec($data.filename)[1];
		}
	} // if ($data.filename !== '')

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('ajaxify - Task=' + $data.task + ' / Param=' + $data.param);
		console.log($params);
	}
	/*<!-- endbuild -->*/

	// Allow to use the navigator's localStorage ? By default, get the settings
	var $useStore = marknotes.settings.use_localcache;

	// Then, if allowed, check the useStore parameter, if specified, get it's value.
	if ($useStore && (typeof $params.useStore !== 'undefined')) {
		$useStore = $params.useStore;
	}

	var $bAjax = true;

	// useStore should only be processed for specifics tasks
	// like listFiles, display, ... but not all tasks
	var $arrUseStore = ['display', 'getFile', 'listFiles'];

	if (($useStore) && (jQuery.inArray($data.task, $arrUseStore) !== -1)) {
		// Using the cache system provided by store.js

		try {
			if (typeof store.get('task_' + $data.task) !== 'undefined') {

				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.log('Using localStorage to retrieve the previous result for [' + $data.task + ']');
				}
				/*<!-- endbuild -->*/

				data = store.get('task_' + $data.task);

				if (typeof (data) !== undefined) {

					if ($data.task === 'listFiles') {
						// Don't reload if the list of files unless the data object
						// doesn't contains files
						if (data.hasOwnProperty("tree")) {
							if (data.tree.children.length > 0) {
								$bAjax = false;
							}
						}
					}
				}

			}
		} catch (err) {
			console.warn(err.message);
		}
	} // if ($useStore)

	if ($bAjax) {
		$params.dataType = (typeof $params.dataType === 'undefined') ? 'html' : $params.dataType;

		// Info : the oldname and the type parameters are set by the Files plugins
		if (typeof $params.oldname !== 'undefined') {
			$data.oldname = $params.oldname;
		}
		if (typeof $params.type !== 'undefined') {
			$data.type = $params.type;
		}

		var $target = '#' + (($params.target === 'undefined') ? 'sidebar' : $params.target);

		$.ajax({
			beforeSend: function () {

				if (marknotes.hasOwnProperty('message')) {
					$($target)
						.html('<div><span class="ajax_loading">&nbsp;</span><span style="font-style:italic;font-size:1.5em;">' + marknotes.message.pleasewait + '</span></div>');
				}
			}, // beforeSend()
			async: true,
			cache: false,
			type: (marknotes.settings.debug ? 'GET' : 'POST'),
			url: (typeof $data.filename !== 'undefined') ? $data.filename : marknotes.url,
			data: (typeof $data.filename !== 'undefined') ? '' : $data,
			datatype: $params.dataType,
			success: function (data) {

				// If "select_node" has been set by the caller (f.i. the treeview plugin
				// add this attribute in the fnPluginTaskTreeView_reload() function when
				// the treeview should be reloaded and, then, a specific node should be
				// selected (f.i. after the creation of a new note)
				if (typeof $params.select_node !== 'undefined') {

					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						console.log('   Add data.select_node attribute');
					}
					/*<!-- endbuild -->*/
					data.select_node = $params.select_node;
					console.log(data);
				}
				if ($useStore) {
					store.set('task_' + $data.task, data);
				}

				if ($params.dataType === 'html') {

					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						console.log('Output the result into target area');
					}
					/*<!-- endbuild -->*/

					$($target).html(data);
				}

				/* jshint ignore:start */
				var $callback = ($params.callback === undefined) ? '' : $params.callback;
				if ($callback !== '') {
					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						console.log('Run the callback function : ' + $params.callback);
					}
					/*<!-- endbuild -->*/
					eval($callback);

				} // if ($callback !== '')
				/* jshint ignore:end */

			}, // success
			error: function (Request, textStatus, errorThrown) {
				// Display an error message to inform the user about the problem
				var $msg = '<div class="bg-danger text-danger img-rounded" style="margin-top:25px;padding:10px;">';
				$msg = $msg + '<strong>An error has occured :</strong><br/>';
				$msg = $msg + 'Internal status: ' + textStatus + '<br/>';
				$msg = $msg + 'HTTP Status: ' + Request.status + ' (' + Request.statusText + ')<br/>';
				$msg = $msg + 'XHR ReadyState: ' + Request.readyState + '<br/>';
				$msg = $msg + 'Raw server response:<br/>' + Request.responseText + '<br/>';
				$msg = $msg + '</div>';
				$($target).html($msg);
			} // error
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
 * The ajax request has returned the list of files.  Build the table and initialize
 * the #TOC DOM object
 *
 * @param {json} $data  The return of the JSON returned by index.php?task=listFiles
 * @returns {Boolean}
 */
function initFiles($data) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('******** initFiles - START ***************');
		console.log($data);
	}
	/*<!-- endbuild -->*/

	var $msg = '';

	if (typeof $data === 'undefined') {
		$msg = marknotes.message.json_error;
		Noty({
			message: $msg.replace('%s', 'listFiles'),
			type: 'error'
		});
		return false;
	}

	try {
		if ($data.hasOwnProperty('count')) {
			// Display the number of returned files
			$msg = marknotes.message.filesfound;
			Noty({
				message: $msg.replace('%s', $data.count),
				type: 'notification'
			});
		}
	} catch (err) {
		console.warn(err.message);
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			Noty({
				message: err.message,
				type: 'error'
			});
		}
		/*<!-- endbuild -->*/
	}

	jstree_init($data);

	// See if the custominiFiles() function has been defined and if so, call it
	try {
		if (typeof custominiFiles !== 'undefined' && $.isFunction(custominiFiles)) {
			custominiFiles();
		}
	} catch (err) {
		console.warn(err.message);
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			Noty({
				message: err.message,
				type: 'error'
			});
		}
		/*<!-- endbuild -->*/
	}

	// Call javascript functions there were added by plugins
	runPluginsFunctions();

	// Automatically select a specific node when $data.select_node exists
	if ($data.hasOwnProperty('select_node')) {

		// Needed otherwise the jstree's state plugin will reset the selected node
		try {
			// Select the node
			$('#TOC').jstree('select_node', $data.select_node);
			$('#TOC').jstree(true).save_state();
		} catch (e) {
			console.warn(err.message);
		} finally {}

	}

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('******** initFiles - END ***************');
	}
	/*<!-- endbuild -->*/

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
			$('[data-task="printer"]').printPreview();
		} catch (err) {
			console.warn(err.message);
		}
	}

	// Get all DOM objects having a data-task attribute
	$("[data-task]").click(function (event) {
		//event.preventDefault();

		var $task = $(this).data('task');
		if ($task.substring(0, 23) !== 'fnPluginButtonClipboard') {
			// DON't STOP PROPAGATION, WILL BREAK THE Clipboard PLUGIN
			event.stopPropagation();
			event.stopImmediatePropagation();
		}

		var $fname = $(this).attr('data-file') ? $(this).data('file') : '';

		var $tag = ($(this)
			.attr('data-tag') ? $(this)
			.data('tag')
			.replace('\\', '/') : '');

		if (($fname !== '') && ($task !== 'file')) {
			// Don't base64 the filename when the task is 'file', the URL (file) should
			// remains readable
			$fname = window.btoa(encodeURIComponent(JSON.stringify($fname)));
		}

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('Running task [' + $task + '] for [' + $fname + ']');
		}
		/*<!-- endbuild -->*/

		switch ($task) {

		case 'display':

			// Display the file by calling the Ajax function.
			// Display its content in the CONTENT DOM element

			ajaxify({
				task: $task,
				param: $fname,
				callback: 'afterDisplay($data.param)',
				target: 'CONTENT'
			});
			break;

		case 'file':

			window.open($fname);
			break;

		case 'fullscreen':

			toggleFullScreen();

			break;

		case 'printer':
			break;

		default:

			// The task is perhaps a function that was added by a plugin
			// For instance the login form is a plugin and the data-task is set to
			// "fnPluginTaskLogin", defined in login.js => try and call this function

			try {

				var fn = window[$task];

				// is object a function?
				if (typeof fn === "function") {

					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						console.log('Running the plugin function  [' + $task + ']');
					}
					/*<!-- endbuild -->*/

					// $task is something like "fnPluginTaskLogin", we should add the ()
					// so javascript know it's a function

					// Give parameters to the function
					var $params = {};
					$params.fname = $fname; // give the filename to the function
					fn($params);

				} else {
					console.warn('Sorry, unknown task [' + $task + ']');
				}
			} catch (e) {
				console.warn('Problem when trying to evaluate [' + $task + '][' + e.message + ']');
			}

		} // switch($task)

	}); // $("[data-task]").click(function()

	return true;

}

/**
 * If a note contains a link to an another note, use ajax and not normal links
 * @returns {Boolean}
 */
function replaceLinksToOtherNotes() {

	try {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
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

			$sNodes = '<span class="note" title="' + marknotes.message.display_that_note + '" data-task="display" data-file="' + $fname + '">' + $nodes[1] + '</span>';

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
 * Force links that points on a another server to be opened in a new window
 * @returns {Boolean}
 */
function forceNewWindow() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
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
	if (marknotes.settings.debug) {
		console.log(' ... add icons to some filetype (function addIcons())');
	}
	/*<!-- endbuild -->*/

	try {
		$("a").each(function () {

			$href = $(this).attr("href");
			$sAnchor = $(this).text();

			if (/\.doc[x]?$/i.test($href)) {
				// Word document
				$sAnchor += '<i class="icon_file fa fa-file-word-o" aria-hidden="true"></i>';
				$(this).html($sAnchor).addClass('download');
			} else if (/\.(log|md|markdown|txt)$/i.test($href)) {
				// LOG - Open it in a new windows and not in the current one
				$sAnchor += '<i class="icon_file fa fa-file-text-o" aria-hidden="true"></i>';
				$(this).html($sAnchor).addClass('download-link').attr('target', '_blank');
			} else if (/\.pdf$/i.test($href)) {
				// PDF - Open it in a new windows and not in the current one
				$sAnchor += '<i class="icon_file fa fa-file-pdf-o" aria-hidden="true"></i>';
				$(this).html($sAnchor).addClass('download-link').attr('target', '_blank');
			} else if (/\.ppt[x]?$/i.test($href)) {
				// Powerpoint
				$sAnchor += '<i class="icon_file fa fa-file-powerpoint-o" aria-hidden="true"></i>';
				$(this).html($sAnchor).addClass('download-link');
			} else if (/\.xls[m|x]?$/i.test($href)) {
				// Excel
				$sAnchor += '<i class="icon_file fa fa-file-excel-o" aria-hidden="true"></i>';
				$(this).html($sAnchor).addClass('download-link');
			} else if (/\.(7z|gzip|tar|zip)$/i.test($href)) {
				// Archive
				$sAnchor += '<i class="icon_file fa fa-file-archive-o" aria-hidden="true"></i>';
				$(this).html($sAnchor).addClass('download-link');
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
		if (marknotes.settings.debug) {
			console.log('In function afterDisplay()');
		}
		/*<!-- endbuild -->*/

		// Remove functionnalities if jQuery librairies are not loaded
		if (typeof Clipboard !== 'function') {
			$('[data-task="clipboard"]').remove();
		}

		if (!$.isFunction($.fn.printPreview)) {
			$('[data-task="printer"]').remove();
		}

		// If a note contains a link to an another note, use ajax and not normal links
		replaceLinksToOtherNotes();

		// Force links that points on the same server (localhost) to be opened in a new window
		forceNewWindow();

		// Add icons to .pdf, .xls, .doc, ... hyperlinks
		//addIcons();

		// Initialize each action buttons of the displayed note
		initializeTasks();

		// Retrieve the heading 1 from the loaded file
		var $title = $('#CONTENT h1').text();
		if ($title !== '') {
			$('title').text($title);
		}

		$fname = $('div.filename').text();
		if ($fname !== '') {
			$('#footer').html('<strong style="text-transform:uppercase;">' + $fname + '</strong>');
		}

		// Interface : put the cursor immediatly in the edit box
		try {
			// The function is defined in a plugin, be sure that the plugin has been loaded
			// and the function therefore exists
			if (typeof fnPluginTaskSearch_afterDisplay === "function") {
				fnPluginTaskSearch_afterDisplay();
			}
		} catch (err) {
			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
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

	// Call javascript functions there were added by plugins
	runPluginsFunctions();

	// Just for esthetics purposes
	$('#CONTENT').fadeOut(1).fadeIn(3);

	return true;

}

/**
 * The array marknotes.arrPluginsFct is a global array and will be initialized by
 * the differents plugins (like Bootstrap, DataTable, ...) and will contains functions name.
 *
 * For instance : the file /marknotes/plugins/content/html/bootstrap/bootstrap.js contains this line :
 *    marknotes.arrPluginsFct.push("PluginBootstrap");
 *
 * This to tell to this code that the PluginBootstrap function should be fired once the note
 * is displayed.  So, let's do it
 */
function runPluginsFunctions() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('Running plugins functions - START');
		console.log(marknotes.arrPluginsFct);
	}
	/*<!-- endbuild -->*/

	try {

		// Duplicate the marknotes.arrPluginsFct array (use slice() for this)
		// because some functions like f.i. fnPluginTaskTreeView_init() (in plugin task treeview)
		// should be called only once and, that function, remove its entry from the
		// marknotes.arrPluginsFct array.  Be sure to process every items so copy the array

		$arrFct = marknotes.arrPluginsFct.slice();

		var $j = $arrFct.length;
		for (var $i = 0, $j = $arrFct.length; $i < $j; $i++) {

			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log('   call ' + ($i + 1) + '/' + $j + ' : ' + $arrFct[$i]);
			}
			/*<!-- endbuild -->*/

			// As explained here : https://www.sitepoint.com/call-javascript-function-string-without-using-eval/
			fn = window[$arrFct[$i]];

			if (typeof fn === "function") {
				fn();
			}

		}
	} catch (err) {
		console.warn(err.message);
	}

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('Running plugins functions - END');
	}
	/*<!-- endbuild -->*/

	return true;

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
