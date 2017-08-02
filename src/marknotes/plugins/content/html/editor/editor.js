/**
 * $params is a JSON object initiliazed by the /assets/js/marknotes.js file.
 */
function fnPluginButtonEdit($params) {

	ajaxify({
		task: 'edit.form',
		param: $params.fname,
		callback: 'afterEdit($data.param)',
		target: 'CONTENT'
	});

	return true;
}

/**
 * EDIT MODE - Render the textarea in an editor
 *
 * @param {type} $fname   Filename
 * @returns {Boolean}
 */
function afterEdit($fname) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('In function afterEdit()');
	}
	/*<!-- endbuild -->*/

	// Create the Simple Markdown Editor
	// @link https://github.com/NextStepWebs/simplemde-markdown-editor

	var simplemde = new SimpleMDE({
		autoDownloadFontAwesome: false,
		autofocus: true,
		element: document.getElementById("sourceMarkDown"),
		indentWithTabs: false,
		codeSyntaxHighlighting: false,
		toolbar: [{
				// Add a custom button for saving
				name: "Save",
				action: function customFunction(editor) {
					buttonSave($fname, simplemde.value());
				},
				className: "fa fa-floppy-o",
				title: marknotes.message.button_save
			},
			{
				// Encrypt
				name: "Encrypt",
				action: function customFunction(editor) {
					buttonEncrypt(editor);
				},
				className: "fa fa-user-secret",
				title: marknotes.message.button_encrypt
			},
			{
				// Table of content
				name: "AddTOC",
				action: function customFunction(editor) {
					buttonAddTOC(editor);
				},
				className: "fa fa-map-o",
				title: marknotes.message.button_addTOC
			},
			"|",
			{
				// Add a custom button for saving
				name: "Exit",
				action: function customFunction(editor) {
					$('#sourceMarkDown').parent().hide();
					ajaxify({
						task: 'display',
						param: $fname,
						callback: 'afterDisplay($data.param)',
						target: 'CONTENT'
					});
				},
				className: "fa fa-sign-out",
				title: marknotes.message.button_exit_edit_mode
			},
			"|", "preview", "side-by-side", "fullscreen", "|",
			"bold", "italic", "strikethrough", "|", "heading", "heading-smaller", "heading-bigger", "|", "heading-1", "heading-2", "heading-3", "|",
			"code", "quote", "unordered-list", "ordered-list", "clean-block", "|", "link", "image", "table", "horizontal-rule"
		] // toolbar
	});

	$('.editor-toolbar').addClass('fa-2x');

	return true;

}

/**
 * EDIT MODE - Save the new content.  Called by the "Save" button of the simplemde editor, initialized in the afterEdit function)
 *
 * @param {type} $fname        Filename
 * @param {type} $markdown     The new content
 * @returns {boolean}
 */
function buttonSave($fname, $markdown) {

	var $data = {};
	$data.task = 'edit.save';
	$data.param = $fname;
	$data.markdown = window.btoa(encodeURIComponent(JSON.stringify($markdown)));

	$.ajax({
		async: true,
		type: 'POST',
		url: marknotes.url,
		data: $data,
		datatype: 'json',
		success: function (data) {
			Noty({
				message: data.status.message,
				type: (data.status.success == 1 ? 'success' : 'error')
			});
		}
	}); // $.ajax()

	return true;

}

/**
 * EDIT MODE - Encrypt the selection.  Add the <encrypt> tag
 *
 * @returns {boolean}
 */
function buttonEncrypt(editor) {

	var cm = editor.codemirror;
	var output = '';
	var selectedText = cm.getSelection();
	var text = selectedText || 'your_confidential_info';

	output = '<encrypt>' + text + '</encrypt>';
	cm.replaceSelection(output);

}

/**
 * ADD TOC - Add the %TOC_3% tag
 *
 * @returns {boolean}
 */
function buttonAddTOC(editor) {

	var cm = editor.codemirror;
	// Just add the tag where the cursor is located
	cm.replaceSelection('%TOC_3%');
}
