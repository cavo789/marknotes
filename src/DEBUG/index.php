<?php

namespace MarkNotes;

class Debugging {

	static $host = '';

	public function __construct(string $host)
	{
		self::$host=$host;
	}

	/**
	 * Helper - Create an accordion
	 */
	private function makeHeading(string $title, string $icon = '') : string
	{
		if (trim($icon) !=='') {
			$icon = '<i class="fa fa-'.$icon.'" aria-hidden="true"></i> ';
		}

		$return = '<details><summary>'.$icon.$title.'</summary>'.
			'<ul>%CONTENT%</ul></details>';

		return $return;
	}

	/**
	 * Helper - Create an URL and add data- elements to properly
	 * manage these links with an ajax request
	 */
	private function makeURL(string $url, string $caption, string $type = 'json') : string
	{
		return '<a data-task="ajax" data-type="'.$type.'" '.
			'href="'.self::$host.$url.'" target="_blank">'.
			$caption.'</a>';
	}

	/**
	 * CRUD operations on files
	 */
	private function getFiles() : string
	{
		$html = self::makeHeading('Files','file-o');

		$content ='<li>'.
			self::makeURL('index.php?task=task.file.create&param=VGVtcG9yYWlyZSUyRmFhJTJGQUFB','Create file /docs/temporaire/aa/AAA').
			'</li>';
		$content .='<li>'.
			self::makeURL('index.php?task=task.file.rename&param=VGVtcG9yYWlyZSUyRmFhJTJGQkJC&oldname=VGVtcG9yYWlyZSUyRmFhJTJGQUFB','Rename /docs/temporaire/aa/AAA into /docs/temporaire/aa/BBB').
			'</li>';
		$content .='<li>'.
			self::makeURL('index.php?task=task.file.delete&param=&oldname=VGVtcG9yYWlyZSUyRmFhJTJGQkJC','Kill /docs/temporaire/aa/BBB').
			'</li>';
		$html = str_replace('%CONTENT%', $content, $html);
		return $html;
	}

	/**
	 * CRUD operations on folders
	 */
	private function getFolders() : string
	{
		$html = self::makeHeading('Folders','folder-o');

		$content ='<li>'.
			self::makeURL('index.php?task=task.folder.create&param=VGVtcG9yYWlyZSUyRmFhJTJGYmNk','Create folder  /docs/temporaire/aa/bcd').
			'</li>';
		$content .='<li>'.
			self::makeURL('index.php?task=task.folder.renameparam=VGVtcG9yYWlyZSUyRmFhJTJGZWZn&oldname=VGVtcG9yYWlyZSUyRmFhJTJGYmNk','Rename /docs/temporaire/aa/bcd into /docs/temporaire/aa/efg').
			'</li>';
		$content .='<li>'.
			self::makeURL('index.php?task=task.folder.delete&oldname=VGVtcG9yYWlyZSUyRmFhJTJGYmNk','Kill folder /docs/temporaire/aa/bcd').
			'</li>';

		$html = str_replace('%CONTENT%', $content, $html);
		return $html;
	}

	/**
	 * Prepare a few URLs for searching notes
	 */
	private function getSearch() : string
	{
		// A few keywords to search for
		$arr = array('Marknotes', 'plugins', 'aeSecure');

		$html = self::makeHeading('Search','search');

		$content = '';

		foreach ($arr as $item) {
			$content .='<li>'.
				self::makeURL('index.php?task=task.search.search&str='.$item,'Searching for keyword='.$item).'</li>';
		}

		$html = str_replace('%CONTENT%', $content, $html);
		return $html;
	}

	/**
	 * These files should be accessible and shouldn't contains
	 * errors
	 */
	private function getSpecialFiles() : string
	{
		$arr = array(
			'buttons.json'	=> 'json',
			'languages/languages.json'=> 'json',
			'languages/marknotes-en.json'=> 'json',
			'languages/marknotes-fr.json'=> 'json',
			'listfiles.json'=> 'json',
			'robots.txt' 	=> 'text',
			'sitemap.xml' 	=> 'xml',
			'tags.json' 	=> 'json',
			'timeline.json' => 'json',
			'timeline.html' => 'html'
		);

		$html = self::makeHeading('Special files','user-secret');

		$content = '';

		foreach ($arr as $item => $type) {
			$content .='<li>'.
				self::makeURL($item, $item, $type).'</li>';
		}

		$html = str_replace('%CONTENT%', $content, $html);
		return $html;
	}

	/**
	 * These files are prohibited : the user can't access them
	 * by URL
	 */
	private function getProhibitedFiles() : string
	{
		$arr = array(
			'.htaccess'			=> 'html',
			'htaccess.txt'		=> 'html',
			'package.json'		=> 'html',
			'robots.txt.dist'	=> 'html',
			'settings.json'		=> 'html',
			'settings.json.dist'=> 'html'
		);

		$html = self::makeHeading('Prohibited files','ban');

		$content = '';

		foreach ($arr as $item => $type) {
			$content .='<li>'.
				self::makeURL($item, $item, $type).'</li>';
		}

		$html = str_replace('%CONTENT%', $content, $html);
		return $html;
	}

	/**
	 * Return the list of options
	 */
	public function getHTML() : string
	{
		$html = file_get_contents('template.html');

		$content = '';

		$content .= self::getFolders();
		$content .= self::getFiles();
		$content .= self::getSearch();
		$content .= self::getSpecialFiles();
		$content .= self::getProhibitedFiles();

		return str_replace('%CONTENT%', $content, $html);

	}
}

$host = 'http://localhost:8080/notes/';
$aeDebug = new \MarkNotes\Debugging($host);
echo $aeDebug->getHTML();
unset($aeDebug);
