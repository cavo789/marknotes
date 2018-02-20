<?php
/**
 * Use https://github.com/jolicode/JoliTypo for automatically solved some typography errors
 * @link https://github.com/jolicode/JoliTypo
 */

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

include 'libs/autoload.php';

class JoliTypo extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.image_gallery';
	protected static $json_options = 'plugins.options.content.html.image_gallery';

	public static function doIt(&$content = null) : bool
	{
		if (trim($content) === '') {
			return true;
		}

		$aeFolders = \MarkNotes\Folders::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if ($aeFolders->exists($aeSettings->getFolderLibs()."jolicode")) {
			$locale = $aeSettings->getLocale();

			// See https://github.com/jolicode/JoliTypo#fixer-recommendations-by-locale
			switch ($locale) {
				case 'fr-FR':
					// Those rules apply most of the recommendations
					// of "Abrégé du code typographique à l'usage de
					// la presse", ISBN: 9782351130667.
					// Remove Hypen because need a library
					// (Hyphenator) of 12MB,
					$fixer = new \JoliTypo\Fixer(array( 'Dimension', 'Numeric', 'Dash', 'SmartQuotes', 'FrenchNoBreakSpace', 'NoSpaceBeforeComma', 'CurlyQuote', 'Trademark'));
					break;

				default:

					// Remove Hypen because need a library
					// (Hyphenator) of 12MB,
					$fixer = new \JoliTypo\Fixer(array('Ellipsis', 'Dimension', 'Numeric', 'Dash', 'SmartQuotes', 'NoSpaceBeforeComma', 'CurlyQuote', 'Trademark'));
					break;
			}

			// Set the locale (en_GB, fr_FR, ...) preferences
			$fixer->setLocale($locale);

			$errorlevel = error_reporting();
			error_reporting(0);

			try {
				$content = $fixer->fix($content);
			} catch (Exception $e) {
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log($e->getMessage(), 'debug');
				}
				/*<!-- endbuild -->*/
			}

			error_reporting($errorlevel);
		}

		return true;
	}
}
