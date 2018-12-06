<?php
/**
 * Export the note as a .md file
 */

namespace MarkNotes\Plugins\Task\Export;

defined('_MARKNOTES') or die('No direct access allowed');

class MD extends \MarkNotes\Plugins\Task\Plugin
{
    protected static $me = __CLASS__;
    protected static $json_settings = 'plugins.task.export.md';
    protected static $json_options = '';

    private static $extension = 'md';

    /**
     * Make the conversion
     */
    public static function run(&$params = null) : bool
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // Display a .md file, call plugins and output note's content
        $final = $aeSettings->getFolderDocs(true).$params['filename'];

        // Get the markdown content, run markdown plugins
        $aeEvents->loadPlugins('markdown');
        $content = $aeFiles->getContent($final);
        
        $params['markdown'] = $content;
        $params['filename'] = $final;
        $args = array(&$params);
        $aeEvents->trigger('markdown::markdown.read', $args);
        $content = $args[0]['markdown'];

        // In case of error, there is no output at all
        $params['content'] = $content;
        return true;
    }
}
