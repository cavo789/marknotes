<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Joomla_Doc
{
    public static function doIt(&$content = null) : bool
    {
        if (trim($content) === '') {
            return true;
        }

        $manifest = 'C:\Christophe\Sites\aes3\administrator\components\com_contact\contact.xml';

        if (strpos($content, '%com_contacts%') !== false) {
            $tmp = file_get_contents($manifest);
            $content = str_replace('%com_contacts%', '<pre>'.str_replace('<', '&lt;', $tmp).'</pre>', $content);
        }
        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind() : bool
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.content', __CLASS__.'::doIt');
        return true;
    }
}
