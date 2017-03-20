<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace AeSecure;

class Functions
{

    /**
    * Return the current URL
    *
    * @param  type $useURI        If true, use $_SERVER['REQUEST_URI'] otherwise use $_SERVER[PHP_SELF]
    *                             (can be /site/router.php and not http://localhost/site/folder/subfolder/file.html in case of URLs rewriting)
    * @param  type $useScriptName  If false, only return the URL and folders name but no script name (f.i. remove index.php and parameters if any)
    *                              script name (f.i. remove index.php and parameters if any)
    * @return type string
    */
    public static function getCurrentURL(bool $useSELF = true, bool $useURI = false) : string
    {
        $ssl      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on');
        $sp       = strtolower($_SERVER['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl)?'s':'');
        $port     = $_SERVER['SERVER_PORT'];
        $port     = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
        $host     =
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') .
            (($useSELF && isset($_SERVER['PHP_SELF'])) ? dirname(dirname($_SERVER['PHP_SELF'])) : '');

        $host     = isset($host) ? rtrim(str_replace(DIRECTORY_SEPARATOR, '/',$host),'/') : $_SERVER['SERVER_NAME'].$port;

        return $protocol.'://'.$host.($useURI ? dirname($_SERVER['REQUEST_URI']) : dirname($_SERVER['PHP_SELF'])).'/';
    } // function getCurrentURL

    /**
    * Safely read posted variables
    *
    * @param  type $name    f.i. "password"
    * @param  type $type    f.i. "string"
    * @param  type $default f.i. "default"
    * @return type
    */
    public static function getParam(
        string $name,
        string $type = 'string',
        $default = '',
        bool $base64 = false,
        int $maxsize = 0
    ) {

        $tmp='';
        $return=$default;

        if (isset($_POST[$name])) {
            if (in_array($type, array('int','integer'))) {
                $return=filter_input(INPUT_POST, $name, FILTER_SANITIZE_NUMBER_INT);
            } elseif ($type==='boolean') {
                // false = 5 characters
                $tmp=substr(filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING), 0, 5);
                $return=(in_array(strtolower($tmp), array('on','true')))?true:false;
            } elseif ($type==='string') {
                $return=filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING);
                if ($base64===true) {
                    $return=base64_decode($return);
                }
                if ($maxsize>0) {
                    $return=substr($return, 0, $maxsize);
                }
            } elseif ($type==='unsafe') {
                $return=$_POST[$name];
            }
        } else { // if (isset($_POST[$name]))

            if (isset($_GET[$name])) {
                if (in_array($type, array('int','integer'))) {
                    $return=filter_input(INPUT_GET, $name, FILTER_SANITIZE_NUMBER_INT);
                } elseif ($type=='boolean') {
                    // false = 5 characters
                    $tmp=substr(filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING), 0, 5);
                    $return=(in_array(strtolower($tmp), array('on','true')))?true:false;
                } elseif ($type==='string') {
                    $return=filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING);
                    if ($base64===true) {
                        $return=base64_decode($return);
                    }
                    if ($maxsize>0) {
                        $return=substr($return, 0, $maxsize);
                    }
                } elseif ($type==='unsafe') {
                    $return=$_GET[$name];
                }
            } // if (isset($_GET[$name]))
        } // if (isset($_POST[$name]))

        if ($type=='boolean') {
            $return=(in_array($return, array('on','1'))?true:false);
        }

        return $return;
    } // function getParam()

    /**
    * Generic function for adding a js in the HTML response
     *
    * @param  type $localfile
    * @param  type $weblocation
    * @return string
    */
    public static function addJavascript(string $localfile, string $weblocation = '', bool $defer = false) : string
    {

        $return='';

        // Perhaps the script (aesecure_quickscan.php) is a symbolic link so __DIR__ is the folder where the
        // real file can be found and SCRIPT_FILENAME his link, the line below should therefore not be used anymore

        if (is_file(dirname($_SERVER['SCRIPT_FILENAME']).'/'.$localfile)) {
            $return='<script '.($defer==true?'defer="defer" ':'').'type="text/javascript" src="'.$localfile.'">'.
               '</script>';
        } else {
            if ($weblocation!='') {
                $return='<script '.($defer==true?'defer="defer" ':'').'type="text/javascript" src="'.$weblocation.'">'.
                   '</script>';
            }
        }

        return $return;
    } // function addJavascript()

    /**
    * Generic function for adding a css in the HTML response
     *
    * @param  type $localfile
    * @param  type $weblocation
    * @return string
    */
    public static function addStylesheet(string $localfile, string $weblocation = '') : string
    {

        $return='';

        // Perhaps the script (aesecure_quickscan.php) is a symbolic link so __DIR__ is the folder where the
        // real file can be found and SCRIPT_FILENAME his link, the line below should therefore not be used anymore

        if (is_file(dirname($_SERVER['SCRIPT_FILENAME']).'/'.$localfile)) {
            $return='<link href="'.$localfile.'" rel="stylesheet" />';
        } else {
            if ($weblocation!='') {
                $return='<link href="'.$weblocation.'" rel="stylesheet" />';
            }
        }

        return $return;
    } // function addStylesheet()

    /**
    * Wrapper for array_unique but for insensitive comparaison  (Images or images should be considered as one value)
     *
    * @link   http://stackoverflow.com/a/2276400
    * @param  array $array
    * @return array
    */
    public static function array_iunique(array $array) : array
    {
        return array_intersect_key($array, array_unique(array_map("StrToLower", $array)));
    }

    /**
    * Return true when the call to the php script has been done through an ajax request
     *
    * @return type
    */
    public static function isAjaxRequest()
    {
        $bAjax=(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
        return $bAjax;
    } // function isAjaxRequest()
} // class Functions
