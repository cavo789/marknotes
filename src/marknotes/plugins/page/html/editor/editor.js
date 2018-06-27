marknotes.arrPluginsFct.push("fnPluginEditInit");

var editor;

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

	return true;
}

/**
 * Fix toolbar at set distance from top and adjust toolbar width
 * @link https://codepen.io/bleutzinn/pen/KmNWmp?editors=0010
 */
function fnPluginEditToolbarAffix() {

	if ($(document).scrollTop() > toolbarAffixAt) {
		$(".te-toolbar-section").addClass("toolbar-fixed");
		$(".te-toolbar-section").css({"top": toolbarFixedTop + "px"});
		fnPluginEditToolbarSetWidth();
	} else {
		$(".te-toolbar-section").removeClass("toolbar-fixed");
		$(".te-toolbar-section").css({top: ""});
	}
}

/**
 * Adjust fixed toolbar width to the width of CodeMirror
 * @link https://codepen.io/bleutzinn/pen/KmNWmp?editors=0010
 */
function fnPluginEditToolbarSetWidth() {
	$(".toolbar-fixed").width(
		$(".CodeMirror-code").width()
	);
}

/**
 * $params is a JSON object initiliazed by the /assets/js/marknotes.js file.
 */
function fnPluginButtonEdit($params) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Editor');
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
			useStore: false
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
		console.log('	  Plugin Page html - Editor - afterEdit');
	}
	/*<!-- endbuild -->*/

	// --------------------------------------------------------------
	// Check if the TogetherJS plugin is enabled
	// If yes, by typing something in the editor, broadcast
	// an information and tells who is editing which note.
	try {
		if (typeof TogetherJS === "function") {
			if (TogetherJS.running) {
				/*<!-- build:debug -->*/
				if (marknotes.settings.debug) {
					console.log('	  Plugin Page html - Editor - '+
						'afterEdit - Inform connected people about the fact '+
						'that the connected user is editing a note');
				}
				/*<!-- endbuild -->*/

				TogetherJS.send({
					type: "editor-started",
					name: TogetherJS.require("peers").Self.name,
					note_name: marknotes.note.file,
					note_id: marknotes.note.id
				});
			}
		}
	} catch (err) {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.warn(err.message + ' --- More info below ---');
			console.log(err);
		}
		/*<!-- endbuild -->*/
	}

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
			console.warn('		 DOM element id=sourceMarkDown is missing');
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
		console.log('	  Plugin Page html - Editor - afterEdit');
		console.log($data);
	}
	/*<!-- endbuild -->*/

	filename = $data.param;

	// Should be en_US and not en-US
	var editor_language = marknotes.settings.language_ISO;
	editor_language = editor_language.replace('-','_');

	var editor = new tui.Editor({
		"el": document.querySelector('#editorMarkDown'),
		//"initialValue": $('#sourceMarkDown').text(),
		"initialEditType": "markdown",
		"previewStyle": "tab",
		"minHeight": "300px",
		"height": "auto",
		// Hide mode switch tab bar
		//"hideModeSwitch": "true",
		// Use default htmlSanitizer
		"useDefaultHTMLSanitizer": false,
		// Otherwise tui.editor make use of Google Analytics for his own stats
		"usageStatistics": "false",
		"language": editor_language,
		"events": {
			// It would be emitted when editor fully load
			"load": fnPluginEditLoaded,
			//"load": fnPluginEditLoaded(),
			//It would be emitted when content changed
			//"change": fnPluginEditChange()
			//It would be emitted when format change by cursor position
			//"stateChange": function() {
			//	console.log('State has changed');
			//}
		}
	});

	// Call the fnPluginEditAddButtonsToolbar() function
	// Function created by marknotes/plugins/task/edit/form.php
	// with the javascript code for each button to add in the
	// toolbar
	var fn = window["fnPluginEditAddButtonsToolbar"];
	if (typeof fn === "function") {
		// Custom functions will need a pointer to the
		// editor and the name of the file under editing
		fnPluginEditAddButtonsToolbar(editor, filename);
	}
	// **************************************************

	/*
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
			// Remove "side-by-side" since it seems to not work
			// anymore in SimpleMDE; a JS error is generated
			"|", "preview",  "|", //"side-by-side", "fullscreen"
			"bold", "italic", "strikethrough", "|",
			"heading-1", "heading-2", "heading-3", "|",
			"code", "quote", "unordered-list", "ordered-list", "clean-block", "|", "link", "image", "table", "horizontal-rule"
		] // toolbar
	});
*/
	// --------------------------------------------------------------
	// Check if the TogetherJS plugin is enabled
	// If yes, by typing something in the editor, broadcast
	// an information and tells who is editing which note.
	/*if (typeof TogetherJS === "function") {
		simplemde.codemirror.on("change", function(){
			try {
				if (TogetherJS.running) {
					TogetherJS.send({
						type: "editor-change",
						name: TogetherJS.require("peers").Self.name,
						note_name: marknotes.note.file,
						note_id: marknotes.note.id,
						note_content: simplemde.value()
					});
				}
			} catch (err) {
			}
		});
	}*/

	// --------------------------------------------------------------
	//
	// Fixed the toolbar when scrolling
	// @https://codepen.io/bleutzinn/pen/KmNWmp?editors=0010

	toolbarInitialTop = $(".tui-editor-defaultUI-toolbar").offset().top;
	toolbarOuterHeight = $(".tui-editor-defaultUI-toolbar").outerHeight();

	toolbarFixedTop = 0;
	if ($(".main-header").length != 0) {
		toolbarFixedTop += $(".main-header").outerHeight();
	}
	if ($(".content-headerFixed").length != 0) {
		toolbarFixedTop += $(".content-headerFixed").outerHeight();
	}
	cmPaperTop = toolbarFixedTop + toolbarOuterHeight;

	toolbarAffixAt = toolbarInitialTop - toolbarFixedTop;

	$(document).scroll(fnPluginEditToolbarAffix);
	$(document).resize(fnPluginEditToolbarSetWidth);

	//
	// --------------------------------------------------------------

	return true;
}
/**
 *
 *  tui.editor is using css sprite as image for
 *  buttons; prefer to use Font-awesome so
 *  standard buttons of tui use the same font than
 *  our custom buttons (added by the editor plugins)
 * @return {[type]} [description]
 */
