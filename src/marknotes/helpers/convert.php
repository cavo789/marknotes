<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace MarkNotes\Helpers;

defined('_MARKNOTES') or die('No direct access allowed');

include 'libs/autoload.php';

class Convert
{
    protected static $_instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {

        if (self::$_instance === null) {
            self::$_instance = new Convert();
        }

        return self::$_instance;
    }

    /**
     * If the JoliTypo settings is enabled in the settings.json file, use that library to solve somes typography issues
     * return html string
     */
    private function useJoliTypo(string $html) : string
    {

        $aeSettings=\MarkNotes\Settings::getInstance();

        // Can we solve somes common typo issues ?
        if ($aeSettings->getUseJoliTypo()) {
            if (is_dir($aeSettings->getFolderLibs()."jolicode")) {
                $locale=$aeSettings->getLocale();


                // See https://github.com/jolicode/JoliTypo#fixer-recommendations-by-locale
                switch ($locale) {
                    case 'fr-FR':
                        // Those rules apply most of the recommendations of "Abrégé du code typographique à l'usage de la presse", ISBN: 9782351130667.
                        // Remove Hypen because need a library (Hyphenator) of 12MB,
                        $fixer=new \JoliTypo\Fixer(array('Ellipsis', 'Dimension', 'Numeric', 'Dash', 'SmartQuotes', 'FrenchNoBreakSpace', 'NoSpaceBeforeComma', 'CurlyQuote', 'Trademark'));
                        break;

                    default:
                        // Remove Hypen because need a library (Hyphenator) of 12MB,
                        $fixer = new \JoliTypo\Fixer(array('Ellipsis', 'Dimension', 'Numeric', 'Dash', 'SmartQuotes', 'NoSpaceBeforeComma', 'CurlyQuote', 'Trademark'));
                        break;
                }

                // Set the locale (en_GB, fr_FR, ...) preferences
                $fixer->setLocale($locale);

                $html=$fixer->fix($html);
            }
        }

        return $html;
    }

    /**
     *  Convert the Markdown string into a HTML one
     */
    public function getHTML(string $markdown, array $params = null) : string
    {

        $aeFunctions=\MarkNotes\Functions::getInstance();
        $aeSettings=\MarkNotes\Settings::getInstance();

        // Call the Markdown parser (https://github.com/erusev/parsedown)
        $lib=$aeSettings->getFolderLibs()."parsedown/Parsedown.php";
        if (!file_exists($lib)) {
            self::ShowError(
                str_replace(
                    '%s',
                    '<strong>'.$lib.'</strong>',
                    $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists')
                ),
                true
            );
        }

        // When the task isn't slideshow, the --- (or -----) shouldn't be considered as an horizontal break (<hr>).
        // --- (or ------) is used in slideshows to create a new slide so, before converting the markdown note in html
        // thanks for Parsedown, remove them from the source

        if (isset($params['task'])) {
            if ($params['task']!=='slideshow') {
                // Except when outputting as a slideshow, remove the --- (or -----) if these characters are preceded and
                // followed by an empty line and --- (or -----) are the only characters on the line
                // (==> so it's a "section break")
                $markdown=preg_replace('/(\n^-{3,5}$\n\n)/m', '', $markdown);
            }
        }

        include_once $lib;
        $parsedown=new \Parsedown();
        $html=$parsedown->text($markdown);

        // Solve typo issues
        $html=$this->useJoliTypo($html);

        // LazyLoad images ?
        if ($aeSettings->getOptimisationLazyLoad()) {
            $root=rtrim($aeFunctions->getCurrentURL(true, false), '/');

            $html=str_replace(
                '<img src="',
                '<img src="'.$root.'/assets/blank.png" class="lazyload" data-src="'.$root.'/',
                $html
            );
        }

        return $html;
    }
}
