	// Add a custom button that will insert a %LASTUPDATE% tag that
	// will be replaced by the last udpate date/time of the note when
	// the note is rendered
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->lastupdate->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_LastUpdate',
			className: 'MN_button fa fa-clock-o tui-lastupdate',
			event: 'fnPluginEditButtonLastUpdateClicked',
			tooltip: $.i18n('button_addLastUpdate')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonLastUpdateClicked');

	editor.eventManager.listen('fnPluginEditButtonLastUpdateClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - LastUpdate');
		}
		/*<!-- endbuild -->*/

		// Insert the tag at the position of the cursor
		editor.insertText('\n\n%LASTUPDATE%\n\n');

		return true;
	});
