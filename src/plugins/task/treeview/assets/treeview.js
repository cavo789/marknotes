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

	if (marknotes.settings.authenticated === 1) {

		// Give the ability to create a new folder
		$items['Add_Folder'] = {
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

		// And a new note too
		$items['Add_Item'] = {
			separator_before: false,
			separator_after: false,
			label: marknotes.message.tree_new_note,
			icon: 'fa fa-file-text-o',
			action: function () {
				// When the user has right-clicked on a folder, add the note within that folder
				// When it was a note, add the new note in the same folder i.e. the parent of the note
				var $node = tree.create_node((node.icon === 'folder' ? node : node.parent), {
					text: marknotes.message.tree_new_note_name,
					icon: "file file-md",
					data: {
						task: "display"
					}
				});
				tree.edit($node);
			}
		};

		// Rename an existing note or folder
		$items['Rename'] = {
			separator_before: false,
			separator_after: false,
			label: marknotes.message.tree_rename,
			icon: 'fa fa-pencil',
			action: function () {
				tree.edit(node);
			}
		};

		// Kill a note or folder
		$items['Remove'] = {
			separator_before: true,
			separator_after: false,
			label: (node.icon === 'folder' ? marknotes.message.tree_delete_folder.replace('%s', node.text) : marknotes.message.tree_delete_file.replace('%s', node.text)),
			icon: 'fa fa-trash',
			action: function () {
				noty({
					theme: 'relax',
					timeout: 0,
					layout: 'center',
					type: 'warning',
					text: '<strong>' + (node.icon === 'folder' ? marknotes.message.tree_delete_folder_confirm.replace('%s', node.text) : marknotes.message.tree_delete_file_confirm.replace('%s', node.text)) + '</strong>',
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
