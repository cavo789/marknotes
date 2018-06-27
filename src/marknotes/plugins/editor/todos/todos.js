	// Add the %TODOS% tag into the toolbar of the editor
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->todos->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_TODOS',
			className: 'MN_button fa fa-check-square-o tui-todos',
			event: 'fnPluginEditButtonTODOSClicked',
			tooltip: $.i18n('button_addTODOS')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonTODOSClicked');

	editor.eventManager.listen('fnPluginEditButtonTODOSClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - TODOS');
		}
		/*<!-- endbuild -->*/

		// Insert the tag at the position of the cursor
		editor.insertText('\n\n%TODOS%\n\n');

		return true;
	});