function fnPluginEditUseFontAwesome() {

	var arrClass = [
		['tui-heading', 'fa-header'],
		['tui-bold', 'fa-bold'],
		['tui-italic', 'fa-italic'],
		['tui-strike', 'fa-strikethrough'],
		['tui-hrline', 'fa-ellipsis-h'],
		['tui-quote', 'fa-quote-left'],
		['tui-ul', 'fa-list-ul'],
		['tui-ol', 'fa-list-ol'],
		['tui-task', 'fa-tasks'],
		['tui-indent', 'fa-indent'],
		['tui-outdent', 'fa-outdent'],
		['tui-table', 'fa-table'],
		['tui-image', 'fa-image'],
		['tui-link', 'fa-link'],
		['tui-code', 'fa-code'],
		['tui-codeblock', 'fa-angle-double-left']
	];

	// Remove the tui-xxxx class and add the fa-class
	arrClass.forEach(function(elem) {
		$('.tui-toolbar-icons.' + elem[0])
			.removeClass(elem[0])
			.addClass('MN_button fa ' + elem[1]);
	});

	return true;
}

/**
 * When the editor has been fully loaded
 */
function fnPluginEditLoaded(editor) {

	fnPluginEditUseFontAwesome();

	// #sourceMarkDown can contains HTML tags like
	// <encrypt> but in this way : &lt;encrypt&gt;
	// This because tui.editor remove tags and keep
	// only the text.
	// So, here, restore tags.
	var $MD = $('#sourceMarkDown').text();
	$MD = $MD
		.replace(/&lt;/g,'<')
		.replace(/&gt;/g,'>')
		.replace(/&amp;/g,'&');

	editor.setValue($MD);

	// Be sure the editor is displaying first lines
	try {
		// For Chrome, Firefox, IE and Opera
		document.documentElement.scrollTop = 0;
	} catch (e) {
		document.body.scrollTop = 0; // For Safari
	} finally {
	}

	return true;
}

/**
 * When the content has been modified
 */
function fnPluginEditChange() {
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
