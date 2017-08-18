var jsTree_Search_Result = '';
var logJSTree;

// Used by the search plugin to filter on notes having an ID as returned by the ajax search task
function jsTree_ajax_search(str, node) {
	return $.inArray(node.id, jsTree_Search_Result) >= 0;
}

/**
 * Initialize the treeview
 *
 * @param {type} $data            JSON returned by index.php?task=listFiles
 * @returns {undefined}
 */
function jstree_init($data) {

	try {
		if ($.isFunction($.fn.jstree)) {

			$('#TOC').jstree("destroy").empty();
			$('#TOC').jstree("true");

			var $arrPlugins = ['contextmenu', 'types', 'search', 'types', 'unique', 'wholerow'];

			$arrPlugins.push('state');

			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log('List of plugins loaded');
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
					//	$("#TOC").jstree("select_node", "#e4dd128f135e34986b483208539e9c6c"); // JUG W
				})
				.on('changed.jstree', function (e, data) {
					var objNode = data.instance.get_node(data.selected);
					if (typeof (objNode.parent) !== "undefined") {
						// Get the filename : objNode.parent mention the relative parent folder (f.. /development/jquery/)
						// and objNode.text the name of the file (f.i. jsTree.md)
						/*<!-- build:debug -->*/
						if (marknotes.settings.debug) {
							console.log('Tree - Selected item : ' + objNode.parent + objNode.text);
						}
						/*<!-- endbuild -->*/

						//var $fname = objNode.parent + objNode.text + '.md';
						var $fname = objNode.data.url;
						$fname = window.btoa(encodeURIComponent(JSON.stringify($fname)));
						ajaxify({
							task: objNode.data.task,
							param: $fname,
							callback: 'afterDisplay($data.param)',
							target: 'CONTENT',
							useStore: false
						});
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

					if (objNode.data) {
						console.log('changed.jstree - ' + objNode.data.file);
					}

				})
				.on('search.jstree', function (nodes, str, res) {
					if (str.nodes.length === 0) {
						// No nodes found, hide all
						$('#TOC').jstree(true).hide_all();
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
							responsive: 0, // Strange : the UI rendering is really bad if responsive=1 on small screens
							variant: 'small',
							stripes: 1
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
						case_insensitive: true,
						show_only_matches: true, // Hide unmatched, show only matched records
						search_leaves_only: true, // Only files, not folder
						ajax: {
							url: 'index.php?task=search', // This request will be fired with the '&str=SEARCH_TERM' parameter
							dataType: 'json',
							type: (marknotes.settings.debug ? 'GET' : 'POST'),
							success: function (data) {

								// data is a JSON string, store it in the jsTree_Search_Result variable
								// used by the callback : jsTree_ajax_search
								jsTree_Search_Result = data;
								/*<!-- build:debug -->*/
								if (marknotes.settings.debug) {
									console.log('Search - success, list of IDs returned :');
									console.log(data);
								}
								/*<!-- endbuild -->*/
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

	var $type = (node.icon.substr(0, 6).toLowerCase() === "folder" ? "folder" : "file");

	var $items = {};

	$items.Collapse = {
		separator_before: false,
		separator_after: false,
		label: marknotes.message.tree_collapse,
		icon: 'fa fa-plus-square-o',
		action: function () {
			$('#TOC').jstree('close_all');
		}
	};

	$items.Expand = {
		separator_before: false,
		separator_after: true,
		label: marknotes.message.tree_expand,
		icon: 'fa fa-minus-square-o',
		action: function () {
			$('#TOC').jstree('open_all');
		}
	};

	// Open the note in a new window or, too, the folder (open then index.html)
	$items.Open_NewWindow = {
		separator_before: false,
		separator_after: true,
		label: marknotes.message.open_html,
		icon: 'fa fa-external-link',
		action: function () {
			contextMenuNewWindow(node);
		}
	};

	// ------------------------------------------------------------------------
	// Plugin Task-Treeview
	// The fnPluginTaskTreeViewContextMenu() is defined in the plugins task/treeview
	// Check if that plugin has loaded the function and if so, get extra items for the context menu
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

	// Folder or file ?
	var $type = (node.icon.substr(0, 6).toLowerCase() === "folder" ? "folder" : "file");

	// If it's a folder, open the index.html file. Otherwise node.data.url point to the note;
	// add the .html extension
	$url = node.data.url;

	// Be sure the URL is ending with / if it's a folder
	if ($url.length > 0) {
		if (($url.slice(-1) !== '/') && ($type === 'folder')) $url += '/';
	}

	$url += ($type === 'folder' ? 'index.html' : '.html');

	window.open(marknotes.webroot + marknotes.docs + '/' + $url);
}
