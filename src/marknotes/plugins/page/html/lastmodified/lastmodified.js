marknotes.arrPluginsFct.push("fnPluginHTMLLastModified");

function fnPluginHTMLLastModified() {

	// This function should only be fired once
	// So, now, remove it from the arrPluginsFct
	// array
	marknotes.arrPluginsFct.splice(marknotes.arrPluginsFct.indexOf('fnPluginHTMLLastModified'), 1);

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Last_modified');
	}
	/*<!-- endbuild -->*/

	// Get the list of the last modified notes
	ajaxify({
		task: 'task.lastmodified.getlist',
		callback: 'afterShowLastModified(data)',
		dataType: 'json',
		useStore: 1,
		async: 0,
		target: 'LASTMODIFIED'
	});

	return true;
}

/**
 * Display the list of last modified notes
 * @param {json} $data  The return of the JSON returned by
 *		index.php?task=task.lastmodified.getlist
 */
function afterShowLastModified($data) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('*********** afterShowLastModified **********');
	}
	/*<!-- endbuild -->*/

	if (typeof $data === 'undefined') {
		Noty({
			message: $.i18n('json_error', 'showLastModified'),
			type: 'error'
		});
		return false;
	}

	if ($data == '') {
		// empty, return
		return true;
	} else {
		// The task lastmodified.getlist has return a list
		$html = '';

		$ul = document.createElement("ul");

		$title = $data['title'];
		//$('.content-header').html('<h1>' + $title + '</h1>');

		// Build <li>...</li> for each favorites
		$.each($data['files'], function(index, value){

			node = document.createElement("li");
			node.setAttribute('id', value['id']);
			//node.setAttribute('data-file', value['file']);
			textnode = document.createTextNode(value['file']);
			node.appendChild(textnode);

			$ul.appendChild(node);
		});

		// Put the list in the content area

		// The LASTMODIFIED id exists on the homepage (when the
		// interface is displayed). As soon as a note is displayed,
		// only the CONTENT node id exists
		if ($('#LASTMODIFIED').length !== 0) {

			$('#LASTMODIFIED').html(
				'<h2>' + $title + '</h2>' +
				'<div class="animated bounceInLeft">' +
					'<ul id="lastmodified">' + $ul.innerHTML + '</ul>' +
				'</div>');

			// And handle clicks
			$("#lastmodified li").click(function (event) {

				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.log('Select node with id ' +
						$(this).attr('id')+' in the treeview');
				}
				/*<!-- endbuild -->*/

				try {
					$(this).attr('id')
					$('#TOC').jstree('select_node', $(this).attr('id'));
				} catch (e) {
					// Problem in jstree ???
					console.warn('Error when selecting the node : ' +
						e.message);
				}
			});
		} // if ($('#LASTMODIFIED').length !== 0)
	}
	return true;
}
