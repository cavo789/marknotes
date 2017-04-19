<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class ReplaceVariables
{
    public static function doIt(&$args = null)
    {
        $args = str_replace('Joomla!&reg;', '<strong style="color:red;">WordPress</strong>', $args);
        return true;
    }

    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('display.html', __CLASS__.'::doIt');
        return true;
    }
}
