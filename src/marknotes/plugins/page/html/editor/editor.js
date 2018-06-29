marknotes.arrPluginsFct.push("fnPluginEditInit");

var editor;	// Remember the editor
var filename; // Remember the filename

// Variables used in order to be able to fixed the toolbar
// when scrolling then page.
var toolbarAffixAt = 0;
var toolbarFixedTop= 0;
var cmPaperTop = 0;

/**
 * @returns boolean
 */
function fnPluginEditInit(params) {
	return true;
}

/**
 * Easily disable a stylesheet.css file; for instance:
 *
 * 	toggleEditorCSS('editor.min.css')
 *
 * This will disable the mentionned css file and, on the next call,
 * enable it again (the way a toggle button works)
 *
 * Needed by the Preview feature to disable a CSS file of tui.editor
 * and allow CSS files of marknotes to format the note
 *
 * @param  {[type]} css_filename_part [description]
 * @return {[type]}			[description]
 */
var toggleStateEditorCSS = function(css_filename_part, state) {
	var stylesheets = document.styleSheets;
	var length = stylesheets.length;
	var i;

	try {
		for (i=0; i < length; i++){
			var ss = stylesheets[i];

			// If part of the href URL match the css_filename_part
			// parameter then update the state (true / false)
			// Don't use a toggle state so we're sure that if the
			// function is called twice, the disabled is well what
			// we wish
			if ((ss.href!==null) && (ss.href.indexOf(css_filename_part) !== -1)) {
				ss.disabled = state;
			}
		}
	} catch (e) {

	} finally {

	}

};

/**
 * Fix toolbar at set distance from top and adjust toolbar width
 * @link https://codepen.io/bleutzinn/pen/KmNWmp?editors=0010
 */
function fnPluginEditToolbarAffix() {
	if ($(document).scrollTop() > toolbarAffixAt) {
		$(".te-toolbar-section")
			.addClass("toolbar-fixed")
			.css({"top": toolbarFixedTop + "px"});

		$("#divEditUpload")
			.css({"top": toolbarFixedTop + "px"});

		fnPluginEditToolbarSetWidth();
	} else {
		$(".te-toolbar-section")
			.removeClass("toolbar-fixed")
			.css({top: ""});

		$("#divEditUpload")
			.css({top: ""});
	}

	return true;
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

		// Get the HTML and javascript for the editor
		ajaxify({
			task: 'task.edit.form',
			param: marknotes.note.md5,
			callback: 'fnPluginEditShowEditor($data, data)',
			useStore: false
		});
	}

	return true;
}

/**
 * EDIT MODE - Render the editor
 */
function fnPluginEditShowEditor($ajax_request, $form) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Editor - fnPluginEditAfterShowEditor');
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
						'fnPluginEditShowEditor - Inform connected '+
						'people about the fact that the connected '+
						'user is editing a note');
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

		  When the editor is displayed, here in fnPluginEditShowEditor, the
		  default task can become task.export.html otherwise,
		  each time the note is clicked in the treeview, the editor
		  will be displayed.

		  So, here below, the code will reset the task of the note
		  under edition
		*/
		if (typeof marknotes.note.id !== 'undefined') {
			$('#TOC').jstree(true).get_node(marknotes.note.id).data.task = 'task.export.html';
		}

		fnPluginEditAfterShowEditorInitialize($ajax_request);

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
function fnPluginEditAfterShowEditorInitialize($data) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  Plugin Page html - Editor - '+
			'fnPluginEditAfterShowEditorInitialize');
		console.log($data);
	}
	/*<!-- endbuild -->*/

	filename = $data.param;

	// Should be en_US and not en-US
	var editor_language = marknotes.settings.language_ISO;
	editor_language = editor_language.replace('-','_');

	/**
	 * Initialize the tui.editor
	 * @type {tui}
	 */
	var editor = new tui.Editor({
		"el": document.querySelector('#editorMarkDown'),
		"initialEditType": "markdown",
		"previewStyle": "tab",
		"minHeight": "300px",
		"height": "auto",
		// Hide mode switch tab bar (displayed in
		// the right bottom corner)
		"hideModeSwitch": true,
		// Use default htmlSanitizer
		"useDefaultHTMLSanitizer": false,
		// Otherwise tui.editor make use of Google Analytics for his own stats
		"usageStatistics": false,
		"language": editor_language,
		"events": {
			// It would be emitted when editor fully load
			"load": fnPluginEditLoaded,
			//It would be emitted when content changed
			//"change": fnPluginEditChange()
			//It would be emitted when format change by cursor position
			//"stateChange": function() {
			//	console.log('State has changed');
			//}
		}
	});

	/* Call the fnPluginEditAddButtonsToolbar() function
		Function created by marknotes/plugins/task/edit/form.php
		with the javascript code for each button to add in the
		toolbar
	*/
	var fn = window.fnPluginEditAddButtonsToolbar;
	if (typeof fn === "function") {
		// Custom functions will need a pointer to the
		// editor and the name of the file under editing
		fnPluginEditAddButtonsToolbar(editor, filename);
	}
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
						note_content: editor.getMarkdown()
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

	toolbarInitialTop = $(".te-toolbar-section").offset().top;
	toolbarOuterHeight = $(".te-toolbar-section").outerHeight();

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

	return true;
}

