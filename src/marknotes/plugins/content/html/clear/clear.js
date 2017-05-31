/**
 * Triggered when the use click on the Clear cache button
 */
function fnPluginTaskClearCache() {

	try {
		ajaxify({
			task: 'clear',
			callback: 'fnPluginTaskClearCacheDoIt();',
			target: 'CONTENT'
		});
	} catch (err) {
		console.warn(err.message);
	}

	return true;

}

/**
 * Empty the localStorage cache and the session on the server; reload then the page
 */
function fnPluginTaskClearCacheDoIt() {

	// Empty the localStorage too
	if (marknotes.settings.use_localcache) {
		try {
			store.clearAll();
		} catch (err) {
			console.warn(err.message);
		}
	}

	location.reload();

	Noty({
		message: marknotes.message.settings_clean_done,
		type: 'success'
	});

	return;

}
