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

			$("#toolbar-app")
			.toolbar({
				content: "#toolbar-app-options",
				position: "bottom",
				event: "click",
				style: "default",
				hideOnClick: true
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

		$('#search').change(function (e) {
			console.log("search change");
			$('#TOC').jstree(true).show_all();
			$('#TOC').jstree('search', $(this).val());
		});

		// Size correctly depending on screen resolution
		$('#TDM').css('max-height', $(window).height() - 30);
		$('#TDM').css('min-height', $(window).height() - 30);

		$('#CONTENT').css('max-height', $(window).height() - 10);
		$('#CONTENT').css('min-height', $(window).height() - 10);

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

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('ajaxify - Task=' + $data.task);
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

	// useStore should only be processed for specifics tasks like listFiles, display, ... but not all tasks
	var $arrUseStore = ['display', 'listFiles'];

	if (($useStore) && (jQuery.inArray($data.task, $arrUseStore) !== -1)) {
		// Using the cache system provided by store.js

		try {
			if (typeof store.get('task_' + $data.task) !== 'undefined') {
				$bAjax = false;
				data = store.get('task_' + $data.task);
				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
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

				if (marknotes.hasOwnProperty('message')) {
					$($target)
						.html('<div><span class="ajax_loading">&nbsp;</span><span style="font-style:italic;font-size:1.5em;">' + marknotes.message.pleasewait + '</span></div>');
				}
			}, // beforeSend()
			async: true,
			cache: false,
			type: (marknotes.settings.debug ? 'GET' : 'POST'),
			url: marknotes.url,
			data: $data,
			datatype: $params.dataType,
			success: function (data) {

				if ($useStore) {
					store.set('task_' + $data.task, data);
				}

				if ($params.dataType === 'html') {

					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
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
					if (marknotes.settings.debug) {
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

/**
 * The ajax request has returned the list of files.  Build the table and initialize the #TOC DOM object
 *
 * @param {json} $data  The return of the JSON returned by index.php?task=listFiles
 * @returns {Boolean}
 */
function initFiles($data) {

	var $msg = '';

	if (typeof $data === 'undefined') {
		$msg = marknotes.message.json_error;
		Noty({
			message: $msg.replace('%s', 'listFiles'),
			type: 'error'
		});
		return false;
	}

	/*<!-- build:debug -->*/
	//if (marknotes.settings.debug) console.log($data.count);
	/*<!-- endbuild -->*/

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

	// initialize the search area, thanks to the Flexdatalist plugin

	if ($.isFunction($.fn.flexdatalist)) {
		$('.flexdatalist').flexdatalist({
			toggleSelected: true,
			minLength: 3,
			valueProperty: 'id',
			selectionRequired: false,
			visibleProperties: ["name", "type"],
			searchIn: 'name',
			data: 'index.php?task=tags',
			focusFirstResult: true,
			noResultsText: marknotes.message.search_no_result,
			requestType: (marknotes.settings.debug ? 'get' : 'post')
		});
	} // if ($.isFunction($.fn.flexdatalist))

	$('#search').css('width', $('#TDM').width() - 5);
	$('.flexdatalist-multiple').css('width', $('.flexdatalist-multiple').parent()
		.width() - 10).show();

	// Interface : put the cursor immediatly in the edit box
	try {
		$('#search-flexdatalist').focus();
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
		if (marknotes.settings.debug) {
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
			$('[data-task="printer"]').printPreview();
		} catch (err) {
			console.warn(err.message);
		}
	}

	// Get all DOM objects having a data-task attribute
	$("[data-task]").click(function (event) {
		//event.preventDefault();

		var $task = $(this).data('task');

		if ($task.substring(0,9) !== 'clipboard') {
			// DON't STOP PROPAGATION, WILL BREAK THE Clipboard PLUGIN
			event.stopPropagation();
			event.stopImmediatePropagation();
		}

		var $fname = $(this).attr('data-file') ? decodeURIComponent($(this).data('file')) : '';

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

				if (Clipboard.isSupported()) {

					var clipboard = new Clipboard('#icon_clipboard');

					clipboard.on('success', function (e) {
						/*<!-- build:debug -->*/
						if (marknotes.settings.debug) {
							console.info('Action:', e.action);
							console.info('Text:', e.text);
							console.info('Trigger:', e.trigger);
						}
						/*<!-- endbuild -->*/

						Noty({
							message: marknotes.message.copy_clipboard_done,
							type: 'success'
						});

						e.clearSelection();
					});

					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						clipboard.on('error', function (e) {
							console.error('Action:', e.action);
							console.error('Trigger:', e.trigger);
						});
					}
					/*<!-- endbuild -->*/
				} // if (Clipboard.isSupported())
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

		case 'file':

			window.open($fname);
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

		case 'clipboard_link_note':

			// Initialize the Copy into the clipboard button, See https://clipboardjs.com/

			if (typeof Clipboard === 'function') {
				if (Clipboard.isSupported()) {
					new Clipboard('#icon_link_note');
					Noty({
						message: marknotes.message.copy_link_done,
						type: 'success'
					});
				} // if (Clipboard.isSupported())
			} // if (typeof Clipboard === 'function')
			break;

		case 'login':
			showLoginForm();
			break;

		case 'printer':
			break;

		case 'sitemap':

			window.open(marknotes.webroot + 'sitemap.xml');
			break;

		case 'tag':

			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log('... filter on [' + $tag + ']');
			}
			/*<!-- endbuild -->*/

			addSearchEntry({
				keyword: $tag,
				reset: true
			});
			break;

		case 'timeline':

			window.open(marknotes.webroot + 'timeline.html');
			break;

		default:

			console.warn('Sorry, unknown task [' + $task + ']');

		} // switch($task)

	}); // $("[data-task]").click(function()

	return true;

}

/**
 * Open the login dialog box
 * @link http://www.alessioatzeni.com/blog/login-box-modal-dialog-window-with-css-and-jquery/
 */
function showLoginForm() {

	//Fade in the Popup
	$('#login-box').fadeIn(300);
	$('#username').focus();

	//Set the center alignment padding + border see css style
	var popMargTop = ($('#login-box').height() + 24) / 2;
	var popMargLeft = ($('#login-box').width() + 24) / 2;

	$('#login-box').css({
		'margin-top': -popMargTop,
		'margin-left': -popMargLeft
	});

	// Add the mask to body
	$('body').append('<div id="mask"></div>');
	$('#mask').fadeIn(300);

	$('a.close, #mask').click(function () {
		$('#mask , .login-popup').fadeOut(300, function () {
			$('#mask').remove();
		});
	});

	$("#password").keyup(function (event) {
		if (event.keyCode == 13) {
			$("#login-box .submit").click();
		}
	});

	$('#login-box .submit').click(function () {
		var $login = $('#username').val();
		var $password = $('#password').val();

		if (($login === null) || ($login === '') || ($password === null) || ($password === '')) {

			Noty({
				message: marknotes.message.incorrect_login,
				type: 'error'
			});

			$('#username').addClass("errorLogin");
			$('#password').addClass("errorLogin");

		} else {

			// Ok, try to connect
			$login = window.btoa(encodeURIComponent(JSON.stringify($login.trim())));
			$password = window.btoa(encodeURIComponent(JSON.stringify($password.trim())));

			$.post("index.php", {
					task: 'login',
					'username': $login,
					'password': $password
				},

				function (data) {

					var $status = false;

					if (data.hasOwnProperty('status')) {
						$status = (data.status === 1 ? true : false);
					}

					if ($status) {
						$('#mask , .login-popup').fadeOut(300, function () {
							$('#mask').remove();
						});
						Noty({
							message: marknotes.message.login_success,
							type: 'success'
						});
					} else {

						Noty({
							message: marknotes.message.incorrect_login,
							type: 'error'
						});
						$('#username').addClass("errorLogin");
						$('#password').addClass("errorLogin");

					}
				}
			);
		}

	});

	return false;
}

/**
 * Empty the localStorage cache and the session on the server; reload then the page
 */
function cleanCache() {

	// Empty the localStorage too
	if (marknotes.settings.use_localcache) {
		try {
			store.clearAll();
		} catch (err) {
			console.warn(err.message);
		}
	}

	location.reload();

	Noty({
		message: marknotes.message.settings_clean_done,
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
 * Force links that points on the same server (localhost) to be opened in a new window
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
			$('#search').focus();

			// Get the searched keywords.  Apply the restriction on the size.
			var $searchKeywords = $('#search').val().substr(0, marknotes.settings.search_max_width).trim();

			if ($searchKeywords !== '') {
				if ($.isFunction($.fn.highlight)) {
					$("#CONTENT").highlight($searchKeywords);
				}
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

	// Last part : the array $arrPluginsFct is a global array and will be initialized by
	// the differents plugins (like Bootstrap, DataTable, ...) and will contains functions name.
	//
	// For instance : the file /plugins/content/html/bootstrap/bootstrap.js contains this line :
	// $arrPluginsFct.push("PluginBootstrap");
	//
	// This to tell to this code that the PluginBootstrap function should be fired once the note
	// is displayd.  So, let's do it

	try {
		for (var i = 0, len = $arrPluginsFct.length; i < len; i++) {
			// As explained here : https://www.sitepoint.com/call-javascript-function-string-without-using-eval/
			fn = window[$arrPluginsFct[i]];

			if (typeof fn === "function") fn();

		}
	} catch (err) {
		console.warn(err.message);
	}

	// Just for esthetics purposes
	$('#CONTENT').fadeOut(1).fadeIn(3);

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
