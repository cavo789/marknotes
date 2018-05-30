<div id="modal-box" class="modal-popup">

	<span>%SEARCHED_ADVANCED_FORM%</span>
	<hr/>
	<form method="post" action="#">

		<span>%SEARCH_DEFINE_TITLE%</span>
		<fieldset class="textbox">
			<select id="cbxFolderList">%FOLDERS%</select>
		</fieldset>

		<br/>
		<button class="submit" type="button">%SEARCH_APPLY%</button>
		<button id="btn_search_remove_restrict" class="submit" type="button">%SEARCH_REMOVE%</button>

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
	marknotes.search.restrict_folder = selectedFolders;

	// Reinitialize the treeview to search only on the selected folder
	var $param = window.btoa(encodeURIComponent(JSON.stringify(selectedFolders)));
	$('#TOC').jstree(true).settings.search.ajax.data = {restrict_folder : $param };

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  fnPluginTaskSearchRestrictFolder - restrict searchs to ');
		console.log('	  '+marknotes.search.restrict_folder);
	}
	/*<!-- endbuild -->*/

	if (marknotes.search.restrict_folder!=='.') {
		$('#search-folder-btn').addClass('btn-restrict-active');
		$('.search-flexdatalist').attr('placeholder', marknotes.search.restrict_folder);
		$('.search-flexdatalist').attr('title', $.i18n('search_restricted_to')+' '+marknotes.search.restrict_folder);
	} else {
		$('#search-folder-btn').removeClass('btn-restrict-active');
		$('.search-flexdatalist').attr('placeholder', $.i18n('search_placeholder'));
		$('.search-flexdatalist').attr('title', '');
	}
	return true;

});

$('#btn_search_remove_restrict').click(function() {

	// No restriction
	marknotes.search.restrict_folder = '.';
	$('#search-folder-btn').removeClass('btn-restrict-active');

	// Reinitialize the treeview to search only on the selected folder
	$('#TOC').jstree(true).settings.search.ajax.data = {restrict_folder : '.'};

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  fnPluginTaskSearchRestrictFolder - Remove restriction');
	}
	/*<!-- endbuild -->*/

	return true;

});

</script>
