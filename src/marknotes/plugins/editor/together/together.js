	// Add a custom button that will start TogetherJS
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->together->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_Together',
			className: 'MN_button fa fa-users tui-together',
			event: 'fnPluginEditButtonTogetherClicked',
			tooltip: $.i18n('button_edit_multiusers')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonTogetherClicked');

	editor.eventManager.listen('fnPluginEditButtonTogetherClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - Together');
		}
		/*<!-- endbuild -->*/

		if (typeof TogetherJS === "function") {
			// Call the TogetherJS function
			// See https://togetherjs.com/ for more information
			TogetherJS();
		} else {
			Noty({
				message: $.i18n('error_together_not_loaded'),
				type: 'error'
			});
		}

		return true;
	});
