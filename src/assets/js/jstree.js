var jsTree_Search_Result = '';

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
function jstree_init($data)
{

    try {
        if ($.isFunction($.fn.jstree)) {
            $('#TOC').jstree("destroy").empty();
            $('#TOC').jstree("true");

            // Use jsTree for the display

            $('#TOC').on('changed.jstree', function (e, data) {

                var objNode = data.instance.get_node(data.selected);

                if (typeof(objNode.parent)!=="undefined") {
                    // Get the filename : objNode.parent mention the relative parent folder (f.. /development/jquery/)
                    // and objNode.text the name of the file (f.i. jsTree.md)
                    /*<!-- build:debug -->*/
                    if (markdown.settings.debug) {
                        console.log('Tree - Selected item : ' +objNode.parent+objNode.text);
                    }
                    /*<!-- endbuild -->*/

                    var $fname=objNode.parent+objNode.text+'.md';

                    $fname=window.btoa(encodeURIComponent(JSON.stringify($fname)));
                    ajaxify({task:objNode.data.task,param:$fname,callback:'afterDisplay($data.param)',target:'CONTENT', useStore:false});
                } // if (typeof(objNode.parent)!="undefined")

            }).on('click', '.jstree-anchor', function (e) {

                // By clicking (single-click) on a folder, open / close it
                $(this).jstree(true).toggle_node(e.target);

            }).on('keydown.jstree', '.jstree-anchor', function (e) {

                // @TODO : Problem : e.currentTarget is not yet the current one but the one when the move was done.
                // If I was on chidl3 and press the down key, I need to capture child4 (the next one) and e.currentTarget is still on child3.
                // Not found a solution...
                var objNode = $('#TOC').jstree(true).get_node(e.currentTarget);

                if (objNode.data) {
                    console.log('changed.jstree - '+objNode.data.file);
                }

            }).on('create_node.jstree', function (e, data) {
               // The user has just click on "Create..." (folder or note)
               // This event is fired before i.e. immediatly when the node is being created
                jstree_create_node(e, data);
            }).on('rename_node.jstree', function (e, data) {
               // The user has just click on "Create..." (folder or note)
                jstree_rename_node(e, data);
            }).on('delete_node.jstree', function (e, data) {
               // The user has just click on "Remove..." (folder or note)
                jstree_remove_node(e, data);
            }).on('search.jstree', function (nodes, str, res) {
                if (str.nodes.length===0) {
                    // No nodes found, hide all
                    $('#TOC').jstree(true).hide_all();
                    // except the first node (show it)
                    $("#TOC").jstree("show_node", "ul > li:first");
                }
            }).jstree({
                core: {
                    animation : 1,
                    data : $data.tree,
                    check_callback : function (operation, node, node_parent, node_position, more) {

                        // Return true to allow the operation, return false otherwise
                        //
                        // Allow changes on the jsTree by javascript
                        // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'

                        return operation === 'move_node' ? false : true;
                    },
                    multiple:false,
                    initially_open : ['phtml_1'],    // Automatically open the root node
                    sort : function (a, b) {
                        return this.get_type(a) === this.get_type(b) ? (this.get_text(a) > this.get_text(b) ? 1 : -1) : (this.get_type(a) >= this.get_type(b) ? 1 : -1);
                    },
                    themes : {
                        responsive : 1,
                        variant : 'small',
                        stripes : 1
                    },
                    types : {
                        default : { icon : 'folder' },
                        file : { icon : 'file file-md' },
                        folder : { icon : 'folder' }
                    }
                },
                plugins : ['contextmenu','state','dnd','search','types','unique','wholerow'],
                search: {
                    case_insensitive : true,
                    show_only_matches : true,  // Hide unmatched, show only matched records
                    search_leaves_only : true,  // Only files, not folder
                    ajax: {
                        url: 'index.php?task=search',   // This request will be fired with the '&str=SEARCH_TERM' parameter
                        dataType: 'json',
                        type: 'POST',
                        success : function(data){

                            // data is a JSON string, store it in the jsTree_Search_Result variable
                            // used by the callback : jsTree_ajax_search
                            jsTree_Search_Result = data;
                            /*<!-- build:debug -->*/
                            if (markdown.settings.debug) {
                                console.log('jsTree - Search - success, list of IDs returned :');
                                console.log(data);
                            }
                        } // success
                    },
                    search_callback : jsTree_ajax_search
                },
                contextmenu: {
                    items: jstree_context_menu
                }
            });
        } // // if ($.isFunction($.fn.jstree))
    } catch (err) {
        console.warn(err.message);
        /*<!-- build:debug -->*/
        if (markdown.settings.debug) {
            Noty({message:err.message, type:'error'});
        }
        /*<!-- endbuild -->*/
    }

} // function jstree_init()

/**
 * Context menu for the treeview.  This function will build the contextual menu
 * and return the list of entries of that menu
 *
 * @param {type} node             The node on which the user has right-clicked
 * @returns {context_menu.items}
 */
