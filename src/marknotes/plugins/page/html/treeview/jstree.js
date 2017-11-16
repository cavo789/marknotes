var jsTree_Search_Result = '';

// Used by the search plugin to filter on notes having an ID as returned
// by the ajax search task
function jsTree_ajax_search(str, node) {
	return $.inArray(node.id, jsTree_Search_Result) >= 0;
}

/**
 * Initialize the treeview
 *
 * @param {type} $data  JSON returned by
 *						index.php?task=task=task.listfiles.treeview
 * @returns {undefined}
 */
function jstree_init($data) {
	try {
		if ($.isFunction($.fn.jstree)) {

			$('#TOC').jstree("destroy").empty();
			$('#TOC').jstree("true");

			var $arrPlugins = ['contextmenu', 'types', 'search', 'types', 'unique'];

			try {
				// Use the "state" plugin of jsTree to remember the state
				// of the treeview between visits only if the LocalStore
				// setting was set i.e. when the store.min.js script
				// has been loaded and define the store object
				if (typeof store === 'object') {
					// Ok, localStore is set, use the state plugin
					$arrPlugins.push('state');
				}
			} catch (e) {
				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.error(e.message);
				}
				/*<!-- endbuild -->*/
			}

			// Sometimes wholerow raise an error with the state plugin.
			// Error can be "this.get node().find is not a function".
			// By removing wholerow in the list below and refreshing
			// the page will solve the issue. Then add wholerow back
			// in the list.
			// Still occurs with jsTree 3.3.4 (2017-07-31)
			//$arrPlugins.push('wholerow');

			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log('List of jstree plugins');
				console.log($arrPlugins);
			}
			/*<!-- endbuild -->*/

			// Use jsTree for the display
			$('#TOC')
				.on('loaded.jstree', function () {
					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						console.log('Tree has been successfully loaded');
					}
					/*<!-- endbuild -->*/
				})
				.on('changed.jstree', function (e, data) {
					var objNode = data.instance.get_node(data.selected);

					// Remember the ID of the note
					// marknotes.node.id is defined in the template
					if (typeof marknotes.note.id !== "undefined") {
						marknotes.note.id = objNode.id;
					}

					if (typeof (objNode.parent) !== "undefined") {
						// Get the filename : objNode.parent mention the
						// relative parent folder (f.. /development/jquery/)
						// and objNode.text the name of the file (f.i.
						// jsTree.md)

						/*<!-- build:debug -->*/
						if (marknotes.settings.debug) {
							console.log('Tree - Selected item : ' + objNode.parent + objNode.text);
							console.log(objNode);
						}
						/*<!-- endbuild -->*/

						//var $fname = objNode.parent + objNode.text + '.md';
						if (objNode.data !== null) {

							// On small screen automatically hide the treeview
							// when a note is being displayed
							if ($(window).width() < 1024) {
								$('body').removeClass('sidebar-open');
							}

							var $fname = objNode.data.url;

							$fname = window.btoa(encodeURIComponent(JSON.stringify($fname)));

							// Remember the URL of the note
							// Use the full URL like (http://localhost/notes/docs/jsTree.html)
							marknotes.note.url = marknotes.docs + '/' + objNode.data.url;
							marknotes.note.basename = objNode.data.basename;

							// And remember the hash (md5) of the URL
							marknotes.note.md5 = $fname;

							// Default task
							var task = 'task.export.html';
							if (typeof (objNode.data.task) !== "undefined") {
								task = objNode.data.task;
							}

							if (task == 'task.edit.form') {
								ajaxify({
									task: 'task.edit.form',
									param: marknotes.note.md5,
									callback: 'afterEdit($data, data)',
									useStore: false,
									target: 'CONTENT'
								});
							} else {
								ajaxify({
									task: task,
									param: $fname,
									callback: 'afterDisplay($data.param)',
									target: 'CONTENT',
									useStore: true
								});
							}
						} // if (objNode.data !== null)
					} // if (typeof(objNode.parent)!="undefined")
				})
				.on('click', '.jstree-anchor', function (e) {

					// By clicking (single-click) on a folder, open / close it
					$(this).jstree(true).toggle_node(e.target);

				})
				.on('keydown.jstree', '.jstree-anchor', function (e) {

					// @TODO : Problem : e.currentTarget is not yet the current one but the one when the move was done.
					// If I was on chidl3 and press the down key, I need to capture child4 (the next one) and e.currentTarget is still on child3.
					// Not found a solution...
					var objNode = $('#TOC').jstree(true).get_node(e.currentTarget);

					/*<!-- build:debug -->*/
					if (marknotes.settings.debug) {
						if (objNode.data) {
							console.log('changed.jstree - ' + objNode.data.file);
						}
					}
					/*<!-- endbuild -->*/

				})
				.on('search.jstree', function (nodes, str, res) {
					if (str.nodes.length === 0) {
						// No nodes found, hide all
						//$('#TOC').jstree(true).hide_all();
						// except the first node (show it)
						$("#TOC").jstree("show_node", "ul > li:first");
					}
				})
				.jstree({
					plugins: $arrPlugins,
					core: {
						animation: 1,
						progressive_render: true,
						data: $data.tree,
						check_callback: function (operation, node, node_parent, node_position, more) {

							// Return true to allow the operation, return false otherwise
							//
							// Allow changes on the jsTree by javascript
							// operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'

							return operation === 'move_node' ? false : true;
						},
						multiple: false,
						initially_open: ['phtml_1'], // Automatically open the root node
						themes: {
							dots: 0,
							ellipsis: 1,
							stripes: 0,
							variant: 'small'
						},
						types: {
							default: {
								icon: 'folder'
							},
							file: {
								icon: 'file file-md'
							},
							folder: {
								icon: 'folder'
							}
						}
					},
					search: {
						close_opened_onclear: false,
						case_insensitive: true,
						show_only_matches: true, // Hide unmatched, show only matched records
						search_leaves_only: true, // Only files, not folder
						ajax: {
							url: 'search.php', // This request will be fired with the '&str=SEARCH_TERM' parameter
							dataType: 'json',
							type: (marknotes.settings.debug ? 'GET' : 'POST'),
							beforeSend: function () {
								/*<!-- build:debug -->*/
								console.time('Search time');

								/*<!-- endbuild -->*/
								var loading = '<div id="ajax_loading" class="lds-css"><div style="width:100%;height:100%" class="lds-ellipsis"><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div></div>';
								$('#TOC').hide().parent().append(loading);
							}, // beforeSend()
							success: function (data) {
								/*<!-- build:debug -->*/
								if (marknotes.settings.debug) {
									console.log('Success');
									console.timeEnd('Search time');
									console.log('Search - success, list of IDs returned :');
									console.log(data);
								}
								/*<!-- endbuild -->*/

								$('#ajax_loading').remove();
								$('#TOC').fadeIn();

								if (data.hasOwnProperty('message')) {
									Noty({
										message: data.message,
										type: 'information'
									});
								}

								// data is a JSON string with a 'files' entry
								// That entry contains md5 of matched filenames
								// Store these md5 in the jsTree_Search_Result
								// variable that is used by the callback :
								// jsTree_ajax_search
								// data.files is a JSON array, convert it to
								// a javascript array thanks to $.parseJSON
								jsTree_Search_Result = $.parseJSON(data.files);
							} // success
						},
						search_callback: jsTree_ajax_search
					},
					contextmenu: {
						items: jstree_context_menu
					}
				});
		} // // if ($.isFunction($.fn.jstree))
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

}

