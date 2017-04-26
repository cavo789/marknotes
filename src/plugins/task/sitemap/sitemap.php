<?php

/**
 * Display the sitemap
 */

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class Sitemap
{
    private static function getFiles() : array
    {
        $arrFiles = array();

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        if ($aeSettings->getOptimisationUseServerSession()) {
            // Get the list of files/folders from the session object if possible
            $arrFiles = json_decode($aeSession->get('Sitemap', ''));
        }

        if (count($arrFiles) == 0) {
            // Not yet in the session object

            $arrFiles = $aeFunctions->array_iunique($aeFiles->rglob('*.md', $aeSettings->getFolderDocs(true)));

            natcasesort($arrFiles);

            // Be carefull, folders / filenames perhaps contains accentuated characters
            $arrFiles = array_map('utf8_encode', $arrFiles);

            if ($aeSettings->getOptimisationUseServerSession()) {
                $aeSession->set('Sitemap', json_encode($arrFiles));
            }
        } // if (count($arrFiles)==0)

        return $arrFiles;
    }

    public static function run(&$params = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $arrFiles = self::getFiles();

        $xml = '';

        $folder = str_replace('/', DS, $aeSettings->getFolderDocs(true));

        foreach ($arrFiles as $file) {
            $relFileName = str_replace($folder, '', $file);

            $url = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';
            $urlHTML = $url.str_replace(DS, '/', $aeFiles->replaceExtension($relFileName, 'html'));

            if (!$aeFiles->fileExists($file)) {
                $file = utf8_decode($file);
            }

            // filemtime will raise an error when the filename contains f.i. accentuated characters
            $lastModified = @filemtime($file);
            if ($lastModified == null) {
                $lastModified = filemtime(utf8_decode($file));
            }

            $xml .=
                '      <url>'.PHP_EOL.
                '         <loc>'.str_replace(' ', '%20', htmlspecialchars($urlHTML, ENT_HTML5)).'</loc>'.PHP_EOL.
                '         <lastmod>'.date('Y-m-d\TH:i:sP', $lastModified).'</lastmod>'.PHP_EOL.
                '         <changefreq>weekly</changefreq>'.PHP_EOL.
                '         <priority>1.0</priority>'.PHP_EOL.
                '      </url>'.PHP_EOL;
        } // foreach

        $sReturn =
            '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.
                'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" '.
                'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.
            '   '.$xml.PHP_EOL.
            '</urlset>';

        // Nothing should be returned, the xml can be displayed immediatly
        header('Content-Type: application/xml; charset=utf-8');
        echo $sReturn;

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
