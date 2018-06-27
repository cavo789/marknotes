	// Add a custom button that will insert a %TOC% tag that
	// will be replaced by a dynamic table of contents when
	// the note is rendered
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->toc->position

	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_TOC',
			className: 'MN_button fa fa-map-o tui-toc',
			event: 'fnPluginEditButtonTOCClicked',
			tooltip: $.i18n('button_addTOC')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonTOCClicked');

	editor.eventManager.listen('fnPluginEditButtonTOCClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - TOC');
		}
		/*<!-- endbuild -->*/

		// Insert the tag at the position of the cursor
		editor.insertText('\n\n%TOC_6%\n\n');

		return true;
	});
