<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Custom
{

    /**
     * Provide additionnal javascript
     */
    public static function addJS(&$js = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        if ($aeFiles->fileExists($aeSettings->getFolderWebRoot()."custom.js")) {
            // if present, add your custom javascript if the custom.js file is present. That file should be present in the root folder; not in /assets/js
            $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');
			
			if ($aeSettings->getDebugMode()) {
				$js .= "\n<!-- Lines below are added by ".__FILE__."-->";
			}
		
            $js .= "\n<script type=\"text/javascript\" src=\"".$root."/custom.js\"></script>\n";
			
			if ($aeSettings->getDebugMode()) {
				$js .= "<!-- End for ".__FILE__."-->";
			}
			
        }

        return true;
    }

    /**
     * Provide additionnal css
     */
    public static function addCSS(&$css = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        if ($aeFiles->fileExists($aeSettings->getFolderWebRoot()."custom.css")) {
            // if present, add your custom javascript if the custom.js file is present. That file should be present in the root folder; not in /assets/js
            $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');
            $css .= "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/custom.css\"/>";
        }

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        $aeEvents->bind('render.css', __CLASS__.'::addCSS');
        return true;
    }
}
