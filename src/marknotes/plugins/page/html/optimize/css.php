<?php
/**
 * Optimize CSS
 * @link https://github.com/matthiasmullie/minify/
 */

 namespace MarkNotes\Plugins\Page\HTML\Optimize;

 defined('_MARKNOTES') or die('No direct access allowed');

class CSS
{

	public static function run(string $str, array $arrOptimize) : string
	{
		$aeFolders = \MarkNotes\Folders::getInstance();

		if ($arrOptimize['minify']??0) {
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log('CSS - Minify', 'debug');
			}
			/*<!-- endbuild -->*/

			$lib=$aeSettings->getFolderLibs().'minify/src/';

			if ($aeFolders->exists($lib)) {
				/*
				$aeDebug = \MarkNotes\Debug::getInstance();
				if ($aeDebug->getDevMode()) {
					$aeDebug->here("Before optimization",1);
					echo '<pre>'.str_replace('<','&lt;',$str).'</pre>';
				}*/

				$arrMatches=array();

				preg_match_all("/<style>(.*?)<\/style>/mis", $str, $arrMatches);

				if (count($arrMatches[0])>0) {
					$j=count($arrMatches[0]);

					//@link https://github.com/matthiasmullie/minify
					//@link https://github.com/matthiasmullie/path-converter

					//include_once($lib.'Exception.php');
					//include_once($lib.'Exceptions/BasicException.php');
					//include_once($lib.'Exceptions/IOException.php');
					include_once($lib.'Minify.php');
					include_once($lib.'ConverterInterface.php');
					include_once($lib.'Converter.php');
					include_once($lib.'CSS.php');

					for ($k=0; $k<$j; $k++) {
						if (($inline=trim($arrMatches[1][$k]))!=='') {
							// Inline style = minify the style
							$minifier = new \MatthiasMullie\Minify\CSS();
							$minifier->add($inline);

							$css=$minifier->minify();
							unset($minifier);

							// and replace the original, unminified, by the optimized version
							$str=str_replace($arrMatches[1][$k], $css, $str);
						} // if (($inline=trim($arrMatches[1][$k]))!=='')
					} // for
				} // if (count($arrMatches[0])>0)

				/*
				$aeDebug = \MarkNotes\Debug::getInstance();
				if ($aeDebug->getDevMode()) {
					$aeDebug->here("After optimization",1);
					echo '<pre>'.str_replace('<','&lt;',$str).'</pre>';
					die("Died in ".__FILE__.", line ".__LINE__);
				}*/
			} else {
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug->log('Library not found ['.$lib.']', 'error');
				}
				/*<!-- endbuild -->*/
			}
		} // if ($arrOptimize['minify']??0)

		return $str;
	}
}
