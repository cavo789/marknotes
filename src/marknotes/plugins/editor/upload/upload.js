	// Add a custom button that will display a DropZone area
	// where the user will be able to drop any files (images or not)
	// and these files will be uploaded to the server.
	//
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->upload->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_Upload',
			className: 'MN_button fa fa-picture-o tui-upload',
			event: 'fnPluginEditButtonUploadClicked',
			tooltip: $.i18n('button_upload_image')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonUploadClicked');

	editor.eventManager.listen('fnPluginEditButtonUploadClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - Upload');
		}
		/*<!-- endbuild -->*/

		$('#divEditUpload').toggle();

		// And initialize DropZone
		var myDropzone = new Dropzone("#upload_droparea", {
			// Add a link so we can remove a file from the preview area
			"addRemoveLinks":true,
			// Max size (in MB; for instance 10)
			// http://www.dropzonejs.com/#config-maxFilesize
			"maxFilesize": marknotes.editor.upload.max_size,
			// Comma separated list of allowed mime types or file extensions.
			// http://www.dropzonejs.com/#config-acceptedFiles
			"acceptedFiles": marknotes.editor.upload.accepted_mime,
			// Allow files from a hidden folder to be dropped
			"ignoreHiddenFiles": false,
			// URL for saving the image
			"url": "index.php?task=task.upload.save",
			// Replace standard messages
			// Place holder; drop area
			"dictDefaultMessage": $.i18n('editor_dropzone_placeholder'),
			// MIME type not allowed
			"dictInvalidFileType": $.i18n('error_js_mime_not_allowed')
		});

		var $imgFileName = '';

		// Get filenames and add them into the editor
		myDropzone.on("success", function (file, data) {

			if (data.status == 1) {

				// Ok, the file has been successfully uploaded
				// Remove it from the Dropzone area
				// (Keep only files with error)
				this.removeFile(file);

				if (data.type == 'image') {
					// It's an image

					// Retrieve the size of the image
					$size = data.width+"x"+data.height;
					$size = " \"" + $size + "\"";

					// Generate the tag
					// Don't add a new line after the image
					$tag = "!["+data.basename+"](%URL%"+data.url+$size+") ";

					// Add the tag where the cursor is located
					editor.insertText($tag);

				} else {
					// It's a file

					// Generate the tag
					$tag = "["+data.basename+"](%URL%"+data.url+")\n\n";

					// Add the tag where the cursor is located
					editor.insertText($tag);
				} // if (data.type == 'image')
			} else {
				Noty({
					message: data.message,
					type: 'error',
					timeout: 10200
				});
			}

		});

		return true;
	});

	// Hide the upload area, show back the editor
	$(".btn-exit-upload-droparea").click(function (event) {
		$('#divEditUpload').hide();
	});
