marknotes.arrPluginsFct.push("fnPluginEditInit");

// Variables used in order to be able to fixed the toolbar
// when scrolling then page.
var toolbarAffixAt = 0;
var toolbarFixedTop= 0;
var cmPaperTop = 0;

/**
 * @returns boolean
 */
function fnPluginEditInit(params) {

	// Initialize the spellchecker
	//if (marknotes.editor.spellChecker) {
	//	if (marknotes.settings.language==='fr') {
	//		$Spelling.DefaultDictionary='Francais';
	//	}
	//
	//	$Spelling.SpellCheckAsYouType('sourceMarkDown');
	//}

	// the btn-exit-editor is added in the edit form by task.edit.form
	//$(".btn-exit-editor").click(function (event) {
	//	fnPluginButtonEdit_Exit(null);
	//});

	// Hide the upload area, show back the editor
	$(".btn-exit-upload-droparea").click(function (event) {
		$('#divEditUpload').hide();
	});

	return true;
}

/**
 * Fix toolbar at set distance from top and adjust toolbar width
 * @link https://codepen.io/bleutzinn/pen/KmNWmp?editors=0010
 */
function fnAffix() {

	if ($(document).scrollTop() > toolbarAffixAt) {
		$(".editor-toolbar").addClass("toolbar-fixed");
		$(".editor-toolbar").css({top: toolbarFixedTop + "px"});
		$(".CodeMirror.cm-s-paper.CodeMirror-wrap").css({
			top: cmPaperTop + "px"
		});
		fnSetWidth();
	} else {
		$(".editor-toolbar").removeClass("toolbar-fixed");
		$(".editor-toolbar").css({top: ""});
		$(".CodeMirror.cm-s-paper.CodeMirror-wrap").css({top: ""});
	}
}

/**
 * Adjust fixed toolbar width to the width of CodeMirror
 * @link https://codepen.io/bleutzinn/pen/KmNWmp?editors=0010
 */
function fnSetWidth() {
	$(".toolbar-fixed").width(
		$(".CodeMirror.cm-s-paper.CodeMirror-wrap").width()
	);
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

/**
 * Initialize the editor
 */
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
		codeSyntaxHighlighting: false,
		element: document.getElementById("sourceMarkDown"),
		indentWithTabs: true,
		insertTexts: {
			horizontalRule: ["", "\n\n---\n\n"],
			image: ["![](https://", ")"],
			link: ["[", "](https://)"],
			table: ["", "\n\n| Column 1 | Column 2 | Column 3 |\n| --- | --- | --- |\n| Text | Text | Text |\n\n"],
		},
		// marknotes.editor.spellChecker is read from settings.json,
		// plugins.options.page.html.editor.spellchecker
		// Note : SimpleMDE only support english. Other languages seems
		// to be downloadable from https://github.com/titoBouzout/Dictionaries
		// but SimpleMDE don't support them at this time.
		spellChecker: marknotes.editor.spellChecker,
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
			//{
			//	// Spell check
			//	name: "SpellCheck",
			//	action: function customFunction(editor) {
			//		buttonSpellCheck(editor);
			//	},
			//	className: "fa fa-check",
			//	title: $.i18n('button_spellcheck')
			//},
			"|",
			{
				// Add a custom button for saving
				name: "Exit",
				action: function customFunction(editor) {
					fnPluginButtonEdit_Exit()
				},
				className: "fa fa-sign-out",
				title: $.i18n('button_exit_edit_mode')
			},
			"|",
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
				className: "fa fa-flash",
				title: $.i18n('button_convertMD')
			},
			{
				// Translate the content
				name: "translate",
				action: function customFunction(editor) {
					button_translate(editor);
				},
				className: "fa fa-book",
				title: $.i18n('button_translate')
			},
			"|",
			{
				name: "uploadImage",
				action: function customFunction(editor) {
					buttonUploadImage(editor);
				},
				className: "fa fa-picture-o",
				title: $.i18n('button_upload_image')
			},
			"|", "preview", "side-by-side", "|", //"fullscreen"
			"bold", "italic", "strikethrough", "|",
			"heading-1", "heading-2", "heading-3", "|",
			"code", "quote", "unordered-list", "ordered-list", "clean-block", "|", "link", "image", "table", "horizontal-rule"
		] // toolbar
	});

	// --------------------------------------------------------------
	//
	// Fixed the toolbar when scrolling
	// @https://codepen.io/bleutzinn/pen/KmNWmp?editors=0010

	toolbarInitialTop = $(".editor-toolbar").offset().top;
	toolbarOuterHeight = $(".editor-toolbar").outerHeight();

	toolbarFixedTop = 0;
	if ($(".main-header").length != 0) {
		toolbarFixedTop += $(".main-header").outerHeight();
	}
	if ($(".content-headerFixed").length != 0) {
		toolbarFixedTop += $(".content-headerFixed").outerHeight();
	}
	cmPaperTop = toolbarFixedTop + toolbarOuterHeight;

	toolbarAffixAt = toolbarInitialTop - toolbarFixedTop;

	$(document).scroll(fnAffix);
	$(document).resize(fnSetWidth);

	//
	// --------------------------------------------------------------

	return true;
}

/*
 * Exit, quit the editor and display the note as an html content
 */
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

function buttonUploadImage(editor) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Editor - Upload');
	}
	/*<!-- endbuild -->*/

	$('#divEditUpload').toggle();

	// And initialize DropZone
	var myDropzone = new Dropzone("#upload_droparea", {
		url: "index.php?task=task.upload.save"
	});

	var $imgFileName = '';

	// Get filenames and add them into the editor
	myDropzone.on("success", function (file) {

		// The upload is successfull, retrieve the size of the image
		var $data = {};
		$data.task = 'task.image.getsize';
		$data.file = file.name;
		$data.note = marknotes.note.file;

		$size = "";

		$.ajax({
			url: marknotes.url,
			data: $data,
			method: 'POST',
			success: function(data){
				if (data.hasOwnProperty("width")) {
					// Get the JSON answer with width and height
					$size = data['width']+"x"+data['height'];
					$size = " \"" + $size + "\"";
				}

				// Generate the tag
				$img = file.name;
				$imgFileName = "!["+$img+"](%URL%.images/"+$img+$size+")\n\n";

				var cm = editor.codemirror;
				// Just add the img tag where the cursor is located
				cm.replaceSelection($imgFileName);
			}
		});


	});

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
 * The suer has clicked on the spell check button
 */
//function buttonSpellCheck(editor) {
//	if (marknotes.editor.spellChecker) {
//		$Spelling.DefaultDictionary = "Francais";
//		$Spelling.SpellCheckInWindow('sourceMarkDown');
//	}
//	return true;
//}

/**
 * Call the "task.fetch.gethtml" task and specify an URL
 * A cURL action will be fired and try to retrieve the HTML content
 * of that page
 */
function buttonCurlBlog(editor) {

	var $default_url = '';

	var $url = prompt($.i18n('fetch_specify_url'), $default_url);

	if (($url != null) && ($url != $default_url)) {
		// Start the ajax request and fetch the HTML of that page.
		// The gethtml task will also clean the code and only
		// keep content's HTML and not footer, navigation, comments,
		// ...
		var $data = {};
		$data.task = 'task.fetch.gethtml';
		$data.url = $url;

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
	$data.content = editor.codemirror.getValue();

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

/**
 * Call the translate task and get the translated content
 */
function button_translate(editor) {

	var $data = {};
	$data.task = 'task.translate.run';
	$data.content = editor.codemirror.getValue();

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
