<?php
/**
 * When the exportation has been done, display the file in the browser
 */
namespace MarkNotes\Plugins\Task\Export\After;

defined('_MARKNOTES') or die('No direct access allowed');

class Display extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.export.after.display';
	protected static $json_options = '';

	public static function run(&$params = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$format = $params['extension'];        // extension like 'txt'
		$content = $params['content'] ?? '';   // in case of a .md file f.i.

		if (trim($content) === '') {
			// When no content has been directly given to this function,
			// check the "output" variable; can contains a filename
			// (absolute name)

			$filename = $params['output'] ?? '';

			if ($filename!=='') {
				$content = 'binary';

				if (in_array($format, array('html','md','remark','reveal','txt'))) {
					// Read content only if it's an ASCII file
					if (is_file($filename)) {
						$content = trim(file_get_contents($filename));
					} elseif (is_file(utf8_decode($filename))) {
						// Arrrgh, sometimes with sometimes without utf8_decode,
						// it's crazy
						$content = trim(file_get_contents(utf8_decode($filename)));
					}
				}
			} else {
				/*<!-- build:debug -->*/

				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();

					if ($aeDebug->getDevMode()) {
						$aeDebug->here("The file [".$filename."] is missing; should be impossible", 10);
						echo "<pre style='background-color:yellow;'>".
							__FILE__." - ".__LINE__."<br/>".
							"<h4>Parameters of the ".__METHOD__." are</h4>".
							print_r($params, true)."</pre>";
					}
					$aeDebug->log("The file [".$filename."] is missing", "error");
				}
				/*<!-- endbuild -->*/
			} // if (($filename!=='') && (is_file($filename)))
		} // if (trim($content) === '')

		if ($content !== '') {
			if ($format==='md') {
				// If there is a YAML content, show it
				$yaml = trim($aeSession->get('yaml', ''));
				if ($yaml!=='') {
					$arr = $aeSettings->getPlugins('plugins.options.markdown.yaml');
					$separator = $arr['separator']??'---';
					$content = $separator."\n".$yaml."\n".$separator."\n\n".$content;
				}
			}

			switch ($format) {
				case 'md':
				case 'txt':
					header('Content-Type: text/plain; charset=utf-8');
					header('Content-Transfer-Encoding: ascii');
					echo $content;

					break;

				case 'html':
				case 'remark':
				case 'reveal':
					header('Content-Transfer-Encoding: ascii');
					header('Content-Type: text/html; charset=utf-8');

					// When the note is displayed through the interface
					// (i.e. using Ajax), we just need to have the content
					// and that content is inside the article tag
					if ($aeFunctions->isAjaxRequest()) {
						// The page has been accessed by an URL (and not through the interface)
						if (preg_match("/<article[^>]*>(.+)<\\/article>/s", $content, $match)) {
							list($pattern, $article) = $match;
							$content = trim($article);
						}
					}

					echo $content;

					break;

				default:
					// Default action will be download the file

					$aeDownload = \MarkNotes\Tasks\Download::getInstance();
					$aeDownload->run($filename, $format);
					break;
			} //switch
		} // if ($content !== '') {

		return true;
	}
}
