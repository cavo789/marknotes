<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

class PDF
{

    protected static $_instance = null;

    public function __construct()
    {
        return true;
    } // function __construct()

    public static function getInstance()
    {

        if (self::$_instance === null) {
            self::$_instance = new PDF();
        }

        return self::$_instance;
    } // function getInstance()

    public function run(array $params)
    {

        // If the filename doesn't mention the file's extension, add it.
        if (substr($params['filename'], -3)!='.md') {
            $params['filename'].='.md';
        }

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $fullname=$aeFiles->replaceExtension(
            str_replace(
                '/',
                DIRECTORY_SEPARATOR,
                utf8_decode(
                    $aeSettings->getFolderDocs(true).
                    ltrim($params['filename'], DS)
                )
            ),
            'html'
        );

        if ($aeFiles->fileExists($fullname)) {
            echo str_replace(
                '%s',
                '<strong>'.$fullname.'</strong>',
                $eSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists')
            );

            return false;
        } else {
            if (is_dir($aeSettings->getFolderLibs()."dompdf")) {
                // Get the HTML rendering of the note

                $aeTask=\MarkNotes\Tasks\Display::getInstance();

                // Use the pdf template and not the "html" one
                $params['template']='pdf';
                $html= $aeTask->run($params);

                // Replace external links to f.i. the Bootstrap CDN to local files
                $matches=array();
                preg_match_all('/<link\s+(?:[^>]*?\s+)?href=(\'|")([^(\'|")]*)(\'|")/', $html, $matches);
                foreach ($matches[2] as $match) {
                    switch (basename($match)) {
                        case 'bootstrap.min.css':
                            $html=str_replace($matches[2], $aeSettings->getFolderLibs().'bootstrap'.DS.'css'.DS.'bootstrap.min.css', $html);
                            break;
                    } // switch
                } // foreach

                $dompdf = new \Dompdf\Dompdf();

                $dompdf->set_base_path(dirname($fullname).DS);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $dompdf->stream(basename($aeFiles->removeExtension($fullname)));
            } // if (file_exists($fullname))
        } // if (file_exists($fullname))

        return true;
    } // function Run()
} // class PDF
