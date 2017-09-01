<?php

/**
 * Optimize the HTML rendering, remove comments, minify css, ...
 *
 * Portions of this code relys on https://github.com/matthiasmullie/minify/
 */

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Optimize
{

	/**
	 * Optimize HTML
	 */
	private static function optimizeHTML(string $str, array $arrSettings) : string {

		$aeSettings = \MarkNotes\Settings::getInstance();
		/*if ($aeSettings->getDevMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->here("Before optimization",1);
			echo '<pre>'.str_replace('<','&lt;',$str).'</pre>';
		}*/

		// Minify content
		if ($arrSettings['minify']??0) {

			// Replace runs of whitespace inside elements with single space
			// escaping pre, textarea, scripts and style elements elements to escape
			$e = 'pre|script|style|textarea';

			// Regex for escape elements
			$p  = "<pre\b[^>]*+>(?><?[^<]*+)*?</pre>";
			$sc = "<script\b[^>]*+>(?><?[^<]*+)*?</script>";
			$st = "<style\b[^>]*+>(?><?[^<]*+)*?</style>";
			$t  = "<textarea\b[^>]*+>(?><?[^<]*+)*?</textarea>";

			$str = preg_replace("#(?>[^<]*+(?:$p|$sc|$st|$t|<[^>]++>".
				"[^<]*+))*?(?:(?:<(?!$e)[^>]*+>)?(?>\s?[^\s<]*+)*?\K\s{2,}|\K$)#i", ' ', $str);

			// Remove additional ws around attributes
			$str = preg_replace('#(?><?[^<]*+)*?(?:<[a-z0-9]++\K\s++|\G[^\>=]'.
				'++=(?(?=\s*+["\'])\s*+["\'][^"\']*+["\']|[^\s]++)\K\s++|$\K)#i',' ',$str);

			// Remove last whitespace in open tag
			$str = preg_replace('#(?><?[^<]*+)*?(?:<[a-z0-9]++(?>\s*+[^\s>]++)*?'.
				'\K(?:\s++(?=>)|(?<=["\'])\s++(?=/>))|$\K)#i','',$str);

			// Replace line feed with space
			$str = preg_replace(
				'#(?>[^<]*+(?:<script\b[^>]*+>(?><?[^<]*+)*?</script>|<(?>[^>\'"]*+'.
				'(?:"[^"]*+"|\'[^\']*+\')?)*?>)?)*?\K'.
				'(?:[\r\n\t\f]++(?=<)|$)#', ' ', $str);

			// Remove whitespace(s) and carriage return between HTML tags
			$str=preg_replace('/(>\s+<)|(>\n+<)/', '><', $str);
		}

		// Remove HTML comments (not containing IE conditional comments)
		if ($arrSettings['remove_comments']??0) {
			$str=preg_replace(
				'#(?>(?:<(?!!))?[^<]*+(?:<(?:script|style)\b[^>]*+>(?>'.
				'<?[^<]*+)*?<\/(?:script|style)>|<!--\[(?><?[^<]*+)*?'.
				'<!\s*\[(?>-?[^-]*+)*?--!?>|<!DOCTYPE[^>]++>)?)*?\K(?:'.
				'<!--(?>-?[^-]*+)*?--!?>|[^<]*+\K$)#i', '', $str);
		}

	   	/*if ($aeSettings->getDevMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->here("After optimization",1);
			echo '<pre>'.str_replace('<','&lt;',$str).'</pre>';
		}*/

		return trim($str);
	}

	/**
	 * Optimize CSS
	 */
	private static function optimizeCSS(string $str, array $arrSettings) : string
	{

		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$lib=$aeSettings->getFolderLibs().'minify/src/';

		if (is_dir($lib)) {

			/*if ($aeSettings->getDevMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
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

				for($k=0; $k<$j; $k++) {

					if (($inline=trim($arrMatches[1][$k]))!=='') {

						if ($arrSettings['minify']??0) {

							// Inline style = minify the style
							$minifier = new \MatthiasMullie\Minify\CSS();
							$minifier->add($inline);

							$css=$minifier->minify();
							unset($minifier);

							// and replace the original, unminified, by the optimized version
							$str=str_replace($arrMatches[1][$k],$css,$str);

						} // if ($arrSettings['minify']??0)

					} // if (($inline=trim($arrMatches[1][$k]))!=='')

				} // for

			} // if (count($arrMatches[0])>0)

			/*if ($aeSettings->getDevMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->here("After optimization",1);
				echo '<pre>'.str_replace('<','&lt;',$str).'</pre>';
				die("Died in ".__FILE__.", line ".__LINE__);
			}*/
		}

		return $str;

	}

	private static function jsAddDefer(string $script, array $arrSettings) : string
	{

		$sReturn=$script;

		$jsTag=substr($script, 0, strpos($script, '>')+1);
		if (stripos($jsTag,' defer')===false) {

			$except=$arrSettings['defer_exceptions']??array();
			$except=implode($except,';');

			$bDoIt=1;
			if(trim($except)!=='') {
				$bDoIt=(preg_match('/'.str_replace(array(',',';'),'|',$except).'/i', $jsTag)<1);
			}

			if ($bDoIt) {
				$script=str_replace($jsTag,rtrim($jsTag,'>').' defer="defer">',$script);
				$sReturn=str_replace($jsTag,rtrim($jsTag,'>').' defer="defer">',$script);
			}
		}

		return $sReturn;
	}

	/**
	 * Optimize JS
	 */
	private static function optimizeJS(string $str, array $arrSettings) : string {

		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();

		// In case of, remove empty tag
		$str=str_replace('<script type="text/javascript"></script>','',$str);
		$str=str_replace('<script type="text/javascript" defer="defer"></script>','',$str);

		// Base url (f.i. http://localhost:8080/notes/)
		$baseURL=$aeFunctions->getCurrentURL();

		$lib=$aeSettings->getFolderLibs().'minify/src/';

		if (is_dir($lib)) {

			// Retrieve all <script> definition in the HTML content
			$arrMatches=null;

			preg_match_all("/<script[^>]*?>([\s\S]*?)<\/script>/", $str, $arrMatches);

			if (count($arrMatches[0])>0) {

				$j=count($arrMatches[0]);

				//@link https://github.com/matthiasmullie/minify
				//include_once($lib.'Exception.php');
				//include_once($lib.'Exceptions/BasicException.php');
				//include_once($lib.'Exceptions/IOException.php');
				include_once($lib.'Minify.php');
				include_once($lib.'JS.php');

				for($k=0; $k<$j; $k++) {

					if (($inline=trim($arrMatches[1][$k]))!=='') {

						if ($arrSettings['minify']??0) {

							// Inline script = minify the script
							$minifier = new \MatthiasMullie\Minify\JS();
							$minifier->add($inline);
							$js=$minifier->minify();
							unset($minifier);

							// and replace the original, unminified, by the optimized version
							$str=str_replace($arrMatches[1][$k],$js,$str);

						} // if ($arrSettings['minify']??0)

					} else {

						// It's a file inclusion like :
						// <script type="text/javascript"
						// src="http://localhost:8080/notes/libs/jquery/jquery.min.js"></script>

						$script=$arrMatches[0][$k];

						// Retrieve the file (http://localhost:8080/notes/libs/jquery/jquery.min.js)
						if (preg_match("/.*src=\"(.*)\".*/", $script, $matches)) {

							// Check if we need to add the Defer attribute
							if ($arrSettings['add_defer']??0) {
								$js= self::jsAddDefer($script, $arrSettings);
								// and replace the original, unminified, by the optimized version
								$str=str_replace($script,$js,$str);
							}

						} // if(preg_match

					} // if (($inline=trim($arrMatches[1][$k]))!=='')

				} // for($k=0; $k<$j; $k++)

			} // if (count($arrMatches[0])>0)

		} // if (is_dir($lib))

		/*if ($aeSettings->getDevMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->here("After optimization",1);
			echo '<pre>'.str_replace('<','&lt;',$str).'</pre>';
			die("Died in ".__FILE__.", line ".__LINE__);
		}*/

		return $str;

	}

	/**
	 * Modify the HTML rendering of the note
	 */
	public static function doIt(&$content = null)
	{
		if (trim($content) === '') {
			return true;
		}

		$aeSettings = \MarkNotes\Settings::getInstance();

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log('minify_html', 'debug');
		}
		/*<!-- endbuild -->*/

        $arrSettings = $aeSettings->getPlugins('options','optimize');

		// Should the HTML be optimized ?
		$arrHTML=$arrSettings['html']??null;
		if ($arrHTML!==null) $content = self::optimizeHTML($content, $arrHTML);

		// Should the CSS be optimized ?
		$arrCSS=$arrSettings['css']??null;
		if ($arrCSS!==null) $content = self::optimizeCSS($content, $arrCSS);

		// Should the javascript be optimized ?
		$arrJS=$arrSettings['js']??null;
		if ($arrJS!==null) $content = self::optimizeJS($content, $arrJS);

		return true;
	}

	/**
	 * Attach the function and responds to events
	 */
	public function bind()
	{
		$aeSession = \MarkNotes\Session::getInstance();
		$task = $aeSession->get('task', '');

		// This plugin is needed only for these tasks
		if (!in_array($task, array('html'))) {
			return false;
		}

		$aeEvents = \MarkNotes\Events::getInstance();

		// display.html = when the full page is being rendered;
		// not only the content (like render.content)
		$aeEvents->bind('display.html', __CLASS__.'::doIt');
		return true;
	}
}
