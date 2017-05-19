<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

include 'libs/autoload.php';

class JoliTypo
{
    public static function doIt(&$content = null) : bool
    {
        if (trim($content) === '') {
            return true;
        }

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

            $content = $fixer->fix($content);
        }

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind() : bool
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.content', __CLASS__.'::doIt');
        return true;
    }
}
