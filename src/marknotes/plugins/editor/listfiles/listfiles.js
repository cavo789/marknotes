	// Add a custom button that will insert a %LISTFILES folder% tag that
	// will be replaced by a dynamic list of files that can be found in
	// the mentionned folder
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->listfiles->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_ListFiles',
			className: 'MN_button fa fa-files-o tui-listfiles',
			event: 'fnPluginEditButtonListFilesClicked',
			tooltip: $.i18n('button_addListFiles')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonListFilesClicked');

	editor.eventManager.listen('fnPluginEditButtonListFilesClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - ListFiles');
		}
		/*<!-- endbuild -->*/

		// Insert the tag at the position of the cursor
		editor.insertText('\n\n%LISTFILES foldername%\n\n');

		return true;
	});
