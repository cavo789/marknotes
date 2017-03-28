<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace AeSecure\Helpers;

include 'libs/autoload.php';

class Convert
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
            self::$_instance = new Convert();
        }

        return self::$_instance;
    } // function getInstance()

    /**
     * If the JoliTypo settings is enabled in the settings.json file, use that library to solve somes typography issues
     * return html string
     */
    private function useJoliTypo(string $html) : string
    {

        // Can we solve somes common typo issues ?
        if ($this->_aeSettings->getUseJoliTypo()) {
            if (is_dir($this->_aeSettings->getFolderLibs()."jolicode")) {
                $locale=$this->_aeSettings->getLocale();


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
        } // if($this->_aeSettings->getUseJolyTypo())

        return $html;
    } // function useJoliTypo()

    /**
     *  Convert the Markdown string into a HTML one
     */
    public function getHTML(string $markdown, array $params = null) : string
    {

        // Call the Markdown parser (https://github.com/erusev/parsedown)
        $lib=$this->_aeSettings->getFolderLibs()."parsedown/Parsedown.php";
        if (!file_exists($lib)) {
            self::ShowError(
                str_replace(
                    '%s',
                    '<strong>'.$lib.'</strong>',
                    $this->_aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists')
                ),
                true
            );
        }

        include_once $lib;
        $parsedown=new \Parsedown();
        $html=$parsedown->text($markdown);

        // Solve typo issues
        $html=$this->useJoliTypo($html);

        // LazyLoad images ?
        if ($this->_aeSettings->getOptimisationLazyLoad()) {
            $root=rtrim(\AeSecure\Functions::getCurrentURL(true, false), '/');

            $html=str_replace(
                '<img src="',
                '<img src="'.$root.'/assets/blank.png" class="lazyload" data-src="'.$root.'/',
                $html
            );
        }

        return $html;
    } // function getHTML()
} // class Functions
