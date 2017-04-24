<?php

/**
 * What are the actions to fired when MarkNotes is running the "reveal" task ?
 */

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class Reveal
{
    private static $extension = 'reveal';

    public static function run(&$params = null)
    {
        // Display the HTML rendering of a note
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->loadPlugins('content', 'slides');
        $args = array(&$params);
        $params['layout'] = static::$extension;
        $aeEvents->trigger('export.slides', $args);

        echo $params['html'];

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('run.task', __CLASS__.'::run');
        return true;
    }
}
