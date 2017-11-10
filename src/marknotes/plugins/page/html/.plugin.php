<?php
/**
 * Definition of a plugin of type Page HTML.
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

require_once (dirname(dirname(__DIR__)).DS.'.plugin.php');

abstract class Plugin extends \MarkNotes\Plugins\Plugin
{
	/**
 	 * Provide additionnal javascript
 	 */
	abstract public static function addJS(&$js = null) : bool;

	/**
	 * Provide additionnal stylesheets
	 */
    abstract public static function addCSS(&$css = null) : bool;

	/**
	 * Add/modify the HTML content
	 */
	abstract public static function doIt(&$html = null) : bool;

	/**
	 * Capture the render.js and render.css events
	 */
	public function bind(string $plugin) : bool
	{
		if ($bReturn = $this->canRun()) {
			$aeEvents = \MarkNotes\Events::getInstance();
			$aeEvents->bind('render.js', static::$me.'::addJS', $plugin);
			$aeEvents->bind('render.css', static::$me.'::addCSS', $plugin);
			$aeEvents->bind('render.html', static::$me.'::doIt', $plugin);
		}

		return $bReturn;
	}
}
