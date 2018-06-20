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
 * The ajax request has returned the list of files.  Build the table and
 * initialize the #TOC DOM object
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
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.warn(err.message + ' --- More info below ---');
			console.log(err);
			Noty({
				message: err.message,
				type: 'error'
			});
		}
		/*<!-- endbuild -->*/
	} // try

	// Initialize the jsTree and attach events
	jstree_init($data);

	// If the data object contains a "select_node" attribute
	// this means that the treeview should immediatly display
	// that given note (based on his md5).
	//
	// Note : md5 is calculated on the filesystem filename but like
	// this : docs\subfolder\note
	//
	//		* should start with the folder name (docs)
	//		* should use the OS folder separator (so \ under Windows)
	//		* should mentionned the relative name of file
	//		* should not mentionned the .md extension

	if ($data.hasOwnProperty('select_node')) {
		if (typeof $data.select_node.id !== 'undefined') {
			// Needed otherwise the jstree's state plugin will
			// reset the selected node

			try {
				// Select the node
				$('#TOC').jstree('select_node', $data.select_node.id);
			} catch (err) {
			}

			try {
				// If the selected node is a folder, just open it
				$('#TOC').jstree('open_node', $('#TOC').jstree('get_selected'));
			} catch (err) {
			}

		}
	}

	// See if the custominiFiles() function has been defined and
	// if so, call it
	try {
		if (typeof custominiFiles !== 'undefined' && $.isFunction(custominiFiles)) {
			custominiFiles();
		}
	} catch (err) {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.warn(err.message + ' --- More info below ---');
			console.log(err);
			Noty({
				message: err.message,
				type: 'error'
			});
		}
		/*<!-- endbuild -->*/
	} // try

	// Call javascript functions there were added by plugins
	runPluginsFunctions();

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('******** initFiles - END ***************');
	}
	/*<!-- endbuild -->*/

	// If nothing has been loaded, show a quick userguide
	if (marknotes.settings.hasOwnProperty('show_tips')) {

		// Enabled ? If yes, run the task and receive the HTML
		// for the in-context help
		if (marknotes.settings.show_tips == 1) {
			ajaxify({
				task: 'task.homepage.show',
				callback: 'afterShowHomepage()',
				dataType: 'html',
				useStore: 0,
				target: 'HOMEPAGE'
			});
		}
	}

	// Detect if the page was called with like "?keyword"
	// f.i. http://localhost:8080/notes/?marknotes
	// If so, immediatly start a search for the keyword marknotes
	$search = window.location.search.substring(1);
	if (($search !== 'undefined') && ($search !== '')) {
		// Verify the presence of the function
		var fn = window["fnPluginTaskSearch_addSearchEntry"];
		if (typeof fn === "function") {
			fnPluginTaskSearch_addSearchEntry({
				keyword: $search,
				reset: true
			});
		}
	}

	return true;
}

/**
 * A tip has been displayed
 */
function afterShowHomepage() {

	if (marknotes.settings.show_tips == 1) {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('*********** afterShowHomepage **********');
		}
		/*<!-- endbuild -->*/

		// First process afterDisplay in order to process content's
		// related features
		afterDisplay("");

		// Add the show_tip class
		//$('#FAVORITES').addClass('show_tip');
	}
	return true;
}

