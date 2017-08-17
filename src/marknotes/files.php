<?php
/* REQUIRES PHP 7.x AT LEAST */

namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

class Files
{
    protected static $hInstance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$hInstance === null) {
            self::$hInstance = new Files();
        }
        return self::$hInstance;
    }

    /**
     * Rename will first remove the existing "new" file if the file already exists
     */
    public function renameFile(string $oldname, string $newname) : bool
    {
        if ((self::fileExists($oldname)) && ($oldname !== $newname)) {

            // Remove the old version if already there
            if (self::fileExists($newname)) {
                unlink($newname);
            }

            // And rename the temporary PDF to its final name
            rename(mb_convert_encoding($oldname, "ISO-8859-1", "UTF-8"), mb_convert_encoding($newname, "ISO-8859-1", "UTF-8"));
        }

        return self::fileExists($newname);
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

        $wReturn = is_file(mb_convert_encoding($filename, "ISO-8859-1", "UTF-8"));

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

        // mb_convert_encoding to support accentuated characters in name
        $wReturn = is_dir(mb_convert_encoding($folderName, "ISO-8859-1", "UTF-8"));

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
     */
    public static function fwriteANSI(string $sFileName, string $sContent)
    {
        file_put_contents($sFileName, utf8_encode($sContent));
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

        // Sort, case insensitive
        usort($paths, 'strnatcasecmp');

        $files = glob(rtrim($path, DS).DS.$pattern, $flags);
        // Sort, case insensitive
        usort($files, 'strnatcasecmp');

        foreach ($paths as $path) {
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
        } // foreach

        // Don't use the / notation but well the correct directory separator
        foreach ($files as $key => $value) {
            $files[$key] = $value;
        }

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

        $sResult = self::removeExtension($filename).'.'.$new_extension;

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
        // Correctly handle double extension like docs\development\marknotes.reveal.pdf
        $arr = explode('.', $filename);

        $extension = '';
        if (count($arr) > 0) {
            unset($arr[0]);
            $extension = implode($arr, '.');
        }

        return str_replace('.'.$extension, '', $filename);
    }

    /**
    * Get file's extension
     *
    * @param  string $filename The filename ("test.md")
    * @return string                The new filename (test)
    */
    public static function getExtension(string $filename) : string
    {
        $filename = basename($filename);

        // Correctly handle double extension like docs\development\marknotes.reveal.pdf
        $arr = explode('.', $filename);

        $extension = '';
        if (count($arr) > 0) {
            unset($arr[0]);
            $sResult = implode($arr, '.');
        }

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

        // Replace characters not in the list below by a dash (-)
        // For instance : single quote, double-quote, parenthesis, ...
        $regex = array('#[^: A-Za-z0-9àèìòùÀÈÌÒÙáéíóúýÁÉÍÓÚÝâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÿÄËÏÖÜŸçÇ\.\\\/\_\- ]#');
        $filename = trim(preg_replace($regex, '-', $filename));

        // Don't allow a double .. in the name and don't allow to start with a dot
        $regex = array('#(\.){2,}#', '#^\.#');
        $filename = trim(preg_replace($regex, '', $filename));

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

        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $filename = str_replace('/', DS, $filename);

        /*if (mb_detect_encoding($filename)) {
            if (!file_exists($filename)) {
                $filename = utf8_decode($filename);
            }
        }*/

        /*<!-- build:debug -->*/
        if ($aeSettings->getDebugMode()) {
            $aeDebug->log('Rewriting file ['.$filename.']', 'debug');
        }
        /*<!-- endbuild -->*/

        if (self::fileExists($filename)) {
            $filename = mb_convert_encoding($filename, "ISO-8859-1", "UTF-8");

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

                /*<!-- build:debug -->*/
                if ($aeSettings->getDebugMode()) {
                    $aeDebug->log($e->getMessage(), 'error');
                }
                /*<!-- endbuild -->*/
            }
        } else { // if (file_exists($filename))

            /*<!-- build:debug -->*/
            if ($aeSettings->getDebugMode()) {
                $aeDebug->log('Oups, file ['.$filename.'] not found', 'error');
            }
            /*<!-- endbuild -->*/
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
                echo $ex->getMessage();
                $aeDebug = \MarkNotes\Debug::getInstance();
                $aeDebug->here("", 99);
            }
            /*<!-- endbuild -->*/
        }
        error_reporting($errorlevel);

        return $bReturn;
    }
}
