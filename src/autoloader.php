<?php
namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

class Autoloader
{

    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    public static function autoload($class)
    {

        // Only for MarkNotes classes and not third parties libraries f.i.
        if (substr($class, 0, 10)==='MarkNotes\\') {
            $parts = preg_split('#\\\#', $class);

            // on extrait le dernier element
            $className = array_pop($parts);

            // on créé le chemin vers la classe
            // on utilise DS car plus propre et meilleure portabilité entre les différents systèmes (windows/linux)

            $path = implode(DS, $parts);
            $file = $className.'.php';

            $filepath = dirname(__FILE__).DS.strtolower($path.DS.$file);

            if (!file_exists($filepath)) {
                echo '<strong>autoloader - The file '.$filepath.' is missing!</strong>';

                /*<!-- build:debug -->*/
                if (class_exists("\MarkNotes\Debug")) {
                    $aeDebug=\MarkNotes\Debug::getInstance();
                    if ($aeDebug->enable()) {
                        echo '<pre>'.print_r(debug_backtrace(3), true).'</pre>';
                    }
                }
                /*<!-- endbuild -->*/

                return false;
            }
            require $filepath;
        }
    }
}
