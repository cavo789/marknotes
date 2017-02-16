<?php
/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.5
* @author    : christophe@aesecure.com
* @license   : MIT
* @url       : https://github.com/cavo789/markdown
* @package   : 2017-02-16T12:37:19.433Z
*/?>
<?php

namespace AeSecureMDTasks;

class PDF
{
    public static function Run(array $params)
    {
        
        $aeDebug=\AeSecure\Debug::getInstance();
        $aeSettings=\AeSecure\Settings::getInstance();
        
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // If the filename doesn't mention the file's extension, add it.
        if (substr($params['filename'], -3)!='.md') {
            $params['filename'].='.md';
        }
        
        $fullname=\AeSecure\Files::replaceExtension(str_replace('/', DIRECTORY_SEPARATOR, utf8_decode($aeSettings->getFolderDocs(true).
           ltrim($params['filename'], DS))), 'html');

        if (\AeSecure\Files::fileExists($fullname)) {
            $Domfolder=$aeSettings->getFolderLibs().'dompdf'.DS;
            if (\AeSecure\Files::fileExists($lib = $Domfolder.'autoload.inc.php')) {
                $html=file_get_contents($fullname);

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
                require_once($lib);
               
                header('Content-Type: application/pdf');

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
        
        die();
    } // function Run()
} // class PDF
