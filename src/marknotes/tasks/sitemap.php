<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

class Sitemap
{
    protected static $_instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {

        if (self::$_instance === null) {
            self::$_instance = new Sitemap();
        }

        return self::$_instance;
    }

    public function run()
    {

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $arrFiles=array();

        if ($aeSettings->getOptimisationUseServerSession()) {
            // Get the list of files/folders from the session object if possible
            $arrFiles=json_decode($aeSession->get('Sitemap', ''));
        }

        if (count($arrFiles)==0) {
            // Not yet in the session object

            $arrFiles=$aeFunctions->array_iunique($aeFiles->rglob('*.md', $aeSettings->getFolderDocs(true)));

            natcasesort($arrFiles);

            // Be carefull, folders / filenames perhaps contains accentuated characters
            $arrFiles=array_map('utf8_encode', $arrFiles);

            if ($aeSettings->getOptimisationUseServerSession()) {
                $aeSession->set('Sitemap', json_encode($arrFiles));
            }
        } // if (count($arrFiles)==0)

        $xml='';

        $folder=str_replace('/', DS, $aeSettings->getFolderDocs(true));

        foreach ($arrFiles as $file) {
            $relFileName=str_replace($folder, '', $file);
            
            $url=rtrim($aeFunctions->getCurrentURL(false, false), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';
            $urlHTML=$url.str_replace(DS, '/', $aeFiles->replaceExtension($relFileName, 'html'));

            $xml.=
                '      <url>'.PHP_EOL.
                '         <loc>'.str_replace(' ', '%20', htmlspecialchars($urlHTML, ENT_HTML5)).'</loc>'.PHP_EOL.
                '         <lastmod>'.date('Y-m-d\TH:i:sP', filemtime($file)).'</lastmod>'.PHP_EOL.
                '         <changefreq>weekly</changefreq>'.PHP_EOL.
                '         <priority>1.0</priority>'.PHP_EOL.
                '      </url>'.PHP_EOL;
        } // foreach


        $sReturn=
            '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.
                'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" '.
                'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.
            '   '.$xml.PHP_EOL.
            '</urlset>';

        return $sReturn;
    }
}
