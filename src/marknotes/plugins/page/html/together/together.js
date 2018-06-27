// Initialization of TogetherJS
//
// See https://togetherjs.com/docs/#configuring-togetherjs,
// for explanations of configuration options

// The language to present the tool in, such as "en-US" or "ru-RU"
// Note this must be set as TogetherJSConfig_lang, as it effects the loader
// and must be set as soon as this file is included
var TogetherJSConfig_lang = marknotes.settings.language;

// Used in some help text.
var TogetherJSConfig_siteName = marknotes.settings.sitename;

// This is the name that you are giving this tool.
// If you use this then "TogetherJS" won't be in the UI
var TogetherJSConfig_toolName = "marknotes collaboration";

// Name of the room that will appears f.i. in the
// "invitation" link
var TogetherJSConfig_findRoom = "marknotes";

// When a person is invited to a session,
// they'll be asked if they want to join in browsing with
// the other person. Set this to true and they won't be
// asked to confirm joining. The "Join TogetherJS Session?"
// confirmation dialog won't be displayed then.
var TogetherJSConfig_suppressJoinConfirmation = true;

// This is used to keep sessions from crossing over on the same
// domain, if for some reason you want sessions that are limited
// to only a portion of the domain:
//var TogetherJSConfig_storagePrefix: "MN_";

// Don't show the "Start TogetherJS" button,
// when someone click on the "invitation" link :
// immediatly start the collaboration session
var TogetherJSConfig_enableShortcut = true;

// When true, we treat the entire URL, including the hash, as the identifier
// of the page; i.e., if you one person is on `http://example.com/#view1`
// and another person is at `http://example.com/#view2` then these two people
// are considered to be at completely different URLs
var TogetherJSConfig_includeHashInUrl = true;

// Initialize the username for TogetherJS by using the
// username used to establish a connection to the site
// (within the .htpasswd protection or in the login
// form f.i.)
// https://togetherjs.com/docs/#setting-identity-information
var TogetherJSConfig_getUserName = function () {
	return marknotes.settings.username;
};

// The editor-started event is fired by the editor.js script
// (plugins/page/html/editor/editor.js) to inform other people
// that someone has just started the editor.
//
// data is a JSON object with the name of the editor, the note
// name and id
TogetherJS.hub.on("editor-started", function (data) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('TogetherJS event [editor-started] received');
	}
	/*<!-- endbuild -->*/

	// Display a notification message
	var $avatar = '<div class="towtruck-person">' +
		'<img style="float:left;padding:10px;" '+
		'src="'+data.peer.avatar+'" /></div>';

	Noty({
		message: $.i18n(
			'together_editor_started',
			$avatar,
			data.name,
			data.note_name,
			data.note_name,
			data.note_id),
		type: 'notification',
		timeout: 24000
	});

});

/**
 * Activate a note and open the editor
 * @param  {[type]} $note_name f.i. folder/a_note (without the .md extension)
 * @param  {[type]} $note_id	the md5 of the note
 * @return {[type]}			[description]
 */
function fnPluginTogetherShow($note_name, $note_id) {

	$('#TOC').jstree('activate_node', $note_id);

	// Be sure that these variables are set
	marknotes.note.file = $note_name;
	marknotes.note.id = $note_id;

	// Get the active note; get the object
	node = $('#TOC').jstree('get_selected',true);

	// And edit it
	fnPluginTaskTreeView_editNode(node);

	return;
}

// The editor-change event is fired by the editor.js script
// (plugins/page/html/editor/editor.js) when the content of the
// note has been updated. This function will allow to keep the
// note synchronized on both interfaces
//
// data is a JSON object with the name of the editor, the note
// name, id and newest content
TogetherJS.hub.on("editor-change", function (data) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('TogetherJS event [editor-change] received');
	}
	/*<!-- endbuild -->*/

	var textArea = document.getElementById('sourceMarkDown');
	var editor = CodeMirror.fromTextArea(textArea);
	editor.getDoc().setValue(data.note_content);

});
