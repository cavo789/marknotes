/**
 * This "fake" function is only needed to keep track of the old values
 * used by the last Ajaxify call.
 *
 * In the ajaxify() function, we can set things like :
 *	  ajaxify_Previous_Run.task = $data.task;
 *
 * Then, in a future call of ajaxify(), we can make f.i.
 *		alert("Last running task was : " +
 *			ajaxify_Previous_Run('task'));
 */
function ajaxify_Previous_Run($info) {
	if ($info == 'callback') {
		return ajaxify_Previous_Run.callback;
	} else if ($info == 'data') {
		return ajaxify_Previous_Run.data;
	} else if ($info == 'dataType') {
		return ajaxify_Previous_Run.dataType;
	} else if ($info == 'filename') {
		return ajaxify_Previous_Run.filename;
	} else if ($info == 'param') {
		return ajaxify_Previous_Run.param;
	} else if ($info == 'select_node') {
		return ajaxify_Previous_Run.select_node;
	} else if ($info == 'target') {
		return ajaxify_Previous_Run.target;
	} else if ($info == 'task') {
		return ajaxify_Previous_Run.task;
	} else {
		console.warn('ajaxify_Previous_Run called for ' + $info + '. Unhandled type');
	}
}

/**
 * Logic for retrieving the callback parameter
 */
function ajaxify_get_callback($params, $bTaskReload) {

	$callback = '';

	if ($bTaskReload) {
		$callback = ajaxify_Previous_Run('callback');

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('ajaxify - Reload = last callback was ' + $callback);
		}
		/*<!-- endbuild -->*/

	} else {

		$callback = ($params.callback !== 'undefined') ? $params.callback : '';

	}

	return $callback;
}

/**
 * Logic for retrieving the dataType parameter
 */
function ajaxify_get_dataType($params, $bTaskReload) {

	$dataType = (($params.task == 'task.export.html') ? 'html' : '');

	if ($bTaskReload) {
		$dataType = ajaxify_Previous_Run('dataType');

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('ajaxify - Reload = last dataType was ' + $dataType);
		}
		/*<!-- endbuild -->*/

	} else {

		if (typeof $params.dataType === 'undefined') {
			if (typeof $params.filename !== 'undefined') {
				// Derive the data type based on the extension
				// http://stackoverflow.com/a/680982
				var re = /(?:\.([^.]+))?$/;
				$dataType = re.exec($params.filename)[1];
			}
		} else {
			$dataType = $params.dataType;
		}
	}

	return $dataType;
}

/**
 * Logic for retrieving the filename parameter
 */
function ajaxify_get_filename($params, $bTaskReload) {

	$filename = '';

	if ($bTaskReload) {
		$filename = ajaxify_Previous_Run('filename');
		$params.filename = ajaxify_Previous_Run('filename');

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('ajaxify - Reload = last filename was ' + $filename);
		}
		/*<!-- endbuild -->*/

	} else {

		if (typeof $params.filename !== 'undefined') {

			// Retrieve the filename that should be displayed
			// (like sitemap.xml f.i.)
			$filename = $params.filename;
		}

	}

	return $filename;
}

/**
 * Logic for retrieving the task parameter
 */
function ajaxify_get_param($params, $bTaskReload) {

	if ($bTaskReload) {
		$param = ajaxify_Previous_Run('param');
		$params.param = $param;
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('ajaxify - Reload = last param was ' + $param);
		}
		/*<!-- endbuild -->*/

	} else {
		$param = (typeof $params.param === 'undefined') ? '' : $params.param;
	}

	return $param;
}

/**
 * Logic for retrieving the select_node parameter
 */
function ajaxify_get_select_node($params, $bTaskReload) {

	$select_node = {};

	if ($bTaskReload) {
		$select_node = ajaxify_Previous_Run('select_node');

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('ajaxify - Reload = last select_node was ' + $select_node);
		}
		/*<!-- endbuild -->*/

	} else {

		if (typeof $params.select_node !== 'undefined') {
			$select_node = $params.select_node;
		}

	}
	return $select_node;
}

/**
 * Logic for retrieving the target parameter
 */
function ajaxify_get_target($params, $bTaskReload) {

	if ($bTaskReload) {
		$target = ajaxify_Previous_Run('target');

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('ajaxify - Reload = last target was ' + $target);
		}
		/*<!-- endbuild -->*/

	} else {

		$target = '#' + (($params.target === 'undefined') ? 'CONTENT' : $params.target);

	}

	return $target;
}

/**
 * Logic for retrieving the task parameter
 */
