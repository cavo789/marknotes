<div id="modal-box" class="modal-popup">

	<form method="post" action="#">

		<fieldset class="textbox">
			<select id="cbxFolderList">%FOLDERS%</select>
		</fieldset>

		<button class="submit button" type="button">%SEARCH_DEFINE%</button>
		<button id="btn_search_remove_restrict" class="submit button" type="button">%SEARCH_REMOVE%</button>

	</form>

</div>

<script>
$("#cbxFolderList").change(function() {

	// The user has selected one or more folders from the listbox
	// Get them

	var selectedFolders = "";

	$("select option:selected").each(function() {
 		selectedFolders += $(this).text() + ";";
	});

	selectedFolders = selectedFolders.substring(0, selectedFolders.length - 1);

	// Remove the final ";"
	marknotes.settings.restrict_folder = selectedFolders;

	if (marknotes.settings.restrict_folder !== marknotes.store.prefix) {
		$('#search-folder-btn').addClass('btn-restrict-active');
	} else {
		$('#search-folder-btn').removeClass('btn-restrict-active');
	}

	// Reinitialize the treeview to search only on the selected folder
	var $param = window.btoa(encodeURIComponent(JSON.stringify(selectedFolders)));
	$('#TOC').jstree(true).settings.search.ajax.data = {restrict_folder : $param };

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  fnPluginTaskSearchRestrictFolder - restrict searchs to ');
		console.log('	 '+marknotes.settings.restrict_folder);
	}
	/*<!-- endbuild -->*/

	return true;

});

$('#btn_search_remove_restrict').click(function() {

	// No restriction
	marknotes.settings.restrict_folder = '';
	$('#search-folder-btn').removeClass('btn-restrict-active');

	// Reinitialize the treeview to search only on the selected folder
	$('#TOC').jstree(true).settings.search.ajax.data = {restrict_folder : '' };

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  fnPluginTaskSearchRestrictFolder - Remove restriction');
	}
	/*<!-- endbuild -->*/

	return true;

});

</script>
