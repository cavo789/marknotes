<?php
/**
 * Provide sharing functionnalities
 */

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Share
{
    public static function doIt(&$html = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $url = rtrim($aeFunctions->getCurrentURL(false, false), '/');
        $urlHTML = '';
        if (isset($_REQUEST['file'])) {
            $urlHTML = $url.'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';
            $urlHTML .= str_replace(DS, '/', $aeFiles->replaceExtension($_REQUEST['file'], 'html'));
        }

        if (file_exists($fname = __DIR__.'/share/template.html')) {
            $tmpl = str_replace('%URL%', $urlHTML, file_get_contents($fname));
            $tmpl = str_replace('%ROOT%', $url, $tmpl);
            $html = str_replace('</body>', $tmpl.'</body>', $html);
        }


        return true;
    }

    /**
     * Provide additionnal css
     */
    public static function addCSS(&$css = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $css .= "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/libs/jquery-toolbar/jquery.toolbar.css\" />\n";
        $css .= "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/marknotes/plugins/content/html/share/assets/share.css\" />\n";

        return true;
    }

    /**
     * Provide additionnal javascript
     */
    public static function addJS(&$js = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');


        if ($aeSettings->getDebugMode()) {
            $js .= "\n<!-- Lines below are added by ".__FILE__."-->";
        }

        $js .= "<script type=\"text/javascript\" src=\"".$root."/libs/jquery-toolbar/jquery.toolbar.min.js\"></script>\n".
        "<script type=\"text/javascript\" src=\"".$root."/marknotes/plugins/content/html/share/assets/share.js\"></script>\n";

        if ($aeSettings->getDebugMode()) {
            $js .= "<!-- End for ".__FILE__."-->";
        }

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');

        // Don't load the Share toolbar for slideshows and for pdf rendering
        if (in_array($task, array('pdf' , 'reveal','remark','slides'))) {
            return true;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('display.html', __CLASS__.'::doIt');
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        $aeEvents->bind('render.css', __CLASS__.'::addCSS');
        return true;
    }
}