/**
 * Initialize each action buttons of the displayed note.
 * These buttons should :
 *	- Have an id starting with "icon_xxxx" (f.i. id="icon_preview")
 *	- Have a data-task attribute			(f.i. data-task="preview")
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

	if (typeof initializeSettings !== 'undefined') {
		initializeSettings();
	}

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

		// Get the relative filename; initialized by the
		// onclick event of jstree. Relative from the /doc folder
		// so, f.i., /folder/note (without extension) and not
		// /docs/folder/note
		var $file = marknotes.note.file;

		if ($(this).attr('data-extension')) {
			var $extension = $(this).data('extension');
			$file = $file + '.' + $extension;
		}

		if (($file !== '') && ($task !== 'file')) {
			// Don't base64 the filename when the task is
			// 'file', the URL (file) should remains readable
			$file = window.btoa(encodeURIComponent(JSON.stringify($file)));
		}

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			if ($file!=='') {
				console.log('Running task [' + $task + '] for ' +
					'file [' + $file + ']');
			} else {
				console.log('Running task [' + $task + ']');
			}
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

			// The attribute data_file is set by f.i. the
			// sitemap or timeline button.
			// The value can be "sitemap.xml" f.i.
			var $data_file = '';

			try {
				// If set, it means that the user has click
				// on a button like sitemap or timeline
				$data_file = $(this).data('file');
			} catch (e) {
			}

			if ($data_file!=='') {
				// data_file are in the root
				window.open(marknotes.webroot + $data_file);
			} else {
				// It's a note (not a data_file so a note);
				// under the /docs folder
				window.open(marknotes.docs + $file);
			}
			break;

		default:

			// The task is perhaps a function that was added
			// by a plugin. For instance the login form is
			// a plugin and the data-task
			// is set to "fnPluginTaskLogin", defined in login.js
			// => try and call this function
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
				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.warn('Problem when trying to evaluate [' + $task + '][' + err.message + ']');
					console.log(err);
				}
				/*<!-- endbuild -->*/
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

		// Be sure to remove the show_tip class which is only for
		// tips (task.homepage.show) and not for other content.
		if ($('#CONTENT').hasClass('show_tip')) {
			$('#CONTENT').removeClass('show_tip');
		}

		// Initialize each action buttons of the displayed note
		initializeTasks();

		// Retrieve the heading 1 from the loaded file
		var $title = $('#CONTENT h1').text();

		if ($title !== '') {
			$('#CONTENT h1').hide();
		}

		// Even when there is no h1, we need to update the area otherwise
		// the previous title (of the previous read note) will still be
		// displayed
		$('title').text($title);
		$('.content-header h1').text($title);

		$fname = $('div.filename').text();
		if ($fname !== '') {
			$('#footer').html('<strong style="text-transform:uppercase;">' + $fname + '</strong>');
		}

		// See if the customafterDisplay() function has
		// been defined and if so, call it
		if (typeof customafterDisplay !== 'undefined' && $.isFunction(customafterDisplay)) {
			customafterDisplay($fname);
		}

	} catch (err) {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.warn(err.message + ' --- More info below ---');
			console.log(err);
		}
		/*<!-- endbuild -->*/
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
 * The array marknotes.arrPluginsFct is a global array and
 * will be initialized by the differents plugins
 * (like Bootstrap, DataTable, ...) and will contains functions name.
 *
 * For instance : the file
 * 		/marknotes/plugins/content/html/bootstrap/bootstrap.js
 * contains this line :
 *		marknotes.arrPluginsFct.push("PluginBootstrap");
 *
 * This to tell to this code that the PluginBootstrap function
 * should be fired once the note is displayed.  So, let's do it
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
		// because some functions like f.i. fnPluginTaskTreeView_init() (in
		// plugin task treeview) should be called only once and, that
		// function, remove its entry from the marknotes.arrPluginsFct
		// array.  Be sure to process every items so copy the array

		$arrFct = marknotes.arrPluginsFct.slice();

		var $j = $arrFct.length;
		for (var $i = 0, $j = $arrFct.length; $i < $j; $i++) {

			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log('	call ' + ($i + 1) + '/' + $j + ' : ' + $arrFct[$i]);
			}
			/*<!-- endbuild -->*/

			// As explained here : https://www.sitepoint.com/call-javascript-function-string-without-using-eval/
			fn = window[$arrFct[$i]];

			if (typeof fn === "function") {
				fn();
			}

		}
	} catch (err) {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.warn(err.message + ' --- More info below ---');
			console.log(err);
		}
		/*<!-- endbuild -->*/
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
 *	  message : the message to display
 *	  type	: success, error, warning, information, notification
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
			$timeout = ((typeof $params.timeout === 'undefined') ? '2400' : $params.timeout);

			// More options, see http://ned.im/noty/options.html
			var n = noty({
				text: $params.message,
				theme: 'relax',
				timeout: $timeout,
				layout: 'bottomRight',
				type: $type
			}); // noty()
		}
	}

}
