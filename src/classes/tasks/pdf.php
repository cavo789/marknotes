<?php

namespace AeSecureMDTasks;

class PDF
{
    public static function run(array $params)
    {

        if (!class_exists('Debug')) {
            include_once dirname(dirname(__FILE__)).'/debug.php';
        }

        $aeDebug=\AeSecure\Debug::getInstance();
        $aeSettings=\AeSecure\Settings::getInstance();

        // If the filename doesn't mention the file's extension, add it.
        if (substr($params['filename'], -3)!='.md') {
            $params['filename'].='.md';
        }

        $fullname=\AeSecure\Files::replaceExtension(
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

        if (\AeSecure\Files::fileExists($fullname)) {

            /*<!-- build:debug -->*/
            if ($aeSettings->getDebugMode()) {
                echo __FILE__.' - '.__LINE__;
            }
            /*<!-- endbuild -->*/

            echo str_replace(
                '%s',
                '<strong>'.$fullname.'</strong>',
                $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists')
            );

            return false;

        } else {

            $Domfolder=$aeSettings->getFolderLibs().'dompdf'.DS;
            if (\AeSecure\Files::fileExists($lib = $Domfolder.'autoload.inc.php')) {


                // Get the HTML rendering of the note
                include_once TASKS.'display.php';
                $html=\AeSecureMDTasks\Display::run($params);

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

                // include autoloader
                include_once $lib;

                header('Content-Type: application/pdf');
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");

                $dompdf = new \Dompdf\Dompdf();

                $dompdf->set_base_path(dirname($fullname).DS);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $dompdf->stream(basename(\AeSecure\Files::removeExtension($fullname)));
            } else { // if (file_exists($fullname))

                header('Content-Type: text/plain; charset=utf-8');
                die('The Dompdf library isn\'t installed'.($params['debug']?', file '.$lib.' is missing':''));
            } // if (file_exists($fullname))

        } // if (file_exists($fullname))

        return true;

    } // function Run()
} // class PDF
