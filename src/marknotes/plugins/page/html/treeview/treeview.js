marknotes.arrPluginsFct.push("fnPluginTaskTreeView_init");

/**
 * Initialize the plugin by adding triggers for the rename and delete events
 */
function fnPluginTaskTreeView_init() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Treeview - Initialization');
	}
	/*<!-- endbuild -->*/

	try {
		if ($.isFunction($.fn.jstree)) {
			/*<!-- build:debug -->*/
			/*if (marknotes.settings.debug) {
				console.log('	  fnPluginTaskTreeView_init - Add events to the treeview - This function will be called only once');
			}*/
			/*<!-- endbuild -->*/

			// This function should only be fired once
			// So, now, remove it from the arrPluginsFct array
			/*marknotes.arrPluginsFct.splice(marknotes.arrPluginsFct.indexOf('fnPluginTaskTreeView_init'), 1);*/

			/*<!-- build:debug -->*/
			/*if (marknotes.settings.debug) {
				console.log('	  fnPluginTaskTreeView_init has been removed from marknotes.arrPluginsFct');
			}*/
			/*<!-- endbuild -->*/

			$('#TOC')
				.on('rename_node.jstree', function (e, data) {
					// Be sure this code is fired only once.
					e.stopImmediatePropagation();
					// The user has just click on "Create..." (folder or note)
					// jsTree first add the node then, the user will give a
					// name
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
 * fnPluginTaskTreeViewContextMenu is called by /assets/treeview.js,
 * when the user will make a right-click on the treeview.
 *
 * fnPluginTaskTreeViewContextMenu will add extra options in the
 * treeview as soon as the user is connectedw.
 *
 * Actions will be, f.i., add a new folder / note, kill one, ...
 */
function fnPluginTaskTreeViewContextMenu(node) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Treeview - Show ContextMenu');
	}
	/*<!-- endbuild -->*/

	var $items = {};

	// Only when the user is connected; don't add these items in the
	// contextual menu otherwise
	if (marknotes.settings.authenticated === 1) {
		var tree = $('#TOC').jstree(true);

		// Give the ability to create a new folder but only when the user right-click on a
		// folder : don't allow to create a folder if he click on a note

		var $type = "file";

		try {
			$type = (node.icon.substr(0, 6).toLowerCase() === "folder" ? "folder" : "file");
		} catch (e) {
			console.warn(err.message);
		}

		if ($type === 'folder') {
			$items.Add_Folder = {
				separator_before: false,
				separator_after: false,
				label: $.i18n('tree_new_folder'),
				icon: 'fa fa-folder-open-o',
				action: function () {
					var $node = tree.create_node(node, {
						text: $.i18n('tree_new_folder_name'),
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
			label: $.i18n('tree_new_note'),
			icon: 'fa fa-file-text-o',
			action: function () {

				// When the user has right-clicked on a folder, add the note within that folder
				// When it was a note, add the new note in the same folder i.e. the parent of the note
				var $node = tree.create_node(
					node, {
						text: $.i18n('tree_new_note_name'),
						icon: "file file-md",
						data: {
							task: "export.html"
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
			label: $.i18n('tree_rename'),
			icon: 'fa fa-pencil',
			action: function () {
				tree.edit(node);
			}
		};

		// Edit an existing note
		if ($type === 'file') {
			$items.Edit = {
				separator_before: false,
				separator_after: false,
				label: $.i18n('edit_file'),
				icon: 'fa fa-pencil-square-o',
				action: function () {
					fnPluginTaskTreeView_editNode(node);
				}
			};
		}

		// Upload files
		if ($type === 'folder') {
			$items.Upload = {
				separator_before: true,
				separator_after: false,
				label: $.i18n('upload_files'),
				icon: 'fa fa-upload',
				action: function () {
					fnPluginTaskTreeView_upload(node);
				}
			};
		}

		// Kill a note or folder
		$items.Remove = {
			separator_before: true,
			separator_after: false,
			label: ($type === 'folder' ? $.i18n('tree_delete_folder', node.text) : $.i18n('tree_delete_file', node.text)),
			icon: 'fa fa-trash',
			action: function () {
				noty({
					theme: 'relax',
					timeout: 0,
					layout: 'center',
					type: 'warning',
					text: '<strong>' + ($type === 'folder' ? $.i18n('tree_delete_folder_confirm', node.text) : $.i18n('tree_delete_file_confirm', node.text)) + '</strong>',
					buttons: [{
							addClass: 'btn btn-primary',
							text: $.i18n('ok'),
							onClick: function ($noty) {
								$noty.close();
								tree.delete_node(node);
							}
					},
						{
							addClass: 'btn btn-danger',
							text: $.i18n('cancel'),
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
			console.log('	  The user isn\'t connected so no actions like create a note added in the treeview');
		}
		/*<!-- endbuild -->*/
	} // if (marknotes.settings.authenticated === 1)

	return $items;

}

function fnPluginTaskTreeView_upload(node) {
	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Treeview - Upload');
		console.log(node);
	}
	/*<!-- endbuild -->*/

	ajaxify({
		task: "task.upload.drop",
		useStore: false,
		param: btoa(node.id),
		callback: 'fnPluginTaskTreeView_upload_droparea(data)'
	});

}

function fnPluginTaskTreeView_upload_droparea(data) {

	// data contains the form generated by index.php?task=task.upload.drop
	// Put this form into the content area
	$('#CONTENT').html(data);

	// And initialize DropZone
	var myDropzone = new Dropzone("#upload_droparea", {
		url: "index.php?task=task.upload.save"
	});

	// When uploading a file, check if the file is within a folder
	// i.e. if the user has dropped an entire folder.
	// If yes, get the relative filename (like /images/article.png)
	// IF the uploaded file isn't within a folder, don't add the
	// subfolder item to the form.
	myDropzone.on("sending", function (file, xhr, formData) {
		if (typeof file.fullPath !== "undefined") {
			formData.append("relativeName", file.fullPath);
		}
	});

	// And initialize DropZone
	//$("#upload_droparea").dropzone({
	//	url: "index.php?task=task.upload.save"
	//});

	return true;
}

/**
 * A node has been added, renamed or deleted in the Treeview.
 * Call the ajax request and make the change in the filesystem
 *
 * @param {type} e
 * @param {type} data
 * @param {string} $task	'rename' or 'delete'
 * @returns {undefined}
 */
function fnPluginTaskTreeView_CRUD(e, data, $task) {
	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Treeview - CRUD');
		console.log('		 Running task = ' + $task);
	}
	/*<!-- endbuild -->*/

	try {
		// The user has just click on "Create..." (folder or note)
		var $type = "file";

		try {
			$type = (data.node.icon.substr(0, 6).toLowerCase() === "folder" ? "folder" : "file");
		} catch (e) {
			console.warn(err.message);
		}

		var $root = data.node.parent;

		// In case of the creation of a new note, the "oldname" is
		// something like "new note" (and "new folder" for a folder)
		// i.e. the default name suggested by jsTree.
		// The real name will be $newname; the one typed by the user

		var $oldname = $root + data.old;

		// Get the name of the file; use the node.data.file info
		// and not node.text which is the displayed name (and
		// can be truncated)
		var $newname = $root;

		if (data.node.data !== null) {
			if (data.node.data.hasOwnProperty('file')) {
				$newname = $newname + data.node.data.file;
			}
		} else {
			$newname = $newname + data.node.text;
		}

		// Remove the starting slash character if present
		if ($newname.charAt(0) === marknotes.settings.DS) {
			$newname = $newname.substr(1);
		}

		if ($oldname.charAt(0) === marknotes.settings.DS) {
			$oldname = $oldname.substr(1);
		}

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			if ($task !== 'create') {
				console.log('		  old name = ' + $oldname);
			}
			console.log('		  new name = ' + $newname);
		}
		/*<!-- endbuild -->*/

		// Encode the name in base64 so no problem with f.i. slashes and accentuated characters
		if ($task !== 'create') {
			$oldname = window.btoa(encodeURIComponent($oldname));
		}

		$newname = window.btoa(encodeURIComponent($newname));

		// By renaming, adding or deleting a note, invalidate localStorage
		try {
			if (typeof store === 'object') {
				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.log('		  Clear localStorage');
				}
				/*<!-- endbuild -->*/

				store.clearAll();
			} // if ($useStore)
		} catch (err) {
			console.warn(err.message);
		}

		switch ($task) {
		case 'create':

			// create : we need the task, node name and type
			// after the creation, reload the treeview
			ajaxify({
				task: "task." + $type + "." + $task, // f.i. task.file.create
				param: $newname, // name of the file to create or remove
				callback: 'fnPluginTaskTreeView_reload(data)'
			});
			break;

		case 'delete':

			// delete : we need the task, node name and type
			ajaxify({
				task: "task." + $type + "." + $task, // f.i. task.file.delete
				oldname: $newname, // name of the file to create or remove
				callback: 'fnPluginTaskTreeView_showStatus(data)'
			});
			break;
		case 'rename':
			ajaxify({
				task: "task." + $type + "." + $task, // f.i. task.file.rename
				param: $newname, // The new name, name of the file/folder to create/rename
				oldname: $oldname, // The old name, previous note name
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
		console.log('	  Plugin Page html - Treeview - Reload');
	}
	/*<!-- endbuild -->*/

	var $select_node = {};
	if (data.hasOwnProperty('filename')) {
		$select_node.id = data.md5;
	}

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Reload the treeview after a creation or a rename');
	}
	/*<!-- endbuild -->*/

	ajaxify({
		filename: 'listfiles.json',
		dataType: 'json',
		callback: 'initFiles(data)',
		select_node: $select_node,
		useStore: false
	});

	return true;

}

/**
 * A CRUD (create, update or delete -no read-) operation has
 * been made on a node
 * This function is called once the ajax request has been fired and
 * terminated.
 *
 * @param {type} $data  JSON string returned by the ajax request (can be
 *					  index.php?task=rename or ?task=delete)
 * @returns {undefined}
 */
function fnPluginTaskTreeView_showStatus($data) {

	return fnPluginTaskTreeView_reload($data);

	// The ideal situation would be to not reload everything but just
	// update informations of the tree node : the id and data-basename,
	// data-file and data-url and perhaps others in the future
	/*<!-- build:debug -->*/
	//if (marknotes.settings.debug) {
	//	console.log('	  Plugin Page html - Treeview - Showstatus');
	//	console.log($data);
	//}
	/*<!-- endbuild -->*/

	/*if ($data.hasOwnProperty('status')) {
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
					message: $.i18n('loading_tree'),
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
	}*/

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
		console.log('	  Plugin Page html - Treeview - Rename node');
	}
	/*<!-- endbuild -->*/

	// data.old is the current name of the node.
	// This name will ne "New note" or "New folder" in case of new addition.
	// So in that case, the action should be "files.create";
	// "files.rename" otherwise

	var $task = '';
	if (
		(data.old === $.i18n('tree_new_note_name')) ||
		(data.old === $.i18n('tree_new_folder_name'))
	) {
		$task = 'create';
	} else {
		$task = 'rename';
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
		console.log('	  Plugin Page html - Treeview - Remove node');
	}
	/*<!-- endbuild -->*/

	fnPluginTaskTreeView_CRUD(e, data, 'delete');

	return;

}

/**
 * Edit the note, open the editor
 */
function fnPluginTaskTreeView_editNode($node) {
	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Treeview - Edit node');
		console.log($node);
	}
	/*<!-- endbuild -->*/

	var $fname = window.btoa(encodeURIComponent(JSON.stringify($node.data.file)));

	ajaxify({
		task: 'task.edit.form',
		param: $fname,
		callback: 'afterEdit($data.param)',
		target: 'CONTENT'
	});

	return true;
}
