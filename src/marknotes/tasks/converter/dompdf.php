<?php

namespace MarkNotes\Tasks\Converter;

defined('_MARKNOTES') or die('No direct access allowed');

class Dompdf
{
    protected static $_Instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$_Instance === null) {
            self::$_Instance = new Dompdf();
        }

        return self::$_Instance;
    }

    /**
     * Use the Dompdf library (https://github.com/dompdf/dompdf) for the conversion HTML -> PDF
     */
    public function run(array $params) : string
    {
        $aeTask = \MarkNotes\Tasks\PDF::getInstance();
        $finalPDF = $aeTask->getPDFFileName($params['filename']);

        // Use the pdf template and not the "html" one
        $params['task'] = 'pdf';
        $params['template'] = 'pdf';

        $aeTask = \MarkNotes\Tasks\Display::getInstance();
        $html = $aeTask->run($params);

        $dompdf = new \Dompdf\Dompdf();

        $dompdf->set_base_path(dirname($finalPDF).DS);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        unset($dompdf);

        file_put_contents($finalPDF, $output);

        return $finalPDF;
    }
}
