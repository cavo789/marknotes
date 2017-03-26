<?php

namespace AeSecureMDTasks;

class PDF
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

        $fullname=\AeSecure\Files::replaceExtension(
            str_replace(
                '/',
                DIRECTORY_SEPARATOR,
                utf8_decode(
                    $this->_aeSettings->getFolderDocs(true).
                    ltrim($params['filename'], DS)
                )
            ),
            'html'
        );

        if (\AeSecure\Files::fileExists($fullname)) {
            echo str_replace(
                '%s',
                '<strong>'.$fullname.'</strong>',
                $this->_aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists')
            );

            return false;
        } else {
            if (is_dir($this->_aeSettings->getFolderLibs()."dompdf")) {
                // Get the HTML rendering of the note

                include_once TASKS.'display.php';
                $aeTask=\AeSecureMDTasks\Display::getInstance();

                // Use the pdf template and not the "html" one
                $params['template']='pdf';
                $html= $aeTask->run($params);

                // Replace external links to f.i. the Bootstrap CDN to local files
                $matches=array();
                preg_match_all('/<link\s+(?:[^>]*?\s+)?href=(\'|")([^(\'|")]*)(\'|")/', $html, $matches);
                foreach ($matches[2] as $match) {
                    switch (basename($match)) {
                        case 'bootstrap.min.css':
                            $html=str_replace($matches[2], $this->_aeSettings->getFolderLibs().'bootstrap'.DS.'css'.DS.'bootstrap.min.css', $html);
                            break;
                    } // switch
                } // foreach


                $dompdf = new \Dompdf\Dompdf();

                $dompdf->set_base_path(dirname($fullname).DS);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $dompdf->stream(basename(\AeSecure\Files::removeExtension($fullname)));
            } // if (file_exists($fullname))
        } // if (file_exists($fullname))

        return true;
    } // function Run()
} // class PDF
