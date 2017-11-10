<?php
/**
 * Optimize JS
 * @link https://github.com/matthiasmullie/minify/
 */

namespace MarkNotes\Plugins\Page\HTML\Optimize;

defined('_MARKNOTES') or die('No direct access allowed');

class JS
{

	private static function addDefer(string $script, array $arrOptimize) : string
	{

		$sReturn=$script;

		$jsTag=substr($script, 0, strpos($script, '>')+1);

		if (stripos($jsTag, ' defer')===false) {
			$except=$arrOptimize['defer_exceptions']??array();
			$except=implode($except, ';');

			$bDoIt=1;
			if (trim($except)!=='') {
				$bDoIt=(preg_match('/'.str_replace(array(',',';'), '|', $except).'/i', $jsTag)<1);
			}

			if ($bDoIt) {
				$script=str_replace($jsTag, rtrim($jsTag, '>').' defer="defer">', $script);
				$sReturn=str_replace($jsTag, rtrim($jsTag, '>').' defer="defer">', $script);
			}
		}

		return $sReturn;
	}

	public static function run(string $str, array $arrOptimize) : string
	{

		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();

		// In case of, remove empty tag
		$str=str_replace('<script type="text/javascript"></script>', '', $str);
		$str=str_replace('<script type="text/javascript" defer="defer"></script>', '', $str);

		// Base url (f.i. http://localhost:8080/notes/)
		$baseURL=$aeFunctions->getCurrentURL();

		// Retrieve all <script> definition in the HTML content
		$arrMatches=null;

		preg_match_all("/<script[^>]*?>([\s\S]*?)<\/script>/", $str, $arrMatches);

		if (count($arrMatches[0])>0) {
			$j=count($arrMatches[0]);

			$lib=$aeSettings->getFolderLibs().'minify/src/';

			//@link https://github.com/matthiasmullie/minify
			//include_once($lib.'Exception.php');
			//include_once($lib.'Exceptions/BasicException.php');
			//include_once($lib.'Exceptions/IOException.php');
			include_once($lib.'Minify.php');
			include_once($lib.'JS.php');

			for ($k=0; $k<$j; $k++) {
				if (($inline=trim($arrMatches[1][$k]))!=='') {
					if ($arrOptimize['minify']??0) {
						/*<!-- build:debug -->*/
						if ($aeSettings->getDebugMode()) {
							$aeDebug = \MarkNotes\Debug::getInstance();
							$aeDebug->log('JS - Minify', 'debug');
						}
						/*<!-- endbuild -->*/

						// Inline script = minify the script
						$minifier = new \MatthiasMullie\Minify\JS();
						$minifier->add($inline);
						$js=$minifier->minify();
						unset($minifier);

						// and replace the original, unminified, by the optimized version
						$str=str_replace($arrMatches[1][$k], $js, $str);
					} // if ($arrOptimize['minify']??0)
				} else {
					// It's a file inclusion like :
					// <script type="text/javascript"
					// src="http://localhost:8080/notes/libs/jquery/jquery.min.js"></script>

					$script=$arrMatches[0][$k];

					// Retrieve the file (http://localhost:8080/notes/libs/jquery/jquery.min.js)
					if (preg_match("/.*src=\"(.*)\".*/", $script, $matches)) {
						// Check if we need to add the Defer attribute
						if ($arrOptimize['add_defer']??0) {
							/*<!-- build:debug -->*/
							if ($aeSettings->getDebugMode()) {
								$aeDebug = \MarkNotes\Debug::getInstance();
								$aeDebug->log('JS - Add defer', 'debug');
							}
							/*<!-- endbuild -->*/

							$js= self::AddDefer($script, $arrOptimize);

							// and replace the original, unminified,
							// by the optimized version

							$str=str_replace($script, $js, $str);
						}
					} // if(preg_match
				} // if (($inline=trim($arrMatches[1][$k]))!=='')
			} // for($k=0; $k<$j; $k++)
		} // if (count($arrMatches[0])>0)

		/*
		$aeDebug = \MarkNotes\Debug::getInstance();
		if ($aeDebug->getDevMode()) {
			$aeDebug->here("After optimization",1);
			echo '<pre>'.str_replace('<','&lt;',$str).'</pre>';
			die("Died in ".__FILE__.", line ".__LINE__);
		}*/

		return $str;
	}
}
