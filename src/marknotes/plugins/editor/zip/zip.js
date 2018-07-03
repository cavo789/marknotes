	// Add a ZIP button that will allow to download the note
	// Based on https://github.com/Stuk/jszip-utils
	//
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->zip->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_ZIP',
			className: 'MN_button fa fa-download tui-zip',
			event: 'fnPluginEditButtonZIPClicked',
			tooltip: $.i18n('button_zip_note')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonZIPClicked');

	editor.eventManager.listen('fnPluginEditButtonZIPClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - ZIP');
		}
		/*<!-- endbuild -->*/

		// Add an external script, load jszip.min.js
		// @Link https://github.com/Stuk/jszip
		// Use an ajax request to load the script so it's loaded
		// only when needed.
		$.ajax({
			"type": "GET",
			"url": "marknotes/plugins/editor/zip/libs/jszip.min.js",
			"dataType": "script",
			"cache": true,
			"success": function (data) {
				// Once loaded, use the library
				var zip = new JSZip();

				// marknotes.note.basename contains the note's filename
				// without the extension, so add the .md extension
				// Use editor.getValue() for retrieving the markdown
				// content of the editor so, the line here below will
				// dynamically create a fake file with the basename
				// of the note and put in it his content.
				zip.file(marknotes.note.basename + ".md", editor.getValue());

				// Get the rendered HTML of the note
				// and loop for all images
				/*var temp = document.createElement('div');
				temp.innerHTML = editor.getHtml();

				var images = temp.getElementsByTagName( 'img' );

				for (var i=0; i<images.length; i++) {
					alert(images[i].src);
					//var img = zip.folder("images");
					//img.file("smile.gif", imgData, {base64: true});
				}
				*/

				// Generate the zip and send it to the browser
				zip.generateAsync({type:"blob"}).then(function(content) {
					saveAs(content, marknotes.note.basename + ".zip");
				});
			}
		});
	});
