<?php

namespace AeSecure\Tasks;

class Sitemap
{
    protected static $_instance = null;

    private $_aeSettings = null;

    public function __construct()
    {

        if (!class_exists('Settings')) {
            include_once dirname(__DIR__).DS.'settings.php';
        }

        $this->_aeSettings=\AeSecure\Settings::getInstance();

        return true;
    } // function __construct()

    public static function getInstance()
    {

        if (self::$_instance === null) {
            self::$_instance = new Sitemap();
        }

        return self::$_instance;
    } // function getInstance()

    public function run()
    {


        include_once(dirname(__DIR__)).'/files.php';
        include_once(dirname(__DIR__)).'/functions.php';

        $folder=str_replace('/', DS, $this->_aeSettings->getFolderDocs(true));

        $arrFiles=\AeSecure\Functions::array_iunique(\AeSecure\Files::rglob('*.md', $this->_aeSettings->getFolderDocs(true)));

        foreach ($arrFiles as $file) {
            $relFileName=utf8_encode(str_replace($folder, '', $file));

            $url=rtrim(\AeSecure\Functions::getCurrentURL(false, false), '/').'/'.rtrim($this->_aeSettings->getFolderDocs(false), DIRECTORY_SEPARATOR).'/';
            $urlHTML=$url.str_replace(DIRECTORY_SEPARATOR, '/', \AeSecure\Files::replaceExtension($relFileName, 'html'));

            $arr[]=str_replace(' ', '%20', htmlspecialchars($urlHTML, ENT_HTML5));
        } // foreach

        $sReturn=
            '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.
            '   <sitemap>'.PHP_EOL.
            '      <loc>'.implode($arr, '</loc>'.PHP_EOL.'      <loc>').'</loc>'.PHP_EOL.
            '   </sitemap>'.PHP_EOL.
            '</sitemapindex>';
//echo '<pre>'.str_replace('<', '&lt;', $sReturn).'</pre>';die();
        return $sReturn;
    } // function run()
} // class Sitemap
