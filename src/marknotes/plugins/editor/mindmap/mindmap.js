// Add a custom button that will insert a %INCLUDE note.md% tag
// $POSITION$ will be replaced by the position configured in
// settings.json->plugins->editor->include->position
toolbar.insertItem($POSITION$, {
	type: "button",
	options: {
		name: "MN_Edit_Mindmap",
		className: "MN_button fa fa-leaf tui-mindmap",
		event: "fnPluginEditButtonMindmapClicked",
		tooltip: $.i18n("button_addMindmap")
	}
});

$DIVIDER$;

editor.eventManager.addEventType("fnPluginEditButtonMindmapClicked");

editor.eventManager.listen("fnPluginEditButtonMindmapClicked", function() {
	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log("	  Plugin Editor - Mindmap");
	}
	/*<!-- endbuild -->*/

	// Insert the tag at the position of the cursor
	editor.insertText(
		"\n\n%MINDMAP_START%\n* Sandwiches\n\t * Egg - related\n\t\t * Egg and cress\n\t\t * Fried egg - chilli - chutney\n\t * Meaty\n\t\t * Chicken\n\t\t * Ham and cheese\n\t * Vegetarian\n\t\t * Salad?\n\t * Other misc.\n%MINDMAP_END%\n"
	);

	return true;
});
