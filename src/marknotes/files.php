<?php
/* REQUIRES PHP 7.x AT LEAST */

namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

class Files
{
    protected static $_Instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$_Instance === null) {
            self::$_Instance = new Files();
        }
        return self::$_Instance;
    }

    /**
    * Check if a file exists and return FALSE if not.  Disable temporarily errors to avoid warnings f.i. when the file
    * isn't reachable due to open_basedir restrictions
    *
    * @param  type $filename
    * @return boolean
    */
    public static function fileExists(string $filename) : bool
    {
        if ($filename == '') {
            return false;
        }

        $errorlevel = error_reporting();
        error_reporting(0);

        $wReturn = is_file(utf8_decode($filename));

        error_reporting($errorlevel);

        return $wReturn;
    }

    /**
    * Check if a file exists and return FALSE if not.  Disable temporarily errors to avoid warnings f.i. when the file
    * isn't reachable due to open_basedir restrictions
    *
    * @param  type $filename
    * @return boolean
    */
    public static function folderExists(string $folderName) : bool
    {
        if ($folderName == '') {
            return false;
        }

        $errorlevel = error_reporting();
        error_reporting($errorlevel & ~E_NOTICE & ~E_WARNING);

        $wReturn = is_dir($folderName);

        error_reporting($errorlevel);

        return $wReturn;
    }

    /**
     * Write a content into a UTF8-BOM file
     */
    public static function fwriteUTF8BOM(string $sFileName, string $sContent)
    {
        $f = fopen($sFileName, "wb");
        fputs($f, "\xEF\xBB\xBF".$sContent);
        fclose($f);
    }

    /**
     * Under Windows, create a text file with the support of UTF8 in his content.
     * Without the 'chcp 65001' command, accentuated characters won't be correctly understand if
     * the file should be executable (like a .bat file)
     *
     * see https://superuser.com/questions/269818/change-default-code-page-of-windows-console-to-utf-8
     */
    public static function fwriteANSI(string $sFileName, string $sContent)
    {
        file_put_contents($sFileName, 'chcp 65001'.PHP_EOL.utf8_encode($sContent));
        return true;
    }

    /**
    * Recursive glob : retrieve all files that are under $path (if empty, $path is the root folder of the website)
    *
    * For instance : aeSecureFct::rglob($pattern='.htaccess',$path=$rootFolder); to find every .htaccess files on
    * the server
    * If folders should be skipped :
    *    aeSecureFct::rglob('.htaccess',$rootFolder,0,array('aesecure','administrator'))
    *
    * @param  type $pattern
    * @param  type $path
    * @param  type $flags
    * @param  type $arrSkipFolder Folders to skip... (subfolders will be also skipped)
    * @return type
    */
    public static function rglob(string $pattern = '*', string $path = '', int $flags = 0, $arrSkipFolder = null) : array
    {
        static $adjustCase = false;

        // glob() is case sensitive so, search for PHP isn't searching for php.
        // Here, the pattern will be changed to be case insensitive.
        // "*.php" will be changed to "*.[pP][hH][pP]"

        if (($pattern != '') && ($adjustCase == false)) {
            $length = strlen($pattern);
            $tmp = $pattern;
            $pattern = '';
            for ($i = 0; $i < $length; $i++) {
                $pattern .= (ctype_alpha($tmp[$i]) ? '['.strtolower($tmp[$i]).strtoupper($tmp[$i]).']' : $tmp[$i]);
            }
            // Do this only once
            $adjustCase = true;
        }

        // If the "$path" is one of the folder to skip, ... skip it.

        if (($arrSkipFolder != null) && (count($arrSkipFolder) > 0)) {
            foreach ($arrSkipFolder as $folder) {
                if (self::startsWith($path, $folder)) {
                    return null;
                }
            } // foreach
        } // if (($arrSkipFolder!=null) && (count($arrSkipFolder)>0))

        $paths = glob($path.'*', GLOB_MARK | GLOB_ONLYDIR);
        $files = glob(rtrim($path, DS).DS.$pattern, $flags);

        foreach ($paths as $path) {
            if (self::folderExists($path)) {
                // Avoid recursive loop when the folder is a symbolic link
                if (rtrim(str_replace('/', DS, $path), DS) == realpath($path)) {
                    $arr = self::rglob($pattern, $path, $flags, $arrSkipFolder);
                    if (($arr != null) && (count($arr) > 0)) {
                        $files = array_merge($files, $arr);
                    }
                } else {
                    // $path is a symbolic link.  Doing a glob on a symbolic link will create a recursive
                    // call and will crash the script
                }
            } // if(!(is_link($path))) {
        } // foreach

        // Don't use the / notation but well the correct directory separator
        foreach ($files as $key => $value) {
            $files[$key] = $value;
        }

        @sort($files);

        return $files;
    }

    /**
    * Replace file's extension
     *
    * @param  string $filename      The filename ("test.md")
    * @param  string $new_extension The new extension ("html")
    * @return string                The new filename (test.html)
    */
    public static function replaceExtension(string $filename, string $new_extension) : string
    {
        $info = pathinfo($filename);

        $sResult = $info['filename'].'.'.$new_extension;

        // Append the folder name when $filename wasn't just a file without path
        if ($filename !== basename($filename)) {
            $sResult = $info['dirname'].DS.$sResult;
        }

        return $sResult;
    }

        /**
        * Remove file's extension
         *
        * @param  string $filename The filename ("test.md")
        * @return string                The new filename (test)
        */
    public static function removeExtension(string $filename) : string
    {
        $info = pathinfo($filename);
        return $info['dirname'].DS.$info['filename'];
    }

            /**
            * Get file's extension
             *
            * @param  string $filename The filename ("test.md")
            * @return string                The new filename (test)
            */
    public static function getExtension(string $filename) : string
    {
        $info = pathinfo($filename);
        $sResult = isset($info['extension']) ? $info['extension'] : '';
        return $sResult;
    }

    /**
    * Be sure that the filename isn't something like f.i. ../../../../dangerous.file
    * Remove dangerouse characters and remove ../
    *
    * @param  string $filename
    * @return string
    *
    * @link http://stackoverflow.com/a/2021729/1065340
    */
    public static function sanitizeFileName(string $filename) : string
    {

        // Remove anything which isn't a word, whitespace, number
        // or any of the following caracters -_~,;[]().
        // If you don't need to handle multi-byte characters
        // you can use preg_replace rather than mb_ereg_replace
        // Thanks @Łukasz Rysiak!

        // Remove any trailing dots, as those aren't ever valid file names.
        $filename = rtrim($filename, '.');

        // Pattern with allowed characters  PROBLEM : accentuated characters or special one (like @)
        // should also be allowed
        // $regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\\\/\_\- ]#', '#^\.#');
        // $filename=trim(preg_replace($regex, '', $filename));

        // If $filename was f.i. '../../../../../'.$filename
        // the preg_replace has change it to '/////'.$filename so remove leading /
        // Remove directory separator for Unix and Windows

        $filename = ltrim($filename, '\\\/');

        return $filename;
    }

    /**
    * Rewrite an existing file.  The function will first take a backup of the file (with new .old suffix).
    * If the write action is successfull, the .old file is removed
    *
    * @param  string $filename    Absolute filename
    * @param  string $new_content The new content
    * @return bool                return False in case of error
    */
    public static function rewriteFile(string $filename, string $new_content) : bool
    {
        $bReturn = false;

        if (file_exists($filename)) {
            rename($filename, $filename.'.old');

            try {
                if ($handle = fopen($filename, 'w')) {
                    fwrite($handle, $new_content);
                    fclose($handle);

                    if (filesize($filename) > 0) {
                        unlink($filename.'.old');
                        $bReturn = true;
                    }
                }
            } catch (Exception $ex) {
            }
        } // if (file_exists($filename))

        return $bReturn;
    }

    public static function createFile(string $filename, string $content, int $chmod = 644) : bool
    {

        $errorlevel = error_reporting();
        error_reporting($errorlevel & ~E_NOTICE & ~E_WARNING);

        $bReturn = false;
        try {
            if ($handle = fopen($filename, 'w')) {
                fwrite($handle, $content);
                fclose($handle);

                if (filesize($filename) > 0) {
                    chmod($filename, $chmod);
                    $bReturn = true;
                }
            }
        } catch (Exception $ex) {
            /*<!-- build:debug -->*/
            if ($aeSettings->getDebugMode()) {
                echo __FILE__."-".__LINE__." - <hr/>";
                echo $ex->getMessage();
            }
            /*<!-- endbuild -->*/
        }
        error_reporting($errorlevel);

        return $bReturn;
    }
}
