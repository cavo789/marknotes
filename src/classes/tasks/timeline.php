<?php

namespace AeSecure\Tasks;

class Timeline
{
    protected static $_instance = null;

    private $_aeSettings = null;
    private $_aeMD = null;

    public function __construct()
    {

        if (!class_exists('Settings')) {
            include_once dirname(__DIR__).DS.'settings.php';
        }

        $this->_aeSettings=\AeSecure\Settings::getInstance();

        include_once(dirname(__DIR__)).'/filetype/markdown.php';
        $this->_aeMD=\AeSecure\FileType\MarkDown::getInstance();

        return true;
    } // function __construct()

    public static function getInstance()
    {

        if (self::$_instance === null) {
            self::$_instance = new Timeline();
        }

        return self::$_instance;
    } // function getInstance()

    public function getJSON()
    {

        $json=array();

        include_once(dirname(__DIR__)).'/files.php';
        include_once(dirname(__DIR__)).'/functions.php';

        $folder=str_replace('/', DS, $this->_aeSettings->getFolderDocs(true));

        $arrFiles=\AeSecure\Functions::array_iunique(\AeSecure\Files::rglob('*.md', $this->_aeSettings->getFolderDocs(true)));

        // -------------------------------------------------------
        // Based on https://github.com/Albejr/jquery-albe-timeline
        // -------------------------------------------------------

        foreach ($arrFiles as $file) {
            $content=$this->_aeMD->read($file);

            $relFileName=utf8_encode(str_replace($folder, '', $file));

            $url=rtrim(\AeSecure\Functions::getCurrentURL(false, false), '/').'/'.rtrim($this->_aeSettings->getFolderDocs(false), DIRECTORY_SEPARATOR).'/';
            $urlHTML=$url.str_replace(DIRECTORY_SEPARATOR, '/', \AeSecure\Files::replaceExtension($relFileName, 'html'));

            $json[]=
              array(
                'fmtime'=>filectime($file),
                'time'=>date("Y-m-d", filectime($file)),
                'header'=> $this->_aeMD->getHeadingText($content),
                'body'=>array(
                  array(
                    'tag'=>'a',
                    'content'=> $relFileName,
                    'attr'=> array(
                      'href'=>$urlHTML,
                      'target'=> '_blank',
                      'title'=>$relFileName
                    ) // attr
                  ),
                  array(
                    'tag'=>'span',
                    'content'=> ' ('
                  ),
                  array(
                  'tag'=>'a',
                  'content'=> 'slide',
                  'attr'=> array(
                    'href'=>$urlHTML.'?format=slides',
                    'target'=> '_blank',
                    'title'=>$relFileName
                    ) // attr
                  ),
                  array(
                    'tag'=>'span',
                    'content'=> ' - '
                  ),
                  array(
                  'tag'=>'a',
                  'content'=> 'pdf',
                  'attr'=> array(
                    'href'=>$urlHTML.'?format=pdf',
                    'target'=> '_blank',
                    'title'=>$relFileName
                    ) // attr
                  ),
                  array(
                    'tag'=>'span',
                    'content'=> ')'
                  )
                ) // body
              ); //
        } // foreach

        usort($json, function ($a, $b) {
           //return strtotime($a['start_date']) - strtotime($b['start_date']);
            return strcmp($b['fmtime'], $a['fmtime']);
        });

        include_once(dirname(__DIR__)).'/json';
        return \AeSecure\JSON::json_encode($json, JSON_PRETTY_PRINT);
    } // function getJSON()

    public function run(array $params)
    {

        // Define the global markdown variable.  Used by the assets/js/markdown.js script
        $JS=
          "\nvar markdown = {};\n".
          "markdown.autoload=0;\n".
          "markdown.url='index.php';\n".
          "markdown.settings={};\n".
          "markdown.settings.debug=".($this->_aeSettings->getDebugMode()?1:0).";\n".
          "markdown.settings.locale='".$this->_aeSettings->getLocale()."';\n".
          "markdown.settings.use_localcache=".($this->_aeSettings->getUseLocalCache()?1:0).";\n";

          $html=file_get_contents($this->_aeSettings->getTemplateFile('timeline'));
          $html=str_replace('<!--%MARKDOWN_GLOBAL_VARIABLES%-->', '<script type="text/javascript">'.$JS.'</script>', $html);

          include_once dirname(__DIR__).'/filetype/html.php';
          $aeHTML=\AeSecure\FileType\HTML::getInstance();

          return $aeHTML->replaceVariables($html, '', $params);
    } // function run()
} // class Timeline
