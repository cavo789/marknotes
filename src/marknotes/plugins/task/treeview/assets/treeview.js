marknotes.arrPluginsFct.push("fnPluginTaskTreeView_init");

/**
 * Initialize the plugin by adding triggers for the rename and delete events
 */
function fnPluginTaskTreeView_init() {

	try {

		if ($.isFunction($.fn.jstree)) {

			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log('fnPluginTaskTreeView_init - Add events to the treeview');
			}
			/*<!-- endbuild -->*/

			$('#TOC')
				.on('rename_node.jstree', function (e, data) {
					// Be sure this code is fired only once.
					e.stopImmediatePropagation();
					// The user has just click on "Create..." (folder or note)
					// jsTree first add the node then, the user will give a name
					// By giving a name, the rename_node event is fired
					fnPluginTaskTreeView_renameNode(e, data);
				})
				.on('delete_node.jstree', function (e, data) {
					// Be sure this code is fired only once.
					e.stopImmediatePropagation();
					// The user has just click on "Remove..." (folder or note)
					fnPluginTaskTreeView_removeNode(e, data);
				});

		} // if ($.isFunction($.fn.jstree))

	} catch (err) {
		console.warn(err.message);
	}

	return true;

}

/**
 * fnPluginTaskTreeViewContextMenu is called by /assets/treeview.js, when the user
 * will make a right-click on the treeview.
 *
 * fnPluginTaskTreeViewContextMenu will add extra options in the treeview as soon as the
 * user is connectedw.
 *
 * Actions will be, f.i., add a new folder / note, kill one, ...
 */
function fnPluginTaskTreeViewContextMenu(node) {

	var $items = {};

	// Only when the user is connected; don't add these items in the contextual menu otherwise
	if (marknotes.settings.authenticated === 1) {

		var tree = $('#TOC').jstree(true);

		// Give the ability to create a new folder but only when the user right-click on a
		// folder : don't allow to create a folder if he click on a note

		var $type = (node.icon.substr(0, 6).toLowerCase() === "folder" ? "folder" : "file");

		if ($type === 'folder') {
			$items.Add_Folder = {
				separator_before: false,
				separator_after: false,
				label: marknotes.message.tree_new_folder,
				icon: 'fa fa-folder-open-o',
				action: function () {
					var $node = tree.create_node(node, {
						text: marknotes.message.tree_new_folder_name,
						icon: "folder"
					});
					tree.edit($node);
				}
			};
		}

		// And a new note too
		$items.Add_Item = {
			separator_before: false,
			separator_after: false,
			label: marknotes.message.tree_new_note,
			icon: 'fa fa-file-text-o',
			action: function () {

				// When the user has right-clicked on a folder, add the note within that folder
				// When it was a note, add the new note in the same folder i.e. the parent of the note
				var $node = tree.create_node(
					//($type === 'folder' ? node : node.parent),
					node, {
						text: marknotes.message.tree_new_note_name,
						icon: "file file-md",
						data: {
							task: "display"
						}
					}
				);
				tree.edit($node);
			}
		};

		// Rename an existing note or folder
		$items.Rename = {
			separator_before: false,
			separator_after: false,
			label: marknotes.message.tree_rename,
			icon: 'fa fa-pencil',
			action: function () {
				tree.edit(node);
			}
		};

		// Kill a note or folder
		$items.Remove = {
			separator_before: true,
			separator_after: false,
			label: ($type === 'folder' ? marknotes.message.tree_delete_folder.replace('%s', node.text) : marknotes.message.tree_delete_file.replace('%s', node.text)),
			icon: 'fa fa-trash',
			action: function () {
				noty({
					theme: 'relax',
					timeout: 0,
					layout: 'center',
					type: 'warning',
					text: '<strong>' + ($type === 'folder' ? marknotes.message.tree_delete_folder_confirm.replace('%s', node.text) : marknotes.message.tree_delete_file_confirm.replace('%s', node.text)) + '</strong>',
					buttons: [{
							addClass: 'btn btn-primary',
							text: marknotes.message.ok,
							onClick: function ($noty) {
								$noty.close();
								tree.delete_node(node);
							}
								},
						{
							addClass: 'btn btn-danger',
							text: marknotes.message.cancel,
							onClick: function ($noty) {
								$noty.close();
							}
								}
							]
				}); // noty()
			} // action()
		};

	} else {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('The user isn\'t connected so no actions like create a note added in the treeview');
		}
		/*<!-- endbuild -->*/

	} // if (marknotes.settings.authenticated === 1)

	return $items;

}

/**
 * A node has been added, renamed or deleted in the Treeview. Call the ajax request and
 * make the change in the filesystem
 *
 * @param {type} e
 * @param {type} data
 * @param {string} $task    'rename' or 'delete'
 * @returns {undefined}
 */