function ajaxify_get_task($params) {

	$task = (typeof $params.task === 'undefined') ? '' : $params.task;

	if ($bTaskReload) {
		// $params.task is reload so
		$params.task = ajaxify_Previous_Run('task');
		$task = $params.task;

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('ajaxify - Reload = last task was ' + $task);
		}
		/*<!-- endbuild -->*/

	} else {
		if ((typeof $params.filename !== 'undefined') && ($task === '')) {
			// A filename has been specified
			// (like f.i. timeline.json).
			// So, the task in this case is "getFile" i.e.
			// just access to a file
			$task = 'getFile';
		}
	}

	return $task;
}

/**
 * Logic for retrieving the useStore parameter
 */
function ajaxify_get_useStore($params, $bTaskReload) {

	$useStore = false;

	if (!$bTaskReload) {
		// Allow to use the navigator's localStorage ?
		// The "store" object is created by the store.js file that
		// is included by the /page/html/optimize plugin when the
		// localStorage setting is enabled in settings.json.
		var $useStore = (typeof store === 'object');

		// Then, if allowed, check the useStore parameter, if specified,
		// get it's value.
		if ($useStore && (typeof $params.useStore !== 'undefined')) {
			$useStore = $params.useStore;
		}

		if ($useStore) {
			// Check on the querystring is 'use_store' has been specified
			// and if so, use that value
			if (typeof url('?use_store') !== "undefined") {
				$useStore = (url('?use_store') == '1');
			}
		}
	} else {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('ajaxify - Reload = Don\'t use localStorage');
		}
		/*<!-- endbuild -->*/
	}

	return $useStore;
}

/**
 * Run an ajax query
 *
 * @param {json} $params
 *	  task = which task should be fired
 *	  param = (optional) parameter to provide for the calling task
 *	  callback = (optional) Function to call once the ajax call
 *		is successfully done
 *	  useStore = (optional) Tell if the localStorage object can
 *			be used or not.
 *			Implemented by the page/html/optimize plugin
 *		reload = (optional) True/False. If set, the ?reload parameter
 *			will be added to the querystring
 *
 * @returns {undefined}
 */
