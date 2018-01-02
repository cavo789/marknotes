marknotes.arrPluginsFct.push("fnPluginHTMLUpdate");

function fnPluginHTMLUpdate() {
	// This .js script is only loaded when the user is well
	// connected but, just for security, add an extra check
	if (marknotes.settings.authenticated!==1) {
		// Not connected ? Do nothing
		return false;
	}

	// The check will be done only once a day and certainly
	// not each time the page is displayed
	try {
		$name = 'marknotes_check_version';
		if (!Cookies.get($name)) {
			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log('Check newer version of MarkNotes');
			}
			/*<!-- endbuild -->*/
			var date = new Date();

			// One day
			date.setTime(date.getTime() + 24 * 60 * 60 * 1000);

			// Create the cookie, expiration : tomorrow.
			// From now till tomorrow, this code won't be executed
			// anymore
			Cookies.set($name, true, { expires: date });

			// Make the control
			fnPluginTaskUpdateGetVersion();
		}
	} catch (e) {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.warn(e.message);
		}
		/*<!-- endbuild -->*/
	}

	return true;
}

/**
 * Make an ajax call to $version_url and retrieve the package
 * json file of marknotes, in order to retrieve the latest
 * version.
 */
function fnPluginTaskUpdateGetVersion() {

	// Default URL; the definitive URL should be mentionned in the
	// settings.json file, in the plugins.page.html.update node.
	$version_url = 'https://raw.githubusercontent.com/cavo789/'
		+'marknotes/master/package.json';

	try {
		$version_url = marknotes.settings.version_url;
	} catch (e) {
		/*<!-- build:debug -->*/
		if (marknotes.settings.debug) {
			console.log('Variable marknotes.settings.version_url '+
				'not defined; impossible to check the latests '+
				'version of marknotes');
		}
		/*<!-- endbuild -->*/
	} // try

	if ($version_url!=='') {
		// Get the file and check if the current installed version
		// is older than the latest publicly available
		try {
			$.ajax({
				async: true,
				cache: true,
				type: 'GET', // Always GET; never POST
				url: $version_url,
				datatype: 'json',
				beforeSend: function () {
					// The button can be hidden when there is no
					// update to install. So we can spare a few space
					// on the screen
					$('#icon_update').hide();
				},
				success: function (data) {
					try {
						// data is a JSON string with, at the root level,
						// a version entry that will mention the latest,
						// publicly available, version of marknotes
						var json = $.parseJSON(data);

						if (json.hasOwnProperty('version')) {
							// json.version is the latest version; from
							// the github repository (f.i. "3.0")
							// marknotes.settings.version is the current,
							// installed, version (f.i. "2.0").
							//
							// $bNeedUpdate will be set to True when the
							// json.version (latest) if greater and thus,
							// when an update is available.
							$bNeedUpdate = (fnPluginTaskUpdateVersionCompare(json.version, marknotes.settings.version) == 1);

							if ($bNeedUpdate) {
								$('#icon_update').css('color','yellow').show();
								document.getElementById('icon_update').title = $.i18n('update_newer');
							}
						} // if (json.hasOwnProperty('version'))
					} catch (e) {
						/*<!-- build:debug -->*/
						if (marknotes.settings.debug) {
							console.log('Error when parsing the result ' +
								'returned by ' + $version_url);
							console.warn(e.message);
							console.log(data);
						}
						/*<!-- endbuild -->*/
					} // try
				}, // success
				error: function (Request, textStatus, errorThrown) {
					// Do nothing; this feature is not really important.
					//ajaxify_show_error($target, Request, textStatus, errorThrown);
				} // error
			}); // $.ajax()
		} catch (e) {
			/*<!-- build:debug -->*/
			if (marknotes.settings.debug) {
				console.log(e.message);
			}
			/*<!-- endbuild -->*/
		} // try
	}

	return true;
}

/**
 * Called when the user click on the update button
 */
function fnPluginTaskUpdate() {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('      Plugin Page Update');
	}
	/*<!-- endbuild -->*/

	if (marknotes.settings.authenticated===1) {
		// Redirect to the install script
		Noty({
			message: $.i18n('update_redirect'),
			type: 'info'
		});

		try {
			ajaxify({
				task: 'task.update.run',
				callback: 'fnPluginTaskUpdateDoIt($data, data);'
			});
		} catch (err) {
			console.warn(err.message);
		}
	} else {
		// Update can be done only when the user is connected.
		Noty({
			message: $.i18n('not_authenticated'),
			type: 'warning'
		});

	}

	return true;
}

/**
 * $data is the parameters sent with the ajax request
 * data is the answer of the task.update.run call
 */
function fnPluginTaskUpdateDoIt($data, data) {

	/*<!-- build:debug -->*/
	if (marknotes.settings.debug) {
		console.log('In fnPluginTaskUpdateDoIt function');
	}
	/*<!-- endbuild -->*/

	if (data.hasOwnProperty('status')) {
		if (parseInt(data.status) === 1) {
			location.href = 'install.php';
		}

	}


	return true;
}

/**
 * Compares two software version numbers (e.g. "1.7.1" or "1.2b").
 *
 * This function was born in http://stackoverflow.com/a/6832721.
 *
 * @param {string} v1 The first version to be compared.
 * @param {string} v2 The second version to be compared.
 * @param {object} [options] Optional flags that affect comparison behavior:
 * <ul>
 *     <li>
 *         <tt>lexicographical: true</tt> compares each part of the version strings lexicographically instead of
 *         naturally; this allows suffixes such as "b" or "dev" but will cause "1.10" to be considered smaller than
 *         "1.2".
 *     </li>
 *     <li>
 *         <tt>zeroExtend: true</tt> changes the result if one version string has less parts than the other. In
 *         this case the shorter string will be padded with "zero" parts instead of being considered smaller.
 *     </li>
 * </ul>
 * @returns {number|NaN}
 * <ul>
 *    <li>0 if the versions are equal</li>
 *    <li>a negative integer iff v1 < v2</li>
 *    <li>a positive integer iff v1 > v2</li>
 *    <li>NaN if either version string is in the wrong format</li>
 * </ul>
 *
 * @copyright by Jon Papaioannou (["john", "papaioannou"].join(".") + "@gmail.com")
 * @license This function is in the public domain. Do what you want with it, no strings attached.
 */
function fnPluginTaskUpdateVersionCompare(v1, v2, options) {
	var lexicographical = options && options.lexicographical,
		zeroExtend = options && options.zeroExtend,
		v1parts = v1.split('.'),
		v2parts = v2.split('.');

	function isValidPart(x) {
		return (lexicographical ? /^\d+[A-Za-z]*$/ : /^\d+$/).test(x);
	}

	if (!v1parts.every(isValidPart) || !v2parts.every(isValidPart)) {
		return NaN;
	}

	if (zeroExtend) {
		while (v1parts.length < v2parts.length) v1parts.push("0");
		while (v2parts.length < v1parts.length) v2parts.push("0");
	}

	if (!lexicographical) {
		v1parts = v1parts.map(Number);
		v2parts = v2parts.map(Number);
	}

	for (var i = 0; i < v1parts.length; ++i) {
		if (v2parts.length == i) {
			return 1;
		}

		if (v1parts[i] == v2parts[i]) {
			continue;
		} else if (v1parts[i] > v2parts[i]) {
			return 1;
		} else {
			return -1;
		}
	} // for

	if (v1parts.length != v2parts.length) {
		return -1;
	}

	return 0;
}
