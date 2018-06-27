	// Add a custom button that will insert a %INCLUDE note.md% tag
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->include->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_Include',
			className: 'MN_button fa fa-code-fork tui-include',
			event: 'fnPluginEditButtonIncludeClicked',
			tooltip: $.i18n('button_addInclude')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonIncludeClicked');

	editor.eventManager.listen('fnPluginEditButtonIncludeClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - Include');
		}
		/*<!-- endbuild -->*/

		// Insert the tag at the position of the cursor
		editor.insertText('\n\n%INCLUDE a_note.md{"increment_headings":"1"}% (or %INCLUDE folder/*.md{"recursive":"1","increment_headings":"1"}%)\n\n');

		return true;
	});
