/**
 * $params is a JSON object initiliazed by the /assets/js/marknotes.js file.
 */
function fnPluginButtonEdit($params) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Editor');
	}
	/*<!-- endbuild -->*/

	if (marknotes.note.url == '') {
		Noty({
			message: $.i18n('error_select_first'),
			type: 'error'
		});

	} else {

		ajaxify({
			task: 'task.edit.form',
			param: marknotes.note.md5,
			callback: 'afterEdit($data, data)',
			useStore: false,
			target: 'CONTENT'
		});
	}

	return true;
}

/**
 * EDIT MODE - Render the textarea in an editor
 */
function afterEdit($ajax_request, $form) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Editor - afterEdit');
	}
	/*<!-- endbuild -->*/

	$('#CONTENT').html($form);

	if (document.getElementById("sourceMarkDown") !== null) {
		afterEditInitMDE($ajax_request);
	} else {
		Noty({
			message: $.i18n('not_authenticated'),
			type: 'error'
		});
	}

	return true;
}

function afterEditInitMDE($data) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Editor - afterEdit');
		console.log($data);
	}
	/*<!-- endbuild -->*/

	filename = $data.param;

	// Create the Simple Markdown Editor
	// @link https://github.com/NextStepWebs/simplemde-markdown-editor

	var simplemde = new SimpleMDE({
		autoDownloadFontAwesome: false,
		autofocus: true,
		autosave: {
			enabled: false
		},
		codeSyntaxHighlighting: true,
		element: document.getElementById("sourceMarkDown"),
		indentWithTabs: true,
		insertTexts: {
			horizontalRule: ["", "\n\n---\n\n"],
			image: ["![](https://", ")"],
			link: ["[", "](https://)"],
			table: ["", "\n\n| Column 1 | Column 2 | Column 3 |\n| --- | --- | --- |\n| Text | Text | Text |\n\n"],
		},
		spellChecker: true,
		status: ["autosave", "lines", "words", "cursor"], // Optional usage
		styleSelectedText: false,
		tabSize: 4,
		toolbar: [
			{
				// Add a custom button for saving
				name: "Save",
				action: function customFunction(editor) {
					buttonSave(filename, simplemde.value());
				},
				className: "fa fa-floppy-o",
				title: $.i18n('button_save')
			},
			{
				// Encrypt
				name: "Encrypt",
				action: function customFunction(editor) {
					buttonEncrypt(editor);
				},
				className: "fa fa-user-secret",
				title: $.i18n('button_encrypt')
			},
			{
				// Table of content
				name: "AddTOC",
				action: function customFunction(editor) {
					buttonAddTOC(editor);
				},
				className: "fa fa-map-o",
				title: $.i18n('button_addTOC')
			},
			"|",
			{
				// Add a custom button for saving
				name: "Exit",
				action: function customFunction(editor) {
					$('#sourceMarkDown').parent().hide();
					ajaxify({
						task: 'task.export.html',
						param: filename,
						callback: 'afterDisplay($data.param)',
						target: 'CONTENT'
					});
				},
				className: "fa fa-sign-out",
				title: $.i18n('button_exit_edit_mode')
			},
			"|", "preview", "side-by-side", "fullscreen", "|",
			"bold", "italic", "strikethrough", "|", "heading", "heading-smaller", "heading-bigger", "|", "heading-1", "heading-2", "heading-3", "|",
			"code", "quote", "unordered-list", "ordered-list", "clean-block", "|", "link", "image", "table", "horizontal-rule"
		] // toolbar
	});

	//	$('.editor-toolbar').addClass('fa-2x');

	return true;
}

/**
 * EDIT MODE - Save the new content.  Called by the "Save" button
 * of the simplemde editor, initialized in the afterEdit function)
 *
 * @param {type} $fname        Filename
 * @param {type} $markdown     The new content
 * @returns {boolean}
 */
function buttonSave($fname, $markdown) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Editor - Save');
	}
	/*<!-- endbuild -->*/

	// If LocalStorage is enabled, remove the old saved note since we've
	// just modify it.
	var $useStore = (typeof store === 'object');
	if ($useStore && (typeof fnPluginTaskOptimizeStore_Remove === 'function')) {
		fnPluginTaskOptimizeStore_Remove($fname);
	}

	var $data = {};
	$data.task = 'task.edit.save';
	$data.param = $fname;
	$data.markdown = window.btoa(encodeURIComponent(JSON.stringify($markdown)));

	$.ajax({
		async: true,
		// GET can't be used because note's content can be too big for URLs
		type: 'POST',
		url: marknotes.url,
		data: $data,
		datatype: 'json',
		success: function (data) {
			Noty({
				message: data.message,
				type: (data.status == 1 ? 'success' : 'error')
			});

			var $useStore = (typeof store === 'object');
			if ($useStore) {
				// Be sure the localStorage array is up-to-date and willn't
				// contains the previous content
				fnPluginTaskOptimizeStore_Remove({
					"name": $fname
				});
			}
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

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Editor - Encrypt');
	}
	/*<!-- endbuild -->*/

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

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Editor - Add TOC tag');
	}
	/*<!-- endbuild -->*/

	var cm = editor.codemirror;
	// Just add the tag where the cursor is located
	cm.replaceSelection('%TOC_5%');
}