function fnPluginTaskTreeView_CRUD(e, data, $task) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('Running task = ' + $task);
	}
	/*<!-- endbuild -->*/

	try {

		// The user has just click on "Create..." (folder or note)

		var $type = (data.node.icon.substr(0, 6).toLowerCase() === "folder" ? "folder" : "file");
		var $root = data.node.parent;

		// In case of the creation of a new note, the "oldname" is something like
		// "new note" (and "new folder" for a folder) i.e. the default name suggested by jsTree.
		//
		// The real name will be $newname; the one typed by the user

		var $oldname = $root + data.old;
		var $newname = $root + data.node.text;

		// Remove the starting slash character if present
		if ($newname.charAt(0) === marknotes.settings.DS) {
			$newname = $newname.substr(1);
		}

		if ($oldname.charAt(0) === marknotes.settings.DS) {
			$oldname = $oldname.substr(1);
		}

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			if ($task !== 'files.create') {
				console.log('    old name = ' + $oldname);
			}
			console.log('    new name = ' + $newname);
		}
		/*<!-- endbuild -->*/

		// Encode the name in base64 so no problem with f.i. slashes and accentuated characters
		if ($task !== 'files.create') {
			$oldname = window.btoa(encodeURIComponent($oldname));
		}
//		$newname = window.btoa(encodeURIComponent(JSON.stringify($newname)));
		$newname = window.btoa(encodeURIComponent($newname));

		switch ($task) {
		case 'files.create':

			// create : we need the task, node name and type
			// after the creation, reload the treeview
			ajaxify({
				task: $task, // f.i. 'files.delete'
				param: $newname, // name of the file to create or remove
				type: $type, // 'file' or 'folder'
				callback: 'fnPluginTaskTreeView_reload(data)'
			});
			break;

		case 'files.delete':

			// delete : we need the task, node name and type
			ajaxify({
				task: $task, // f.i. 'files.delete'
				param: $newname, // name of the file to create or remove
				type: $type, // 'file' or 'folder'
				callback: 'fnPluginTaskTreeView_showStatus(data)'
			});
			break;
		case 'files.rename':
			ajaxify({
				task: $task, // 'files.rename'
				param: $newname, // The new name, name of the file/folder to create/rename
				oldname: $oldname, // The old name, previous note name
				type: $type, // 'file' or 'folder'
				callback: 'fnPluginTaskTreeView_showStatus(data)'
			});
			break;
		}

	} catch (err) {
		console.warn(err.message);
	}

}

/**
 * Reload the treeview
 */
function fnPluginTaskTreeView_reload(data) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('Reload the treeview after a creation or a rename');
	}
	/*<!-- endbuild -->*/

	ajaxify({
		task: 'listFiles',
		dataType: 'json',
		callback: 'initFiles(data)',
		useStore: false
	});

	return true;

}

/**
 * A CRUD (create, update or delete -no read-) operation has been made on a node
 * This function is called once the ajax request has been fired and terminated.
 *
 * @param {type} $data       JSON string returned by the ajax request (can be index.php?task=rename or ?task=delete)
 * @returns {undefined}
 */
function fnPluginTaskTreeView_showStatus($data) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('fnPluginTaskTreeView_showStatus');
		console.log($data);
	}
	/*<!-- endbuild -->*/

	if ($data.hasOwnProperty('status')) {
		$status = $data.status;
		if ($status == 1) {
			Noty({
				message: $data.msg,
				type: 'success'
			});

			if (($data.type === "folder") && (($data.action === "rename") || ($data.action === "create"))) {
				$('#TOC li')
					.each(function () {
						$("#TOC")
							.jstree()
							.disable_node(this.id);
					});

				ajaxify({
					task: 'listFiles',
					callback: 'initFiles(data)',
					useStore: false // After a creation, don't use the localStorage, we need to get the new list
				});
				Noty({
					message: marknotes.message.loading_tree,
					type: 'info'
				});

				//  $('#TOC').jstree('refresh');
			}
		} else {
			Noty({
				message: $data.msg,
				type: 'error'
			});
		}
	}

}

/**
 * A note has been renamed OR inserted
 *
 * @param {type} e
 * @param {type} data
 * @returns {undefined}
 */
function fnPluginTaskTreeView_renameNode(e, data) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('fnPluginTaskTreeView_renameNode has been called');
	}
	/*<!-- endbuild -->*/

	// data.old is the current name of the node.
	// This name will ne "New note" or "New folder" in case of new addition.
	// So in that case, the action should be "files.create"; "files.rename" otherwise

	var $task = '';
	if (
		(data.old === marknotes.message.tree_new_note_name) ||
		(data.old === marknotes.message.tree_new_folder_name)
	) {
		$task = 'files.create';
	} else {
		$task = 'files.rename';
	}

	fnPluginTaskTreeView_CRUD(e, data, $task);

	return;

}

/**
 * Removing an entire folder or just a note
 * This function is fired after the confirmation of the user
 *
 * @param {type} e
 * @param {type} data
 * @returns {undefined}
 */
function fnPluginTaskTreeView_removeNode(e, data) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('fnPluginTaskTreeView_removeNode has been called');
	}
	/*<!-- endbuild -->*/

	fnPluginTaskTreeView_CRUD(e, data, 'files.delete');

	return;

}
