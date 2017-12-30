<?php
/**
 * After the code for the exportation, the export.after event will be fired and
 * this plugin will check if the output file was well created and if not, will
 * display debugging informations to help to understand why the file wasn't created
 *
 * Only active when debug=1 in settings.json
 */

namespace MarkNotes\Plugins\Task\Export\After;

defined('_MARKNOTES') or die('No direct access allowed');

require_once(dirname(dirname(__DIR__)).'/.plugin.php');

class Debug extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.export.after.debug';
	protected static $json_options = '';

	/*<!-- build:debug -->*/
	public static function run(&$params = null) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();

		$filename = $params['output'] ?? '';   // output filename (fullname)

		if (!$aeFiles->exists($filename)) {
			$format = $params['extension'];  // extension like 'txt'

			if ($filename=='') {
				// When $filename is empty, there was a problem when creating that
				// version of the file (f.i. the .txt version of the note).
				//
				// $params['filename'] contains the filename (relative) of the note
				$filename=$aeFiles->removeExtension($params['filename']).'.'.$format;
			}

			$aeConvert = \MarkNotes\Tasks\Convert::getInstance($params['filename'], $format, 'pandoc');

			$fScriptFile=$aeSettings->getFolderTmp().$aeConvert->getSlugName().'.bat';

			$msg = $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists');
			$msg = str_replace('%s', '<strong>'.$filename.'</strong>', $msg);

			$aeDebug->here('#DebugMode# - File '.$filename.' not found', 10);

			echo $msg.PHP_EOL.PHP_EOL;

			// Perhaps a problem in the settings of pandoc ? Display node's informations
			$arr=$aeSettings->getPlugins(JSON_OPTIONS_PANDOC);
			echo '<p>Perhaps a problem with the definition, in settings.json, of the '.
			   JSON_OPTIONS_PANDOC.' node, please check below if everything is correct';
			echo "<pre style='background-color:yellow;'>".print_r($arr, true)."</pre>";

			echo '<p>Check to start <strong>'.$fScriptFile.'</strong> manually; indeed, sometimes it doesn\'t work within PHP but well manually; with the user\'s OS credentials (PHP permissions problems). Then, just refresh this page.</p>';

			$debugFile=$aeSettings->getFolderTmp().$aeConvert->getDebugFileName();

			if ($aeFiles->exists($debugFile)) {
				$content = file_get_contents($debugFile);
				echo '<h3>Content of the debug file : '.$debugFile.'</h3>';
				echo "<pre style='background-color:yellow;'>".$content."</pre>";
			}
		}

		return true;
	}
	/*<!-- endbuild -->*/

	/**
	 * Verify if the plugin is well needed and thus have a reason
	 * to be fired
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = false;

		/*<!-- build:debug -->*/
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			// Only when debugmode is set
			$aeSettings = \MarkNotes\Settings::getInstance();
			$bCanRun = $aeSettings->getDebugMode();
		}
		/*<!-- endbuild -->*/

		return $bCanRun;
	}
}
