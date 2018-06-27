	// Add a custom button that will insert a %CSV file.csv% tag that
	// will be replaced by a HTML table with the content of that file
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->csv->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_CSV',
			className: 'MN_button fa fa-file-text-o tui-csv',
			event: 'fnPluginEditButtonCSVClicked',
			tooltip: $.i18n('button_addCSV')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonCSVClicked');

	editor.eventManager.listen('fnPluginEditButtonCSVClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - CSV');
		}
		/*<!-- endbuild -->*/

		// Insert the tag at the position of the cursor
		editor.insertText('\n\n%CSV filename.csv{"separator":";"}%\n\n');

		return true;
	});
