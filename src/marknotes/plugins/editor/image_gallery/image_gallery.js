	// Add a custom button that will insert a %GALLERY% tag that
	// will be replaced by a dynamic table of contents when
	// the note is rendered
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->image_gallery->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_ImageGallery',
			className: 'MN_button fa fa-file-image-o tui-image-gallery',
			event: 'fnPluginEditButtonImage_GalleryClicked',
			tooltip: $.i18n('button_addImage_Gallery')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonImage_GalleryClicked');

	editor.eventManager.listen('fnPluginEditButtonImage_GalleryClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - Image_Gallery');
		}
		/*<!-- endbuild -->*/

		// Insert the tag at the position of the cursor
		editor.insertText('\n\n%GALLERY your_image_folder%\n\n');

		return true;
	});
