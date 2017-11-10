<?php

/**
 * Optimize HTML
 */

 namespace MarkNotes\Plugins\Page\HTML\Optimize;

 defined('_MARKNOTES') or die('No direct access allowed');

class HTML
{

	public static function run(string $str, array $arrOptimize) : string
	{

		$aeSettings = \MarkNotes\Settings::getInstance();

		/*
		$aeDebug = \MarkNotes\Debug::getInstance();
		if ($aeDebug->getDevMode()) {
		   $aeDebug->here("Before optimization",1);
		   echo '<pre>'.str_replace('<','&lt;',$str).'</pre>';
		}*/

		// Minify content
		if ($arrOptimize['minify']??0) {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log('HTML - Minify', 'debug');
			}
			/*<!-- endbuild -->*/

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
				'++=(?(?=\s*+["\'])\s*+["\'][^"\']*+["\']|[^\s]++)\K\s++|$\K)#i', ' ', $str);

			// Remove last whitespace in open tag
			$str = preg_replace('#(?><?[^<]*+)*?(?:<[a-z0-9]++(?>\s*+[^\s>]++)*?'.
				'\K(?:\s++(?=>)|(?<=["\'])\s++(?=/>))|$\K)#i', '', $str);

			// Replace line feed with space
			$str = preg_replace(
				'#(?>[^<]*+(?:<script\b[^>]*+>(?><?[^<]*+)*?</script>|<(?>[^>\'"]*+'.
				'(?:"[^"]*+"|\'[^\']*+\')?)*?>)?)*?\K'.
				'(?:[\r\n\t\f]++(?=<)|$)#',
				' ',
				$str
			);

			// Remove whitespace(s) and carriage return between HTML tags
			$str=preg_replace('/(>\s+<)|(>\n+<)/', '><', $str);
		}

		// Remove HTML comments (not containing IE conditional comments)
		if ($arrOptimize['remove_comments']??0) {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log('HTML - Remove comments', 'debug');
			}
			/*<!-- endbuild -->*/

			$str=preg_replace(
				'#(?>(?:<(?!!))?[^<]*+(?:<(?:script|style)\b[^>]*+>(?>'.
				'<?[^<]*+)*?<\/(?:script|style)>|<!--\[(?><?[^<]*+)*?'.
				'<!\s*\[(?>-?[^-]*+)*?--!?>|<!DOCTYPE[^>]++>)?)*?\K(?:'.
				'<!--(?>-?[^-]*+)*?--!?>|[^<]*+\K$)#i',
				'',
				$str
			);
		}

		/*
		$aeDebug = \MarkNotes\Debug::getInstance();
		if ($aeDebug->getDevMode()) {
		   $aeDebug->here("After optimization",1);
		   echo '<pre>'.str_replace('<','&lt;',$str).'</pre>';
		}*/

		return trim($str);
	}
}
