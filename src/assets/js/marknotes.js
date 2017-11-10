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

		// Be sure that this plugin is well part of the current
		// Prism installation and is loaded
		if (typeof Prism === 'object') {
			if (Prism.plugins.NormalizeWhitespace) {
				Prism.plugins.NormalizeWhitespace.setDefaults({
					'break-lines': 120 // Soft wrap after 120 chars
				});
			}
		}

		// On page entry, get the list of .md files on the server
		if (marknotes.hasOwnProperty('message')) {
			Noty({
				message: $.i18n('loading_tree'),
				type: 'info'
			});
		}

		ajaxify({
			filename: 'listfiles.json', // same of task: 'task.listfiles.treeview'
			dataType: 'json',
			callback: 'initFiles(data)'
		});

	} // if (marknotes.autoload === 1)

});

/**
 * The ajax request has returned the list of files.  Build the table and initialize
 * the #TOC DOM object
 *
 * @param {json} $data  The return of the JSON returned by
 *		index.php?task=task.listfiles.treeview
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
		Noty({
			message: $.i18n('json_error', 'listFiles'),
			type: 'error'
		});
		return false;
	}

	try {
		if ($data.hasOwnProperty('count')) {
			// Display the number of returned files
			Noty({
				// Don't use jQuery.i18n yet since at this stage,
				// the plugin isn't yet loaded...
				message: marknotes.files_found.replace('$1', $data.count),
				type: 'notification'
			});
		}
	} catch (err) {
		console.warn(err.message + ' --- More info below ---');
		console.log(err);
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
		console.warn(err.message + ' --- More info below ---');
		console.log(err);
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
		} catch (err) {
			console.warn(err.message + ' --- More info below ---');
			console.log(err);
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

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('*********** initializeTasks **********');
		//console.groupCollapsed('Get list of buttons');
		//$("[data-task]").each(function () {
		//	console.log('There is a button with an id=[' + $(this).attr('id') + '] and a data-task=[' + $(this).data('task') + ']');
		//});
		//console.groupEnd();
	}
	/*<!-- endbuild -->*/

	// Get all DOM objects having a data-task attribute
	$("[data-task]").click(function (event) {
		//event.preventDefault();

		// When clicking on an option, hide the side-bar
		if ($('.control-sidebar').length !== 0) {
			$('.control-sidebar').removeClass('control-sidebar-open');
		}

		var $task = $(this).data('task');

		if ($task.substring(0, 23) !== 'fnPluginButtonClipboard') {
			// DON't STOP PROPAGATION, WILL BREAK THE Clipboard PLUGIN
			event.stopPropagation();
			event.stopImmediatePropagation();
		}

		// In case of a data-file parameter has been specified, get it
		var $file = $(this).attr('data-file') ? $(this).data('file') : '';

		if (($file !== '') && ($task !== 'file')) {
			// Don't base64 the filename when the task is 'file', the URL (file) should
			// remains readable
			$file = window.btoa(encodeURIComponent(JSON.stringify($file)));
		}

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('Running task [' + $task + '] for [' + $file + ']');
		}
		/*<!-- endbuild -->*/

		switch ($task) {

		case 'task.export.html':

			// export.html i.e. display the file by calling the Ajax function.
			// Display its content in the CONTENT DOM element

			ajaxify({
				task: $task,
				param: $file,
				callback: 'afterDisplay($data.param)',
				target: 'CONTENT'
			});
			break;

		case 'file':

			window.open($file);
			break;

			/*case 'fullscreen':

				toggleFullScreen();

				break;*/

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
						console.log('Running the function called [' + $task + ']');
					}
					/*<!-- endbuild -->*/

					// Check if there is a data-param attribute
					// (plugins/content/html/tag/tags.php is an example
					// of how specify a parameter)
					var data_param = $(this).attr('data-param') ? $(this).data('param') : '';

					// Give parameters to the function
					var params = {};
					params.param = data_param; // Set the param property
					params.filename = $file; // and the filename if defined
					fn(params);

				} else {
					console.warn('Sorry, unknown task [' + $task + ']');
				}
			} catch (err) {
				console.warn('Problem when trying to evaluate [' + $task + '][' + err.message + ']');
				console.log(err);
			}

		} // switch($task)

	}); // $("[data-task]").click(function()

	return true;

}

/**
 * Called after the ajax "export.html" request, the file is displayed,
 * run the last javascript script like initializing buttons
 */
function afterDisplay($fname) {

	try {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('--- Start - Running afterDisplay() ---');
		}
		/*<!-- endbuild -->*/

		// Initialize each action buttons of the displayed note
		initializeTasks();

		// Retrieve the heading 1 from the loaded file
		var $title = $('#CONTENT h1').text();
		if ($title !== '') {
			$('title').text($title);
			$('.content-header h1').text($title);
			$('#CONTENT h1').hide();
		}

		$fname = $('div.filename').text();
		if ($fname !== '') {
			$('#footer').html('<strong style="text-transform:uppercase;">' + $fname + '</strong>');
		}

		// See if the customafterDisplay() function has been defined and if so, call it
		if (typeof customafterDisplay !== 'undefined' && $.isFunction(customafterDisplay)) {
			customafterDisplay($fname);
		}
	} catch (err) {
		console.warn(err.message + ' --- More info below ---');
		console.log(err);
	}

	// Call javascript functions there were added by plugins
	runPluginsFunctions();

	// Just for esthetics purposes
	$('#CONTENT').fadeOut(1).fadeIn(3);

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('--- End - Running afterDisplay() ---');
	}
	/*<!-- endbuild -->*/

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
		console.log('--- Start - Running plugins functions ---');
		//console.log(marknotes.arrPluginsFct);
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
		console.warn(err.message + ' --- More info below ---');
		console.log(err);

	}

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('--- End - Running plugins functions ---');
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

	// If present, retrieve the marknotes.isBot variable. Is set to 1 when
	// the site is visited by a crawler bot
	isBot = ((marknotes.isBot === 'undefined') ? 0 : marknotes.isBot);

	// Notification are not usefull for bots ;-)
	if (!marknotes.isBot) {
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

}
