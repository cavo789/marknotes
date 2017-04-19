<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class JoliTypo
{
    public static function doIt(&$html = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        if (is_dir($aeSettings->getFolderLibs()."jolicode")) {
            $locale = $aeSettings->getLocale();


            // See https://github.com/jolicode/JoliTypo#fixer-recommendations-by-locale
            switch ($locale) {
                case 'fr-FR':
                    // Those rules apply most of the recommendations of "Abrégé du code typographique à l'usage de la presse", ISBN: 9782351130667.
                    // Remove Hypen because need a library (Hyphenator) of 12MB,
                    $fixer = new \JoliTypo\Fixer(array('Ellipsis', 'Dimension', 'Numeric', 'Dash', 'SmartQuotes', 'FrenchNoBreakSpace', 'NoSpaceBeforeComma', 'CurlyQuote', 'Trademark'));
                    break;

                default:
                    // Remove Hypen because need a library (Hyphenator) of 12MB,
                    $fixer = new \JoliTypo\Fixer(array('Ellipsis', 'Dimension', 'Numeric', 'Dash', 'SmartQuotes', 'NoSpaceBeforeComma', 'CurlyQuote', 'Trademark'));
                    break;
            }

            // Set the locale (en_GB, fr_FR, ...) preferences
            $fixer->setLocale($locale);

            $html = $fixer->fix($html);

            $html = str_replace('Wallonie', 'KLJLJKJ', $html);
        }

        return true;
    }

    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('display.html', __CLASS__.'::doIt');
        return true;
    }
}
