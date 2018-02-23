marknotes.arrPluginsFct.push("fnPluginHTMLFavorites");
marknotes.arrPluginsFct.push("fnPluginHTMLFavoritesShowIcon");

function fnPluginHTMLFavorites() {

	// This function should only be fired once
	// So, now, remove it from the arrPluginsFct array
	marknotes.arrPluginsFct.splice(marknotes.arrPluginsFct.indexOf('fnPluginHTMLFavorites'), 1);

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Favorites');
	}
	/*<!-- endbuild -->*/

	// Display the list of favorites only if there is something
	// to display
	if (marknotes.favorites_count > 0) {
		// If favorites are displayed, no need to display tips
		if (marknotes.settings.hasOwnProperty('show_tips')) {
			//marknotes.settings.show_tips = 0;
		}

		fnPluginHTMLFavoritesShow();
	}

	return true;
}

function fnPluginHTMLFavoritesShowIcon() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Favorites - ShowIcon');
	}
	/*<!-- endbuild -->*/

	if (marknotes.note.file!=="") {
		ajaxify({
			task: 'task.favorites.geticon',
			param: window.btoa(encodeURIComponent(JSON.stringify(marknotes.note.file))),
			callback: 'afterGetFavoritesIcon(data)',
			dataType: 'json',
			useStore: 0
		});
	}

	return true;
}

function afterGetFavoritesIcon($data) {
	// $data is a JSON answer with, at least :
	//
	//	* title : "Remove this note from yours favorites"  i.e.
	//		what will do the button
	//	* icon  : The font-awesome icon (star or star-o f.i.)
	//	* task  : The task to launch when the user will click on the button

	$fav_title = $data.title;
	$fav_title = $fav_title.replace("'", "''");

	$fav_icon = $data.icon;
	$fav_task = $data.task;

	$title = $('.content-header h1').text();
	$title = $title + ' <i id="favNoteIcon" data-task="' + $fav_task + '" ' +
		'class="fa fa-' + $fav_icon + '" ' +
		'title="' + $fav_title + '" aria-hidden="true"></i>';

	$('.content-header h1').html($title);

	// And handle clicks
	$("#favNoteIcon").click(function (event) {
		// Add or Remove the note into the favorites
		var $data = {};
		$data.task = 'task.favorites.' + $(this).attr('data-task');
		$data.param =
		window.btoa(encodeURIComponent(JSON.stringify(marknotes.note.file)));

		var $icon = $(this);

		$.ajax({
			async: true,
			type: (marknotes.settings.debug ? 'GET' : 'POST'),
			url: marknotes.url,
			data: $data,
			// If a key is mentionned, result is a JSON info
			datatype: 'json',
			success: function (data) {

				Noty({
					message: data.message,
					type: (data.status == 1 ? 'success' : 'error')
				});

				if (data.hasOwnProperty('task')) {
					// data.task / data.icon and data.title are
					// returned by the task.
					// When the task was "remove", the JSON answer provide
					// infos so the button can be used to add the note again
					// And same if task was "add", JSON contains info for the remove
					// action
					$fav_icon = data.icon;
					$($icon).attr('class', 'fa fa-' + data.icon);
					$($icon).attr('title', data.title);
					$($icon).attr('data-task', data.task);
				}
			}
		}); // $.ajax()

	});

	return true;
}

/**
 * Also called by the Show Favorites button
 */
function fnPluginHTMLFavoritesShow() {
	ajaxify({
		task: 'task.favorites.getlist',
		callback: 'afterShowFavorites(data)',
		dataType: 'json',
		useStore: 1
	});

	return true;
}

/**
 * Display the list of favorites
 * @param {json} $data  The return of the JSON returned by
 *		index.php?task=task.favorites.getlist
 */
function afterShowFavorites($data) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('*********** afterShowFavorites **********');
	}
	/*<!-- endbuild -->*/

	if (typeof $data === 'undefined') {
		Noty({
			message: $.i18n('json_error', 'showFavorites'),
			type: 'error'
		});
		return false;
	}

	$html = '';

	$ul = document.createElement("ul");

	$title = $data['title'];
	$('.content-header').html('<h1>' + $title + '</h1>');

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

	// The FAVORITES id exists on the homepage (when the interface
	// is displayed). As soon as a note is displayed, only the
	// CONTENT node id exists
	$domID = ($('#FAVORITES').length == 0) ? '#CONTENT' : '#FAVORITES';

	$($domID).html(
		'<h2>' + $title + '</h2>' + 
		'<div class="animated bounceInLeft">' +
			'<ul id="favorites">' + $ul.innerHTML + '</ul>' +
		'</div>');

	// And handle clicks
	$("#favorites li").click(function (event) {

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

	return true;
}
