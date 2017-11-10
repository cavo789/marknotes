# Marknotes - Plugins - Type Markdown

A markdown plugin is a piece of code that will be fired when the note (.md file) will be read on disk whatever the output format (html, epub, pdf, ...).

Such plugin will then always be fired (unless you specify the `not_if_task` in the settings.json for that plugin). For instance : not run the plugin when the task is the search engine (`task.search.search`).

The minimalist code for a markdown plugin is this one :

```php
<?php
/**
 * YOUR_PLUGIN_NAME description
 */
namespace MarkNotes\Plugins\Content\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class YOUR_PLUGIN_NAME extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.abbreviations';
	protected static $json_options = 'plugins.options.markdown.abbreviations';

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		// Do something with $params['markdown']
		// ...

		return true;
	}

	/**
	 * Determine if this plugin is needed or not
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			// Make extra tests and initialize $bCanRun = false when
			// the plugin is not needed (f.i. check settings and if
			// nothing has been specified, no need to fire the plugin)
		}

		return $bCanRun;
	}
}
```
