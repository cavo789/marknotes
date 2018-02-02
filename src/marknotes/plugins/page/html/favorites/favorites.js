marknotes.arrPluginsFct.push("fnPluginHTMLFavorites");

function fnPluginHTMLFavorites() {

	// This function should only be fired once
	// So, now, remove it from the arrPluginsFct array
	marknotes.arrPluginsFct.splice(marknotes.arrPluginsFct.indexOf('fnPluginHTMLFavorites'), 1);

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Favorites');
	}
	/*<!-- endbuild -->*/

	// If favorites are displayed, no need to display tips
	if (marknotes.settings.hasOwnProperty('show_tips')) {
		marknotes.settings.show_tips = 0;
	}

	fnPluginHTMLFavoritesShow();

	return true;
}

/**
 * Also called by the Show Favorites button
 */
function fnPluginHTMLFavoritesShow() {

	ajaxify({
		task: 'task.favorites.show',
		callback: 'afterShowFavorites(data)',
		dataType: 'json',
		useStore: 1
	});

	return true;
}

/**
 * Display the list of favorites
 * @param {json} $data  The return of the JSON returned by
 *		index.php?task=task.favorites.show
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

	$('#CONTENT').html(
		'<div class="animated bounceInLeft">' +
			'<ul id="favorites">' + $ul.innerHTML + '</ul>' +
		'</div>');

	// And handle clicks
	$("#favorites li").click(function (event) {
		$('#TOC').jstree('select_node', $(this).attr('id'));
	});

	return true;
}
