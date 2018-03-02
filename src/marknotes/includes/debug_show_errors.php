<?php
/**
 * Enable the error_reporting(E_ALL) mode when :
 *
 *   - There is a settings.json file present in the root folder
 *   - That file is configured for enabling debug mode i.e.
 *		contains at least :
 *
 *		{
 *			"debug": {
 *				"enabled": 1
 *			}
 *		}
 *
 * During the development of marknotes, this file will be
 * included at the very top of index.php and router.php so
 * errors are catched immediatly
 */

$root=trim(dirname($_SERVER['SCRIPT_FILENAME']), DIRECTORY_SEPARATOR);
$root=str_replace('/', DIRECTORY_SEPARATOR, $root).DIRECTORY_SEPARATOR;

if (is_file($fname = $root.'settings.json'))
{
	$json=json_decode(file_get_contents($fname),true);

	if (isset($json['debug']))
	{
		if(boolval($json['debug']['enabled']??0))
		{
			ini_set("display_errors", "1");
			ini_set("display_startup_errors", "1");
			ini_set("html_errors", "1");
			ini_set("docref_root", "http://www.php.net/");

			// Log php errors in the temporary folder
			$tmp=$root.'tmp'.DIRECTORY_SEPARATOR;
			ini_set('error_log', $tmp.'php_errors.log');

			ini_set(
				"error_prepend_string",
				"<div style='background-color:yellow;border:1px solid red;padding:5px;'>"
			);

			ini_set("error_append_string", "</div>");

			error_reporting(E_ALL);
		} // if(boolval($json['debug']['enabled']??0))
	} // if (isset($json['debug']))
}
