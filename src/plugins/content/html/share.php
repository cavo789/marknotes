<?php

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

    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('display.html', __CLASS__.'::doIt');
        return true;
    }
}