/**
 * Context menu for the treeview.  This function will build the contextual menu
 * and return the list of entries of that menu
 *
 * This function will ask call the Task-Treeview plugin for populating the menu
 *
 * @param {type} node             The node on which the user has right-clicked
 * @returns {context_menu.items}
 */
function jstree_context_menu(node) {

	var $tree = $('#TOC').jstree(true);

	var $type = 'file';

	try {
		$type = (node.icon.substr(0, 6).toLowerCase() === "folder" ? "folder" : "file");
	} catch (e) {
		console.warn(err.message);
	}

	var $items = {};

	$items.Collapse = {
		separator_before: false,
		separator_after: false,
		label: $.i18n('tree_collapse'),
		icon: 'fa fa-plus-square-o',
		action: function () {
			$('#TOC').jstree('close_all');
		}
	};

	$items.Expand = {
		separator_before: false,
		separator_after: true,
		label: $.i18n('tree_expand'),
		icon: 'fa fa-minus-square-o',
		action: function () {
			$('#TOC').jstree('open_all');
		}
	};

	// Open the note in a new window or, too, the folder (open then index.html)
	$items.Open_NewWindow = {
		separator_before: false,
		separator_after: true,
		label: $.i18n('open_html'),
		icon: 'fa fa-external-link',
		action: function () {
			contextMenuNewWindow(node);
		}
	};

	// Start the presentation mode
	$items.Open_Slideshow = {
		separator_before: false,
		separator_after: true,
		label: $.i18n('open_slideshow'),
		icon: 'fa fa-desktop',
		action: function () {
			contextMenuSlideshow(node);
		}
	};

	// ------------------------------------------------------------------------
	// Plugin Task-Treeview
	// The fnPluginTaskTreeViewContextMenu() is defined in the plugins
	// task/treeview Check if that plugin has loaded the function and
	// if so, get extra items for the context menu
	var fn = window.fnPluginTaskTreeViewContextMenu;

	// is object a function?
	if (typeof fn === "function") {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('Run fnPluginTaskTreeViewContextMenu(), retrieve what should appears in the contextual menu');
		}
		/*<!-- endbuild -->*/

		// Give parameters to the function
		var $params = {};
		$extraItems = fn(node);

		if (Object.keys($extraItems).length > 0) {

			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log('Below items returned by fnPluginTaskTreeViewContextMenu()');
				console.log($extraItems);
			}
			/*<!-- endbuild -->*/

			// $extraItems is a JSON object
			// Loop and add any entries (i.e. actions) into the $item JSON object

			jQuery.each($extraItems, function ($action, $node) {
				$items[$action] = $node;
			});

		}

	}
	// ------------------------------------------------------------------------

	return $items;

}

/**
 * The treeview context menu - Open in a new window function has been clicked
 */
function contextMenuNewWindow(node) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('Context menu - Open in a new window');
		console.log(node);
	}
	/*<!-- endbuild -->*/

	window.open(node.data.url);
}
/**
 * The treeview context menu - Open in a new window function has been clicked
 */
function contextMenuSlideshow(node) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('Context menu - Start the slideshow');
		console.log(node);
	}
	/*<!-- endbuild -->*/

	var $url = node.data.url;
	$url = $url.substr(0, $url.lastIndexOf(".")) + ".reveal";

	window.open($url);
}
