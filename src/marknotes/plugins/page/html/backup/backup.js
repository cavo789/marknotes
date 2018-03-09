//marknotes.arrPluginsFct.push("fnPluginTaskBackup");

function fnPluginTaskBackup() {

	$.ajax({
		beforeSend: function () {
			// Remove the form if already present
			if ($('#backup_form').length) {
				$('#backup_form').remove();
			}
		},
		type: "POST",
		url: "index.php",
		data: "task=task.backup.getform",
		dataType: "json",
		success: function (data) {
			if (data.hasOwnProperty("form")) {
				// The result of the task 'task.backup.getform' is a HTML
				// string : the backup screen.
				// Add that form to the parent of the content DOM element
				$("#CONTENT").html(data['form']);

				// Retrieve the heading 1 from the loaded file
				var $title = $('#CONTENT h1').text();
				if ($title !== '') {
					$('#CONTENT h1').hide();
				}
				$('title').text($title);
				$('.content-header h1').text($title);

				// When the user select a second folder from the
				// folders list, reinitialize the backup button so
				// the user can save a second folder, a third one, ...
				$("#backup_folder").change(function(e) {
					if ($('#status').length) {
						// off() is important in order to remove the
						// previous onclick() handler
						$('#status').off();
						$('#status').attr('disabled', false);
						$('#status').attr('class', 'btn btn-primary');
						$('#status').html($('#btn_start_text').val());
						$('#status').attr('id', 'backup_start');

						// Reinitialize the click on the Start button
						fnPluginTaskBackupInitStart();
					}
				});

				// Initialize the click event on the button
				fnPluginTaskBackupInitStart();

			} else {
				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.error('	  Invalid JSON returned by the backup.getform task');
				}
				/*<!-- endbuild -->*/
			}
		}
	});
	return true;
}

function fnPluginTaskBackupInitStart() {
	// Initialize the click event on the button
	$("#backup_start").click(function(e) {
		e.stopImmediatePropagation();
		$('#backup_start').attr('disabled', true);
		$('#backup_start').attr('id', 'status');
		fnPluginTaskBackupStart();
	});

	return true;
}

/**
 * The user has clicked on the "Start the archive" button
 * First action : get the list of files
 */
function fnPluginTaskBackupStart() {

	// Retrieve the value selected in the combobox
	$folder = $('#backup_folder').val();
	// List of extensions to ignore
	$ignore_extensions = $('#ignore_extensions').val();

	// First get the list of files to create
	var $data = {};
	$data.task = 'task.backup.getfiles';
	$data.folder = window.btoa(encodeURIComponent(JSON.stringify($folder)));
	$data.ignore_extensions = window.btoa(encodeURIComponent(JSON.stringify($ignore_extensions)));

	$.ajax({
		beforeSend: function () {
			// Clear the previous textarea
			$('#backup_history').empty();
		}, // beforeSend()
		async: true,
		cache: false,
		type: (marknotes.settings.debug ? 'GET' : 'POST'),
		url: marknotes.url,
		data: $data,
		datatype: 'json',
		success: function (data) {
			if (data.status == 1) {
				// Ok, we've the list of files, we can start the
				// backup process
				fnPluginTaskBackupDoIt(0);
			} else {
				Noty({
					message: data.message,
					type: 'error'
				});
			}
		}
	});

	return true;
}

/**
 * The user has clicked on the "Start the archive" button
 * Second action : once the list of files has been created, start
 * the process of adding these files into an archive
 */
function fnPluginTaskBackupDoIt($offset) {

	// First get the list of files to create
	var $data = {};
	$data.task = 'task.backup.addfile';
	$data.offset = $offset;

	$index = 0;

	$.ajax({
		beforeSend: function () {
		}, // beforeSend()
		async: true,
		cache: false,
		type: (marknotes.settings.debug ? 'GET' : 'POST'),
		url: marknotes.url,
		data: $data,
		datatype: 'json',
		success: function (data) {
			$.each(data, function(index, json_file) {
				// Remember the index because we'll need it
				// outside the ^$.each() loop
				$index = index;

				// The task.backup.addfiles return a JSON
				// array like this :
				//
				// [
				//		{
				//			"end":false,
				//			"log_info":".htaccess -success",
				//			"btn_text":"1/7",
				//			"btn_bootstrap_class":"btn-default",
				//			"offset":1
				//		},
				//		{
				//			"end":false,
				//			"log_info":"settings.json -success",
				//			"btn_text":"2/7",
				//			"btn_bootstrap_class":"btn-default",
				//			"offset":2
				//		},
				//		...
				// ]
				// log_info : contains the name of the processed file
				// the one that has been added in the ZIP file; with an
				// indicator (success, failure, ...)
				$('#backup_history').append(json_file.log_info + "\n");

				// Retrieve the offset of the next file to process
				// Will be 0 when all files have been processed
				$offset = json_file.offset;
			});

			$('#backup_history').scrollTop($('#backup_history')[0].scrollHeight);

			// btn_text will contains the processed file number ("#1")
			// and the number of files to process (so f.i. 1/7)
			$('#status').html(data[$index].btn_text);

			// CSS class to give to the status button
			$('#status').attr('class', 'btn ' + data[$index].btn_bootstrap_class);

			// Not yet done => process the next file
			if ($offset !== 0) {
				fnPluginTaskBackupDoIt($offset);
			} else {
				// Propose the download link
				$('#status').attr('disabled', false);

				// off() is important in order to remove the
				// previous onclick() handler
				$('#status').off();
				$("#status").click(function(e) {
					e.stopImmediatePropagation();
					fnPluginTaskBackupDownload();
				});
			}
		}
	});

	return true;
}

function fnPluginTaskBackupDownload() {
	window.location = marknotes.url + '?task=task.backup.download';
	return true;
}
