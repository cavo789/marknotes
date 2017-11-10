/**
 * Triggered when the use click on the Clear cache button
 */
function fnPluginTaskOptimizeClearCache() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page html - Optimize - Clear cache');
	}
	/*<!-- endbuild -->*/
	try {
		ajaxify({
			task: 'task.optimize.clear',
			callback: 'fnPluginTaskOptimizeClearCacheDoIt();',
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
function fnPluginTaskOptimizeClearCacheDoIt() {

	try {
		store.clearAll();
	} catch (err) {
		console.warn(err.message);
	}

	location.reload();

	Noty({
		message: $.i18n('settings_clean_done'),
		type: 'success'
	});

	return;

}

/**
 * The user has displayed a note; from the cache and has click on
 * "Reload the note" to get a newer copy
 */
function fnPluginTaskOptimizeReloadNote() {
	ajaxify({
		task: 'reload'
	});
	return;
}

/**
 * The name in the store is not f.i. "listfiles" but "docs_listfiles"
 * i.e. each variables is prefixed so we can make separate multiples
 * instances of marknotes on the same server
 */
function fnPluginTaskOptimize_GetName($params) {

	$sName = $params.name;

	if (typeof marknotes.store.prefix !== 'undefined') {
		$sName = marknotes.store.prefix + '_' + $sName;
	}

	return $sName;

}

/**
 * Set a value into the localStorage
 */
function fnPluginTaskOptimizeStore_Set($params) {

	var $return = false;

	if (typeof store !== 'object') {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.error("    store.js not loaded");
		}
		/*<!-- endbuild -->*/
		// store.js not loaded
		return $return;
	}

	// The name parameter should be mentionned
	if ((typeof $params.name === 'undefined') || (typeof $params.data === 'undefined')) {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.error("    fnPluginTaskOptimizeStore_Set should be " +
				"called with a name and a data");
		}
		/*<!-- endbuild -->*/
		return $return;
	}

	if ($params.name === '') {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.warn("    fnPluginTaskOptimizeStore_Set - Name is empty");
		}
		/*<!-- endbuild -->*/
		return $return;
	}

	try {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log("   Store [" + $params.name + "] in localStorage ");
			console.log($params);
		}
		/*<!-- endbuild -->*/
		$name = fnPluginTaskOptimize_GetName($params);
		$return = store.set($name, $params.data);
	} catch (err) {
		console.warn(err.message);
	}

	return $return;
}

/**
 * Get a value from the localStorage or return null if not found
 */
function fnPluginTaskOptimizeStore_Get($params) {

	var $return = null;

	if (typeof store !== 'object') {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.error("    store.js not loaded");
		}
		/*<!-- endbuild -->*/
		// store.js not loaded
		return null;
	}

	// The name parameter should be mentionned
	if (typeof $params.name === 'undefined') {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.error("    fnPluginTaskOptimizeStore_Get should be " +
				"called with a parameter name");
		}
		/*<!-- endbuild -->*/
		return null;
	}

	// The name parameter is empty
	if (typeof $params.name == '') {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.error("    fnPluginTaskOptimizeStore_Get - The name parameter is empty");
		}
		/*<!-- endbuild -->*/
		return null;
	}

	/*<!-- build:debug -->*/
	//if (marknotes.settings.debug) {
	//	console.log("   Try to get value for [" + $params.name + "]");
	//	console.log("   Below, the list of keys already stored in store.js");
	//	store.each(function (value, key) {
	//		console.log("         [" + key + "]");
	//	})
	//}
	/*<!-- endbuild -->*/

	// useStore should only be processed for specifics tasks
	// like listFiles, display, ... but not all tasks
	//var $arrUseStore = ['task.export.html', 'getFile', 'listFiles'];

	//if (jQuery.inArray($params.name, $arrUseStore) !== -1) {

	// Do we've such variable in our localStorage ?
	/*<!-- build:debug -->*/
	//if (marknotes.settings.debug) {
	//	console.log($params);
	//}
	/*<!-- endbuild -->*/
	$name = fnPluginTaskOptimize_GetName($params);

	if ($name in localStorage) {
		// Yes
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('   Found in the localStorage; return that value');
		}
		/*<!-- endbuild -->*/

		try {
			$return = store.get($name);
		} catch (err) {
			console.warn(err.message);
		}

	} else {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log("   Not found in localStorage");
		}
		/*<!-- endbuild -->*/
	}

	//} else {

	//	console.log("Don't use the store for " + $params.name);

	//}

	return $return;

}

/**
 * Remove a value from the localStorage
 */
function fnPluginTaskOptimizeStore_Remove($params) {

	var $return = null;

	if (typeof store !== 'object') {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.error("    store.js not loaded");
		}
		/*<!-- endbuild -->*/
		// store.js not loaded
		return null;
	}

	// The name parameter should be mentionned
	if (typeof $params.name === 'undefined') {

		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.error("    fnPluginTaskOptimizeStore_Remove should be " +
				"called with a parameter name");
		}
		/*<!-- endbuild -->*/
		return null;
	}

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log("   Remove [" + $params.name + "] from the localStorage");

		$name = fnPluginTaskOptimize_GetName($params);
		store.remove($name);
	}
	/*<!-- endbuild -->*/

	return true;

}
