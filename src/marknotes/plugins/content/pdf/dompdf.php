<?php

namespace MarkNotes\Plugins\Content\PDF;

defined('_MARKNOTES') or die('No direct access allowed');

class DomPDF
{
    /**
     *
     */
    public static function doIt(&$params = null)
    {
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $aeTask = \MarkNotes\Tasks\Convert::getInstance();
        $final = $aeTask->getFileName($params['filename'], $params['task']);

        // Use the pdf template and not the "html" one
        $params['task'] = 'pdf';
        $params['template'] = 'pdf';

        $aeTask = \MarkNotes\Tasks\Display::getInstance();
        $html = $aeTask->run($params);

        $dompdf = new \Dompdf\Dompdf();

        $dompdf->set_base_path(dirname($final).DS);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        unset($dompdf);

        file_put_contents(mb_convert_encoding($final, "ISO-8859-1", "UTF-8"), $output);

        $params['output'] = $final;

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('export.pdf', __CLASS__.'::doIt');
        return true;
    }
}
