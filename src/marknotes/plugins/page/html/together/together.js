// Initialization of TogetherJS
//
// See https://togetherjs.com/docs/#configuring-togetherjs,
// for explanations of configuration options

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
// asked to confirm joining.
var TogetherJSConfig_suppressJoinConfirmation = true;

// Don't show the "Start TogetherJS" button,
// when someone click on the "invitation" link :
// immediatly start the collaboration session
var TogetherJSConfig_enableShortcut = true;

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

	// Display a notification message
	Noty({
		message: $.i18n('together_editor_started', data.name, data.note_name),
		type: 'notification',
		timeout: 24000
	});

	// Activate the note
	$('#TOC').jstree('activate_node', data.note_id);

});