function jstree_context_menu(node)
{

    var tree = $('#TOC').jstree(true);

    var items = {
        Add_Folder: {
            separator_before: false,
            separator_after: false,
            label: markdown.message.tree_new_folder,
            icon:'fa fa-folder-open-o',
            action: function () {
                var $node = tree.create_node(node, {
                    text: markdown.message.tree_new_folder_name,
                    icon: "folder"
                });
                tree.edit($node);
            }
        }, // Add_Folder
        Add_Item: {
            separator_before: false,
            separator_after: false,
            label: markdown.message.tree_new_note,
            icon:'fa fa-file-text-o',
            action: function () {
                // When the user has right-clicked on a folder, add the note within that folder
                // When it was a note, add the new note in the same folder i.e. the parent of the note
                var $node = tree.create_node((node.icon==='folder'?node:node.parent), {
                    text: markdown.message.tree_new_note_name,
                    icon: "file file-md",
                    data: {
                        task:"display"
                    }
                });
                tree.edit($node);
            }
        }, // Add_Item
        Rename: {
            separator_before: false,
            separator_after: false,
            label: markdown.message.tree_rename,
            icon: 'fa fa-pencil',
            action: function () {
                tree.edit(node);
            }
        }, // Rename
        Remove: {
            separator_before: true,
            separator_after: false,
            label: (node.icon==='folder' ? markdown.message.tree_delete_folder.replace('%s', node.text) : markdown.message.tree_delete_file.replace('%s', node.text)),
            icon: 'fa fa-trash',
            action: function () {
                noty({
                    theme: 'relax',
                    timeout: 0,
                    layout: 'center',
                    type:'warning',
                    text: '<strong>'+(node.icon==='folder' ? markdown.message.tree_delete_folder_confirm.replace('%s', node.text) : markdown.message.tree_delete_file_confirm.replace('%s', node.text))+'</strong>',
                    buttons: [
                    {
                        addClass: 'btn btn-primary',
                        text: markdown.message.ok,
                        onClick: function ($noty) {
                            $noty.close();
                            tree.delete_node(node);
                        }
                    },
                    {
                        addClass: 'btn btn-danger',
                        text: markdown.message.cancel,
                        onClick: function ($noty) {
                            $noty.close();
                        }
                    }
                    ]
                }); // noty()
            } // action()
        } // Remove
    };

    // Create a new folder : not if the user has right-clicked on a note.
    if (node.icon!=='folder') {
        delete items.Add_Folder;
    }

    return items;

} // function context_menu()

/**
 * The user has just click on "Create..." (folder or note)
 * This event is fired before i.e. immediatly when the node is being created
 *
 * @param {type} e
 * @param {type} data
 * @returns {undefined}
 */
function jstree_create_node(e, data)
{

    /*<!-- build:debug -->*/
    if (markdown.settings.debug) {
        console.log('jstree_create_node');
    }
    /*<!-- endbuild -->*/

    return;
}

/**
 * A node has been added, renamed or deleted.  Can be a folder or a note
 *
 * @param {type} e
 * @param {type} data
 * @param {string} $task    'rename' or 'delete'
 * @returns {undefined}
 */
function jstree_CRUD_node(e, data, $task)
{

    /*<!-- build:debug -->*/
    if (markdown.settings.debug) {
        console.log('jstree_CRUD_node');
    }
    /*<!-- endbuild -->*/

    try {
        // The user has just click on "Create..." (folder or note)

        var $type=(data.node.icon==="folder" ? "folder" : "file");
        var $oldname='';
        var $newname='';

        if ($type==='folder') {
            $oldname=data.node.parent+data.old;
            $newname=data.node.parent+data.node.text;
        } else { // if($type==='folder')

            // Working on a note : retrieve the parent and append the note's name
            var $parentNode = $('#TOC').jstree(true).get_node(data.node.parent);

            $oldname=$parentNode.parent+$parentNode.text+markdown.settings.DS+data.old;
            $newname=$parentNode.parent+$parentNode.text+markdown.settings.DS+data.node.text;
        }

        $oldname=window.btoa(encodeURIComponent(JSON.stringify($oldname)));
        $newname=window.btoa(encodeURIComponent(JSON.stringify($newname)));

        if ($task!=='rename') {
            ajaxify({task: $task, param: $newname, param3: $type, callback: 'jstree_show_status(data)'});
        } else {
            // Only for rename action : we need the old name and the new one
            ajaxify({task: $task, param: $oldname, param2: $newname, param3: $type, callback: 'jstree_show_status(data)'});
        }
    } catch (err) {
        console.warn(err.message);
    }
} // function jstree_CRUD_node()

/**
 * A CRUD (create, update or delete -no read-) operation has been made on a node
 * This function is called once the ajax request has been fired and terminated.
 *
 * @param {type} $data       JSON string returned by the ajax request (can be index.php?task=rename or ?task=delete)
 * @returns {undefined}
 */
function jstree_show_status($data)
{

    if ($data.hasOwnProperty('status')) {
        $status=$data.status;

        if ($status==1) {
            Noty({message:$data.msg, type:'success'});

            if (($data.type==="folder") && (($data.action==="rename")||($data.action==="create"))) {
                $('#TOC li').each(function () {
                    $("#TOC").jstree().disable_node(this.id);
                })

                ajaxify({task:'listFiles',callback:'initFiles(data)'});
                Noty({message:markdown.message.loading_tree, type:'info'});

              //  $('#TOC').jstree('refresh');
            }
        } else {
            Noty({message:$data.msg, type:'error'});
        }
    }

} // function jstree_show_status()

/**
 * A note has been renamed OR inserted
 *
 * @param {type} e
 * @param {type} data
 * @returns {undefined}
 */
function jstree_rename_node(e, data)
{

    /*<!-- build:debug -->*/
    if (markdown.settings.debug) {
        console.log('jstree_rename_node');
    }
    /*<!-- endbuild -->*/

    jstree_CRUD_node(e, data, 'rename');

    return;

} // function jstree_rename_node()

/**
 * Removing an entire folder or just a note
 * This function is fired after the confirmation of the user
 *
 * @param {type} e
 * @param {type} data
 * @returns {undefined}
 */
function jstree_remove_node(e, data)
{

    /*<!-- build:debug -->*/
    if (markdown.settings.debug) {
        console.log('jstree_remove_node');
    }
    /*<!-- endbuild -->*/

    jstree_CRUD_node(e, data, 'delete');

    return;

} // function jstree_remove_node()
