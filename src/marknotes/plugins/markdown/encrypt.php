<?php

/**
 * Encrypt / Decrypt part of the .md file included between <encrypt> tags.
 * Use php_openssl so that extension should be loaded in php.ini.
 *
 *    1. Check that extension=php_openssl.dll is present in your php.ini file
 *    2. Verify that the extension_dir variable is correctly initialized
 */

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Encrypt
{
    private static $method = '';
    private static $password = '';

    public static function initialize() : bool
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $arrSettings = $aeSettings->getPlugins('options', 'encrypt');

        static::$password = $arrSettings['password'] ?? '';
        static::$method = $arrSettings['method'] ?? 'aes-256-ctr';

        return true;
    }

    /**
    * Encrypt a string by using SSL
    *
    * @param  string $data     The string that should be encrypted
    * @return type             The string, encrypted.  The first characters will
    *                          contains the "Initialization vector", required for the decryption
    */
    private static function sslEncrypt(string $data = '') : string
    {
        // Dynamically generate an "IV" i.eI and initialization vector that will ensure cypher to be unique
        // (http://stackoverflow.com/questions/11821195/use-of-initialization-vector-in-openssl-encrypt)
        // And concatenate that "IV" to the encrypted texte

        $ivSize = @\mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = @\mcrypt_create_iv($ivSize, MCRYPT_RAND);

        if (function_exists('openssl_encrypt')) {
            return urlencode($iv.\openssl_encrypt(urlencode($data), self::$method, static::$password, 0, $iv));
        } else {
            return urlencode(
                exec(
                    "echo \"".urlencode($data)."\" | openssl enc -".urlencode(self::$method).
                    " -base64 -nosalt -K ".bin2hex(static::$password)." -iv ".bin2hex($iv)
                )
            );
        }
    }

    /**
    * Decrypt a SSL encrypted string
    *
    * @param  string $encrypted  The string to decrypt. The first characters contains the "Initialiation vector"
    * @return type               The string, decrypted
    */
    public static function sslDecrypt(string $encrypted = '') : string
    {
        if (trim($encrypted) === '') {
            return '';
        }

        $tmp = urldecode($encrypted);

        $ivSize = @\mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = substr($tmp, 0, $ivSize);

        if (function_exists('openssl_decrypt')) {
            return trim(
                urldecode(
                    \openssl_decrypt(
                        substr($tmp, $ivSize),
                        static::$method,
                        static::$password,
                        0,
                        $iv
                    )
                )
            );
        } else {
            return trim(
                urldecode(
                    exec(
                        "echo \"".$tmp."\" | openssl enc -".static::$method." -d -base64 -nosalt -K ".
                        bin2hex(static::$password)." -iv ".bin2hex($iv)
                    )
                )
            );
        }
    }

    private static function encrypt(string $markdown, array $matches = array()) : string
    {
        if ((trim($markdown) === '') || (count($matches) === 0)) {
            return $markdown;
        }

        $j = count($matches[0]);

        $i = 0;

        $rewriteFile = false;

        // Loop and process every <encrypt> tags
        // For instance : <encrypt data-encrypt="true">ENCRYPTED TEXT</encrypt>

        for ($i; $i < $j; $i++) {

            // Retrieve the attributes (f.i. data-encrypt="true")
            $attributes = $matches[1][$i];

            $isEncrypted = false;

            $tmp = array();
            preg_match('#data-encrypt="(.*)"#', $attributes, $tmp);

            if (count($tmp) > 0) {
                // Only when data-encrypt="true" is found, consider the content has an encrypted one.
                $isEncrypted = (strcasecmp(rtrim($tmp[1]), 'true') === 0?true:false);
            }

            // Retrieve the text (encrypted if data-encrypt was found and set on "true"; uncrypted otherwise)
            $words = $matches[2][$i];

            if (!$isEncrypted) {

                // At least one <encrypt> tag found without attribute data-encrypt="true" => the content
                // should be encrypted and the file should be override with encrypted data
                $encrypted = self::sslEncrypt($words);

                // Just to be sure that encryption is successfull
                $decrypt = self::sslDecrypt($encrypted);

                if ($words !== $decrypt) {

                    /*<!-- build:debug -->*/
                    $aeSettings = \MarkNotes\Settings::getInstance();
                    if ($aeSettings->getDebugMode()) {
                        $aeDebug = \MarkNotes\Debug::getInstance();
                        $aeDebug->log('The encryption has failed !!!', 'error');
                        $aeDebug->log('*    1. Check that extension=php_openssl.dll is present in your php.ini file', 'error');
                        $aeDebug->log('*    2. Verify that the extension_dir variable is correctly initialized', 'error');
                    }
                    /*<!-- endbuild -->*/
                } else {
                    // OK, it's fine, encryption is a success
                    $markdown = str_replace(
                        $matches[0][$i],
                        utf8_encode(
                            '<encrypt data-encrypt="true">'.
                            $encrypted.'</encrypt>'
                        ),
                        $markdown
                    );

                    $rewriteFile = true;
                }
            } // if (!$isEncrypted)
        } // for($i;$i<$j;$i++)

        if ($rewriteFile === true) {
            // Call content plugins
            $aeEvents = \MarkNotes\Events::getInstance();
            $aeEvents->loadPlugins('markdown');
            $args = array(&$markdown);
            $aeEvents->trigger('markdown.write', $args);
            $markdown = $args[0];
        }

        return $markdown;
    }

    /**
     * The markdown file has been read, this function will get the content of the .md file and
     * make some processing like data cleansing
     *
     * $params is a associative array with, as entries,
     *	* markdown : the markdown string (content of the file)
     *	* filename : the absolute filename on disk
    */
    public static function readMD(&$params = null)
    {
        if (trim($params['markdown']) === '') {
            return true;
        }
        // Check if, in the markdown string, there is encrypt tags like <encrypt>SOMETHING</encrypt>
        // i.e. unencrypted content.

        $pattern = '/<encrypt[[:blank:]]*([^>]*)>([\\S\\n\\r\\s]*?)<\/encrypt>/';
        $matches = array();
        preg_match_all($pattern, $params['markdown'], $matches);

        if (count($matches) > 0) {
            // Yes => encrypt these contents
            $params['markdown'] = self::encrypt($params['markdown'], $matches);
        }

        $aeSession = \MarkNotes\Session::getInstance();

        // The note should be unencrypted in case of editing or when the search is fired
        // edit.form : the unencrypted version should be editable
        // search : we also need to search in encrypted text
        $task = $aeSession->get('task');

        $bShowUnencrypt = in_array($task, array('edit.form','search'));

        if ($bShowUnencrypt) {
            $matches = array();

            // ([\\S\\n\\r\\s]*?)  : match any characters, included new lines
            preg_match_all('/<encrypt[[:blank:]]*[^>]*>([\\S\\n\\r\\s]*?)<\/encrypt>/', $params['markdown'], $matches);

            // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
            if (count($matches[1]) > 0) {
                $j = count($matches[0]);

                $i = 0;

                for ($i; $i < $j; $i++) {
                    $decrypt = self::sslDecrypt($matches[1][$i]);
                    $params['markdown'] = str_replace($matches[0][$i], '<encrypt>'.$decrypt.'</encrypt>', $params['markdown']);
                }
            } // if (count($matches[1])>0)
        }

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        self::initialize();

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('markdown.read', __CLASS__.'::readMD');
        return true;
    }
}
