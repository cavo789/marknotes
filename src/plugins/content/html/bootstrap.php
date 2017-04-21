<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Bootstrap
{

    /**
     * Set the ul/li style to use Font-Awesome
     */
    private static function setBullets(string $html) : string
    {

        // Replace <li></li> but only if they're part of a <ul></ul> i.e. don't modify <li> for <ol>
        // http://stackoverflow.com/a/4835671
        $sReturn = preg_replace_callback(
           "/(<ul>.*<\/ul>)/Ums",
           function ($ol) {
               // The anonymous function requires to declare the $aeSettings class
               $aeSettings = \MarkNotes\Settings::getInstance();
               $arrSettings = $aeSettings->getPlugins('options', 'bootstrap');
               $icon = $arrSettings['bullet'] ?? 'check';
               $extra = $arrSettings['extra_attribute'] ?? '';
               return preg_replace("/(<li(|\s*\/)>)/", "<li><i class='fa-li fa fa-".$icon."' ".$extra."></i>", $ol[1]);
           },
           $html
       );

        return str_replace('<ul>', '<ul class="fa-ul">', $sReturn);
    }

    /**
     * Add Bootstrap classes for tables and add a parent div so tables will be responsive
     */
    private static function setTables(string $html) : string
    {

        // Add bootstrap to tables
        $html = str_replace('<table>', '<div class="table-responsive"><table class="table table-striped table-bordered table-hover">', $html);
        $html = str_replace('</table>', '</table></div>', $html);

        return $html;
    }

    public static function doIt(&$html = null)
    {
        $html = self::setBullets($html);
        $html = self::setTables($html);

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('display.html', __CLASS__.'::doIt');
        return true;
    }
}