/**
 * #sourceMarkDown can contains HTML tags
 * <encrypt> but in this way : &lt;encrypt&gt;
 * This because tui.editor remove tags and keep
 * only the text.
 * So, here, restore tags.
 * @return {[type]} [description]
 */
function fnPluginEditSetContent(editor) {

	var $MD = $('#sourceMarkDown').text();
	$MD = $MD
		.replace(/&lt;/g,'<')
		.replace(/&gt;/g,'>')
		.replace(/&amp;/g,'&');

	editor.setValue($MD);

	// Reset the page to the top so the first line of the
	// note can be displayed
	$(document).scrollTop(0);

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

/*
 * tui.editor display a "Write" and a "Preview"
 * button at the left of the editor inside
 * a .te-markdown-tab-section div.
 * The Preview button has a data-index attribute
 * set to 1 so, here below, capture the click event
 *  on that button.
 * @param  {[type]} editor [description]
 * @return {[type]}		[description]
 */
function fnPluginEditOverridePreview(editor) {

	// ------------------------------------
	// 1. Write

	// Target the "Write" button of tui.editor
	var $selector = '.te-markdown-tab-section [data-index=0]';

	// Add click event
	$($selector).on('click', function(e) {

		// enable back the CSS of tui.editor for the contents part
		toggleStateEditorCSS('tui-editor-contents.min.css', false);

		fnPluginEditSetContent(editor);
		$('.tui-editor-defaultUI-toolbar').show();
		//fnPluginEditToolbarAffix();
	});

	// Add a font-awesome icon and remove the text ("Write")
	try {
		$($selector).addClass('fa fa-pencil-square-o').text('');
	} catch (e) {
	} finally {
	}

	// ------------------------------------
	// 2. Preview

	// Target the "Preview" button of tui.editor
	$selector = '.te-markdown-tab-section [data-index=1]';

	// Add click event
	$($selector).on('click', function(e, options) {
		options = options || {};

		if ( !options.getHTML ) {

			toggleStateEditorCSS('tui-editor-contents.min.css', true);

			// Keep a copy of the markdown before switching
			// to the HTML preview
			$('#sourceMarkDown').text(editor.getMarkdown());

			$('.tui-editor-defaultUI-toolbar').hide();
			// The getHTML option wasn't set : before
			// showing the Preview mode, first call the
			// task.edit.preview task to retrieve the HTML content
			// (!! rendered by marknotes !!). Once done, the
			// getHTML option will be set to True then the normal
			// JS code of tui.editor will be fired
			var $data = {};
			$data.task = 'task.edit.preview';
			$data.param = filename;
			$data.markdown = window.btoa(encodeURIComponent(JSON.stringify(editor.getMarkdown())));

			$.ajax({
				async: false,
				// GET can't be used because note's content can be
				// too big for URLs
				type: 'POST',
				url: marknotes.url,
				data: $data,
				datatype: 'json',
				success: function (data) {
					// Ok, we've retrieved the HTML generated by
					// marknotes, set that HTML to the html
					// By using the timeout "trick", the HTML content
					// won't be sanitized i.e. our HTML tags won't be
					// removed (as seen in the example on
					// https://nhnent.github.io/tui.editor/api/latest/tutorial-example12-writing-extension.html#
					// -see JS tab-)
					setTimeout(function(){
						$('.tui-editor-contents').html(data.html);
					}, 0);
					// And retrigger the onclick event with
					// getHTML=true so the normal behavior of tui.editor
					// can be fired and the Preview pane will be displayed
					$(e.currentTarget).trigger('click', {
						"getHTML": true
					});
				}
			}); // $.ajax()

		} else {
			// Allow default behavior of tui.editor to happen
		}

	});

	// Add a font-awesome icon and remove the text ("Preview")
	//  !!! STRANGE !!!
	//  When the text is empty (nothing at all), the preview
	//  button isn't working. But with a space or anything else;
	//  it's work
	try {
		$($selector).addClass('fa fa-eye').text(' ');
	} catch (e) {
	} finally {
	}

	return true;
}

/**
 * When the editor has been fully loaded
 */
function fnPluginEditLoaded(editor) {

	fnPluginEditSetContent(editor);

	fnPluginEditUseFontAwesome();

	fnPluginEditOverridePreview(editor);

	return true;
}

/**
 * When the content has been modified
 */
function fnPluginEditChange() {
}
