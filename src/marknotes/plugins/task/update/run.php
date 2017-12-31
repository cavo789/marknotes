<?php
/**
 * Update marknotes
 *
 * Retrieve the install.php script from https://raw.githubusercontent.com/cavo789/marknotes_install/master/install.php
 *
 * The url is specified in settings.json in the
 * plugins->options->task->update->url entry
 */
namespace MarkNotes\Plugins\Task\Update;

defined('_MARKNOTES') or die('No direct access allowed');

class Update extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.update';
	protected static $json_options = 'plugins.options.task.update';

	public static function run(&$params = null) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();
		$root = $aeSettings->getFolderWebRoot();

		// Get the URL to the update script
		// i.e. https://raw.githubusercontent.com/cavo789/marknotes_install/master/install.php
		$url = trim(self::getOptions('url', ''));

		$bStatus = 0;

		if ($url !== '') {
			// Use GuzzleHttp
			$client = new \GuzzleHttp\Client(
				array('curl'=>array(CURLOPT_SSL_VERIFYPEER=>false))
			);

			$res = $client->request('GET', $url,
				['connect_timeout' => 10]);

			if ($res->getStatusCode() == 200) {
				// Get the script content; it's the PHP source of
				// marknotes_install
				$content = $res->getBody();

				// Get the root folder of the website
				$root = rtrim($aeSettings->getFolderWebRoot(), DS).DS;

				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("Create ".$root."install.php", "debug");
				}
				/*<!-- endbuild -->*/

				$aeFiles = \MarkNotes\Files::getInstance();
				$aeFiles->create($root.'install.php', $content);

				// True if the file has been created
				$bStatus = ($aeFiles->exists($root.'install.php'));

			} // if ($res->getStatusCode() == 200)
		} // if ($url !== '')

		header('Content-Type: application/json');
		echo json_encode(array('status' => ($bStatus?1:0)));

		return true;
	}
}
