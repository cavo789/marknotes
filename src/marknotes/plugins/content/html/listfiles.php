<?php

/**
 * Retrieve the list of files in a specific folder
 *
 * Find any '%LISTFILES foldername%' and get the list of files found in that folder.
 */

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class ListFiles
{
    public static function doIt(&$content = null) : bool
    {
        if (trim($content) === '') {
            return true;
        }

        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');
        $layout = $aeSession->get('layout');

        $matches = array();
        // Check if the content contains things like '%LISTFILES foldername%'
        // i.e. '%LISTFILES ' followed by a foldername and ending by '%'

        if (preg_match_all('/%LISTFILES ([^\\%]*)%/', $content, $matches)) {
            $aeFiles = \MarkNotes\Files::getInstance();
            $aeFunctions = \MarkNotes\Functions::getInstance();
            $aeSettings = \MarkNotes\Settings::getInstance();
            $aeSession = \MarkNotes\Session::getInstance();

            // Retrieve the note fullpath
            $root = rtrim($aeSettings->getFolderDocs(true), DS).DS;
            $root = $root.dirname($aeSession->get('filename')).DS;

            // Retrieve the note URL
            $url = rtrim($aeFunctions->getCurrentURL(true, false), '/');
            $url .= '/'.$aeSettings->getFolderDocs(false);
            $url .= dirname($aeSession->get('filename')).DS;
            $url = str_replace(DS, '/', $url);

            $arrTags = $matches[0];
            $arrFolders = $matches[1];

            $i = 0;
            $j = count($arrFolders);

            for ($i == 0; $i < $j; $i++) {

                // Retrieve the full folder name like
                // C:\sites\notes\docs\folder\subfolder
                $folder = str_replace('/', DS, $arrFolders[$i]);
                if (!(is_dir($folder))) {
                    $folder = $root.$folder.DS;
                }

                if (!(is_dir($folder))) {
                    // Correctly handle accentuated characters
                    $folder = utf8_decode($folder);
                }

                if (is_dir($folder)) {
                    $arrFiles = $aeFiles->rglob('*', $folder);

                    $sList = '';
                    foreach ($arrFiles as $file) {
                        $ext = strtolower($aeFiles->getExtension($file));

                        switch ($ext) {
                             case 'doc':
                             case 'docx':
                                 $icon = 'file-excel-o';
                                 break;
                            case 'pdf':
                               $icon = 'file-pdf-o';
                               break;
                            case 'ppt':
                            case 'pptx':
                                $icon = 'file-powerpoint-o';
                                break;
                            case 'txt':
                               $icon = 'file-text-o';
                               break;
                            case 'xls':
                            case 'xlsx':
                                $icon = 'file-excel-o';
                                break;
                            case '7z':
                            case 'gzip':
                            case 'tar':
                             case 'zip':
                                 $icon = 'file-archive-o';
                                 break;
                            default:
                                $icon = 'file-o';
                        }

                        $file = str_replace($root, '', utf8_encode($file));

                        $relURL = str_replace(DS, '/', str_replace($root, '', $file));
                        $sList .= '<li><i class="fa fa-'.$icon.'"></i><a href="'.$url.$relURL.'">&nbsp;'.$relURL.'</a></li>';
                    }

                    if ($sList !== '') {
                        $sList = '<ul class="fa-ul">'.$sList.'</ul>';
                    }

                    $content = str_replace($arrTags[$i], $sList, $content);
                }
            }
        }
        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind() : bool
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');

        // This plugin is only needed when the task is one of the following
        if (!in_array($task, array('display','html','pdf','reveal'))) {
            return true;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.content', __CLASS__.'::doIt');
        return true;
    }
}
