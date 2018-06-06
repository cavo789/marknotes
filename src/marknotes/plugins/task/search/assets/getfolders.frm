<div id="modal-box" class="modal-popup">

	<span>%SEARCHED_ADVANCED_FORM%</span>
	<hr/>
	<form method="post" action="#">

		<span>%SEARCH_DEFINE_TITLE%</span>
		<fieldset class="textbox">
			<select id="cbxFolderList">%FOLDERS%</select>
		</fieldset>

		<br/>

		<div class="form-check">
		  <input class="form-check-input" type="checkbox" value="" id="chkDisablePlugins">
		  <span class="form-check-label" for="chkDisablePlugins">%SEARCH_DISABLE_PLUGINS%</span>
		</div>

		<br/>

		<button id="btn_search_apply" class="submit" type="button">%SEARCH_APPLY%</button>
		<button id="btn_search_remove_restrict" class="submit" type="button">%SEARCH_REMOVE%</button>

	</form>

</div>

<script>
$("#btn_search_apply").click(function() {
//$("#cbxFolderList").change(function() {

	// Get the selected folder
	marknotes.search.restrict_folder = $("#cbxFolderList").val();

	// Disable or not plugins ?
	marknotes.search.disable_plugins = $('#chkDisablePlugins').is(":checked") ? 1 : 0;

	// Reinitialize the treeview to search only on the selected folder
	$('#TOC').jstree(true).settings.search.ajax.data =
		{
			restrict_folder : window.btoa(encodeURIComponent(JSON.stringify(marknotes.search.restrict_folder))),
			disable_plugins : marknotes.search.disable_plugins
		 };

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  fnPluginTaskSearchRestrictFolder - restrict searchs to ');
		console.log('	  '+marknotes.search.restrict_folder);
	}
	/*<!-- endbuild -->*/

	if (marknotes.search.restrict_folder!=='.') {
		$('#search-advanced-btn').addClass('btn-restrict-active');
		$('.search-flexdatalist').attr('placeholder', marknotes.search.restrict_folder);
		$('.search-flexdatalist').attr('title', $.i18n('search_restricted_to')+' '+marknotes.search.restrict_folder);
	} else {
		$('#search-advanced-btn').removeClass('btn-restrict-active');
		$('.search-flexdatalist').attr('placeholder', $.i18n('search_placeholder'));
		$('.search-flexdatalist').attr('title', '');
	}
	return true;
});

$('#btn_search_remove_restrict').click(function() {

	// No restriction
	marknotes.search.restrict_folder = '.';
	$('#search-advanced-btn').removeClass('btn-restrict-active');

	// Reinitialize the treeview to search only on the selected folder
	$('#TOC').jstree(true).settings.search.ajax.data =
		{
			restrict_folder : '.',
			disable_plugins: 0
		};

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('	  fnPluginTaskSearchRestrictFolder - Remove restriction');
	}
	/*<!-- endbuild -->*/

	return true;

});

</script>
