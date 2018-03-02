function fnPluginButtonFileManager($params) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - FileManager');
	}
	/*<!-- endbuild -->*/

	ajaxify({
		task: 'task.elf.show',
		callback: 'afterFileManager($data, data)',
		dataType: 'json',
		useStore: false,
		target: 'CONTENT'
	});

	return true;
}

/**
 * Display the file manager in the content part
 */
function afterFileManager($ajax_request, $json) {

	// $json contains the answer of index.php?task=task.elf.show

	$('.content-header h1').html($json.title);
	$('#CONTENT').html($json.html);

	// Maximize the iframe
	var $rect = document.getElementById('CONTENT').getBoundingClientRect();
	var $width = $rect.width;
	var $height = $rect.height;

	$('#FileManager').width($width-50).height($height);
	return true;
}
