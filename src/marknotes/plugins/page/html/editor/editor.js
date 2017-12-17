marknotes.arrPluginsFct.push("fnPluginEditInit");

/**
 * @returns boolean
 */
function fnPluginEditInit(params) {

	// the btn-exit-editor is added in the edit form by task.edit.form
	$(".btn-exit-editor").click(function (event) {
		fnPluginButtonEdit_Exit(null);
	});

	return true;
}


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
		/**
		  When a new note is created from marknotes (not on the
		  filesystem), the defaut task for this note is assigned
		  to "task.edit.form" in the listfile.json so, when the
		  user will click on the just created new note, the editor
		  will be displayed and not the HTML rendering (it's not
		  usefull since the note is empty).

		  When the editor is displayed, here in afterEdit, the
		  default task can become task.export.html otherwise,
		  each time the note is clicked in the treeview, the editor
		  will be displayed.

		  So, here below, the code will reset the task of the note
		  under edition
		*/
		if (typeof marknotes.note.id !== 'undefined') {
			$('#TOC').jstree(true).get_node(marknotes.note.id).data.task = 'task.export.html';
		}

		afterEditInitMDE($ajax_request);

		// Initialize events
		fnPluginEditInit();

	} else {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.warn('         DOM element id=sourceMarkDown is missing');
		}
		/*<!-- endbuild -->*/
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
			{
				// Retrieve the HTML of an article on the web
				name: "curlBlog",
				action: function customFunction(editor) {
					buttonCurlBlog(editor);
				},
				className: "fa fa-download",
				title: $.i18n('button_curlBlog')
			},
			{
				// Convert the content (HTML) to markdown
				name: "convertMD",
				action: function customFunction(editor) {
					button_convertMD(editor);
				},
				className: "fa fa-download",
				title: $.i18n('button_convertMD')
			},
			{
				// Translate the content
				name: "translate",
				action: function customFunction(editor) {
					button_translate(editor);
				},
				className: "fa fa-download",
				title: $.i18n('button_translate')
			},
			//"|",
			//{
			//	// Add a custom button for saving
			//	name: "Exit",
			//	action: function customFunction(editor) {
			//		fnPluginButtonEdit_Exit();
			//	},
			//	className: "fa fa-sign-out",
			//	title: $.i18n('button_exit_edit_mode')
			//},
			"|", "preview", "side-by-side", "fullscreen", "|",
			"bold", "italic", "strikethrough", "|", "heading", "heading-smaller", "heading-bigger", "|", "heading-1", "heading-2", "heading-3", "|",
			"code", "quote", "unordered-list", "ordered-list", "clean-block", "|", "link", "image", "table", "horizontal-rule"
		] // toolbar
	});

	//	$('.editor-toolbar').addClass('fa-2x');

	return true;
}

function fnPluginButtonEdit_Exit($params) {
	$('#sourceMarkDown').parent().hide();
	ajaxify({
		task: 'task.export.html',
		param: filename,
		callback: 'afterDisplay($data.param)',
		target: 'CONTENT'
	});
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

/**
 * Call the "task.fetch.gethtml" task and specify an URL
 * A cURL action will be fired and try to retrieve the HTML content
 * of that page
 */
function buttonCurlBlog(editor) {

	var $default = 'https://www.joomla.org/announcements/general-news/5721-joomla-response-to-overturning-net-neutrality-in-the-united-states.html';

	var $url = prompt("Which URL please ?", $default);

	if ($url != null) {
		var $data = {};
		$data.task = 'task.fetch.gethtml';
		$data.param = $url;

		$.ajax({
			async: true,
			type: 'POST',
			url: marknotes.url,
			data: $data,
			datatype: 'html',
			success: function (data) {
				editor.codemirror.setValue(data);
			}
		}); // $.ajax()
	}

}

/**
 * Call the task "task.convert.fromHTML" so the content of the
 * editor can be converted (best try) to a markdown string
 */
function button_convertMD(editor) {

	var $data = {};
	$data.task = 'task.convert.fromHTML';
	$data.param = editor.codemirror.getValue();

	$.ajax({
		async: true,
		type: 'POST',
		url: marknotes.url,
		data: $data,
		datatype: 'html',
		success: function (data) {
			editor.codemirror.setValue(data);
		}
	}); // $.ajax()

}

function button_translate(editor) {

	var $data = {};
	$data.task = 'task.translate.run';
	$data.param = editor.codemirror.getValue();

	$.ajax({
		async: true,
		type: 'POST',
		url: marknotes.url,
		data: $data,
		datatype: 'html',
		success: function (data) {
			editor.codemirror.setValue(data);
		}
	}); // $.ajax()

}
