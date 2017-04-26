<?php

/**
 * Return a timeline with the list of notes displayed in a descending chronological order
 */

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class Timeline
{
    private static function getJSON() : bool
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
        $aeJSON = \MarkNotes\JSON::getInstance();
        $aeMarkDown = \MarkNotes\FileType\MarkDown::getInstance();

        $sReturn = '';

        if ($aeSettings->getOptimisationUseServerSession()) {
            // Get the list of files/folders from the session object if possible
            $aeSession = \MarkNotes\Session::getInstance();
            $sReturn = $aeSession->get('timeline', '');
        }

        if ($sReturn === '') {
            $json = array();

            $folder = str_replace('/', DS, $aeSettings->getFolderDocs(true));

            $arrFiles = $aeFunctions->array_iunique($aeFiles->rglob('*.md', $aeSettings->getFolderDocs(true)));

            // -------------------------------------------------------
            // Based on https://github.com/Albejr/jquery-albe-timeline
            // -------------------------------------------------------

            foreach ($arrFiles as $file) {
                $content = $aeMarkDown->read($file);

                $relFileName = utf8_encode(str_replace($folder, '', $file));

                $url = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DIRECTORY_SEPARATOR).'/';
                $urlHTML = $url.str_replace(DIRECTORY_SEPARATOR, '/', $aeFiles->replaceExtension($relFileName, 'html'));

                $json[] =
                  array(
                'fmtime' => filectime($file),
                'time' => date("Y-m-d", filectime($file)),
                'header' => $aeMarkDown->getHeadingText($content),
                'body' => array(
                  array(
                    'tag' => 'a',
                    'content' => $relFileName,
                    'attr' => array(
                      'href' => $urlHTML,
                      'target' => '_blank',
                      'title' => $relFileName
                    ) // attr
                  ),
                  array(
                    'tag' => 'span',
                    'content' => ' ('
                  ),
                  array(
                  'tag' => 'a',
                  'content' => 'slide',
                  'attr' => array(
                    'href' => $urlHTML.'?format=slides',
                    'target' => '_blank',
                    'title' => $relFileName
                    ) // attr
                  ),
                  array(
                    'tag' => 'span',
                    'content' => ' - '
                  ),
                  array(
                  'tag' => 'a',
                  'content' => 'pdf',
                  'attr' => array(
                    'href' => $urlHTML.'?format=pdf',
                    'target' => '_blank',
                    'title' => $relFileName
                    ) // attr
                  ),
                  array(
                    'tag' => 'span',
                    'content' => ')'
                  )
                  ) // body
              ); //
            } // foreach

            usort($json, function ($a, $b) {
                //return strtotime($a['start_date']) - strtotime($b['start_date']);
                return strcmp($b['fmtime'], $a['fmtime']);
            });

            $sReturn = $aeJSON->json_encode($json, JSON_PRETTY_PRINT);

            if ($aeSettings->getOptimisationUseServerSession()) {
                // Remember for the next call
                $aeSession->set('timeline', $sReturn);
            }
        }

        header('Content-Type: application/json');
        echo $sReturn;

        return true;
    }

    private static function getHTML(array $params = array()) : bool
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $aeHTML = \MarkNotes\FileType\HTML::getInstance();

        // Define the global marknotes variable.  Used by the assets/js/marknotes.js script
        $JS =
            "\nvar marknotes = {};\n".
            "marknotes.autoload=0;\n".
            "marknotes.url='index.php';\n".
            "marknotes.settings={};\n".
            "marknotes.settings.debug=".($aeSettings->getDebugMode()?1:0).";\n".
            "marknotes.settings.locale='".$aeSettings->getLocale()."';\n".
            "marknotes.settings.use_localcache=".($aeSettings->getUseLocalCache()?1:0).";\n";

        $html = file_get_contents($aeSettings->getTemplateFile('timeline'));
        $html = str_replace('<!--%MARKDOWN_GLOBAL_VARIABLES%-->', '<script type="text/javascript">'.$JS.'</script>', $html);

        header('Content-Type: text/html; charset=utf-8');
        echo $aeHTML->replaceVariables($html, '', $params);

        return true;
    }

    public static function run(&$params = null)
    {
        $aeSession = \MarkNotes\Session::getInstance();

        if ($aeSession->get('layout', 'html') === 'html') {
            self::getHTML($params);
        } else {
            self::getJSON();
        }

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