function ajaxify($params) {

	var $data = {};

	$bTaskReload = false;
	if ((typeof $params.task !== 'undefined') && ($params.task == 'reload')) {
		$bTaskReload = true;
	}

	// Get parameters. This is a little more complex than just reading
	// the $params parameter because, when $params.task is "reload",
	// ajaxify should retrieve the $params used during the last call
	// so we need to retrieve, parameter by parameter the old value.
	$useStore = ajaxify_get_useStore($params, $bTaskReload);
	$data.task = ajaxify_get_task($params, $bTaskReload);
	$data.param = ajaxify_get_param($params, $bTaskReload);
	$filename = ajaxify_get_filename($params, $bTaskReload);
	$dataType = ajaxify_get_dataType($params, $bTaskReload);
	$target = ajaxify_get_target($params, $bTaskReload);
	$callback = ajaxify_get_callback($params, $bTaskReload);

	$select_node = ajaxify_get_select_node($params, false);

	$reload=false;
	if (typeof $params.reload !== 'undefined') {
		$reload = $params.reload;
	}

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('ajaxify - Task=' + $data.task + ' / Param=' + $data.param);
		console.log($params);
	}
	/*<!-- endbuild -->*/

	// Remember parameters in case we need to rerun the ajax call
	// (with using LocalStorage in this case)
	if ($data.task !== 'reload') {
		ajaxify_Previous_Run.callback = $callback;
		ajaxify_Previous_Run.task = $data.task;
		ajaxify_Previous_Run.param = $data.param;
		ajaxify_Previous_Run.filename = $filename;
		ajaxify_Previous_Run.dataType = $dataType;
		ajaxify_Previous_Run.target = $target;
	}

	var $bAjax = true;

	if ($useStore) {
		/**
		 * Using the cache system provided by store.js
		 * The key for the localStorage array will be the
		 * name of the task (i.e. $data.task) except when the
		 * task is "export.html" i.e. displaying a note.
		 * Since we need to make a clear distinction between
		 * each note. $data.param will then be used since its
		 * the md5 of the note's filename so unique.
		 */
		data = fnPluginTaskOptimizeStore_Get({
			"name": ($data.task == 'task.export.html' ? $data.param : $data.task)
		});

		if (data !== null) {
			if (($data.task === 'task.listfiles.treeview') || ($filename === 'listfiles.json')) {
				// Don't reload if the list of files unless
				// the data object doesn't contains files
				if (data.hasOwnProperty("tree")) {
					if (data.tree.children.length > 0) {
						$bAjax = false;
					}
				}
			} else {
				if (typeof data === 'undefined') {
					// Undefined => load
					$bAjax = true;
				} else if (typeof data === 'string') {
					// False => don't run an ajax request
					// when data isn't empty
					$bAjax = (data.trim() === '');
				} else {
					$bAjax = false;
				}
			}
		} // if (data !== null)
	} // if ($useStore)

	if ($bAjax) {
		// The localStorage hasn't already retrieved the
		// value for the task so run the ajax request
		// The oldname and the type parameters are set by
		// the plugin Files
		if (typeof $params.oldname !== 'undefined') {
			$data.oldname = $params.oldname;
		}
		if (typeof $params.type !== 'undefined') {
			$data.type = $params.type;
		}

		if ($reload==1) {
			// Add the reload parameter to inform PHP code
			// to not use $_SESSION
			$data.reload = 1;
		}

		$url = ($filename !== '') ? $filename : marknotes.url;

		// If a filename is specified (like listfiles.json), there
		// is no querystring (the $data variable) except the ?reload
		// parameter if needed (when $reload is set to 1).
		// This will ask to the PHP code to not use the $_SESSION
		// object in case of the optimize plugin is active.
		if ($filename !== '') {
			$data = ($reload==1) ? 'reload' : '';
		}

		$.ajax({
			beforeSend: function () {
				if (marknotes.hasOwnProperty('message')) {
					$($target)
						.html('<div><span class="ajax_loading">&nbsp;</span><span style="font-style:italic;font-size:1.5em;">' +
							$.i18n('please_wait') + '</span></div>');
				}
			}, // beforeSend()
			async: true,
			cache: true,
			type: (marknotes.settings.debug ? 'GET' : 'POST'),
			url: $url,
			data: $data,
			datatype: $dataType,
			success: function (data) {
				// If "select_node" has been set by the caller
				// (f.i. the treeview plugin add this attribute in the
				// fnPluginTaskTreeView_reload() function when
				// the treeview should be reloaded and, then, a specific
				// node should be selected (f.i. after the creation of
				// a new note)
				if (!jQuery.isEmptyObject($select_node)) {
					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						console.log('	Add data.select_node attribute');
						console.log($select_node);
					}
					/*<!-- endbuild -->*/
					data.select_node = $select_node;
				}

				if ($useStore) {
					var storeVarName = ($data.task == 'task.export.html' ? $data.param : $data.task);

					if (storeVarName !== undefined) {
						fnPluginTaskOptimizeStore_Set({
							"name": storeVarName,
							"data": data
						});
					}
				}

				if ($dataType === 'html') {

					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						console.log('Output the result into target area');
					}
					/*<!-- endbuild -->*/

					$($target).html(data);
				}

				/* jshint ignore:start */
				if ($callback !== '') {
					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						console.log('Run the callback function : ' + $callback);
					}
					/*<!-- endbuild -->*/
					eval($callback);

				} // if ($callback !== '')
				/* jshint ignore:end */

			}, // success
			error: function (Request, textStatus, errorThrown) {
				ajaxify_show_error($target, Request, textStatus, errorThrown);
			} // error
		}); // $.ajax()
	} else { // if ($bAjax)
		// The localStorage has been used => no need to run
		// the ajax request again.
		// The answser (i.e. the HTML of the note f.i.) is in the data variable
		// It's a string

		if ($dataType === 'html') {
			$($target).html(data);
		}

		/* jshint ignore:start */
		var $callback = ($params.callback === undefined) ? '' : $params.callback;
		if ($callback !== '') {
			eval($callback);
		}
		/* jshint ignore:end */

		Noty({
			message: $.i18n('loaded_from_localStorage'),
			type: 'info'
		});
	} // if ($bAjax)
}

/**
 * Display an error message to inform the user about the problem
 */
function ajaxify_show_error(Target, Request, textStatus, errorThrown) {
	var $msg = '<div class="bg-danger text-danger img-rounded" style="margin-top:25px;padding:10px;">';
	$msg = $msg + '<strong>An error has occured :</strong><br/>';
	$msg = $msg + 'Internal status: ' + textStatus + '<br/>';
	$msg = $msg + 'HTTP Status: ' + Request.status + ' (' + Request.statusText + ')<br/>';
	$msg = $msg + 'XHR ReadyState: ' + Request.readyState + '<br/>';
	$msg = $msg + 'Raw server response:<br/>' + Request.responseText + '<br/>';
	$msg = $msg + '</div>';
	$(Target).html($msg);
	return;
}
