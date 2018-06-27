	// Add a custom button that will insert a mermaid sample that
	// will be replaced by a dynamic table of contents when
	// the note is rendered
	// $POSITION$ will be replaced by the position configured in
	// settings.json->plugins->editor->mermaid->position
	toolbar.insertItem($POSITION$, {
		type: 'button',
		options: {
			name: 'MN_Edit_Mermaid',
			className: 'MN_button fa fa-diamond tui-mermaid',
			event: 'fnPluginEditButtonMermaidClicked',
			tooltip: $.i18n('button_addMermaid')
		}
	});

	$DIVIDER$

	editor.eventManager.addEventType('fnPluginEditButtonMermaidClicked');

	editor.eventManager.listen('fnPluginEditButtonMermaidClicked', function () {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('	  Plugin Editor - Mermaid');
		}
		/*<!-- endbuild -->*/

		var sampleMermaid =
			'<div class="mermaid">\n'+
			'graph TD;\n'+
			'	A-->B;\n'+
			'	A-->C;\n'+
			'	B-->D;\n'+
			'	C-->D;\n'+
			'</div>\n'+
			'(more examples on https://mermaidjs.github.io/)\n';

		// Insert the tag at the position of the cursor
		editor.insertText('\n\n'+sampleMermaid+'\n\n');

		return true;
	});
