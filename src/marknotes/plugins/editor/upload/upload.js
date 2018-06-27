	// Add a custom button that will call the translate task and
	// get the translated content
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
			url: "index.php?task=task.upload.save"
		});

		var $imgFileName = '';

		// Get filenames and add them into the editor
		myDropzone.on("success", function (file) {

			// The upload is successfull, retrieve the size of the image
			var $data = {};
			$data.task = 'task.image.getsize';
			$data.file = file.name;
			$data.note = marknotes.note.file;

			$size = "";

			$.ajax({
				url: marknotes.url,
				data: $data,
				method: 'POST',
				success: function(data){
					if (data.hasOwnProperty("width")) {
						// Get the JSON answer with width and height
						$size = data['width']+"x"+data['height'];
						$size = " \"" + $size + "\"";
					}

					// Generate the tag
					$img = file.name;
					$imgFileName = "!["+$img+"](%URL%.images/"+$img+$size+")\n\n";

					// Just add the img tag where the cursor is located
					editor.replaceSelection($imgFileName);
				}
			});

		});

		return true;
	});

	// Hide the upload area, show back the editor
	$(".btn-exit-upload-droparea").click(function (event) {
		$('#divEditUpload').hide();
	});
