<?php
/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.5
* @author    : christophe@aesecure.com
* @license   : MIT
* @url       : https://github.com/cavo789/markdown
* @package   : 2017-03-21T22:24:08.019Z
*/?>
<?php
/* REQUIRES PHP 7.x AT LEAST */

namespace AeSecure;

/**
 * Encryption class.  Use SSL for the encryption.
 *
 * Partially based on @link : http://php.net/manual/fr/function.openssl-decrypt.php#111832
 */
class Encrypt
{

    private $method='';
    private $password='';
    private $iv='';

    /**
    * Initialize the class
    *
    * @param string $password The password to use for the encryption
    * @param string $method   OPTIONAL, If not mentionned, will be 'aes-256-ctr'
    */
    public function __construct(string $password, string $method)
    {

        $this->password=$password;

        if (trim($method)==null) {
            $this->method='aes-256-ctr';
        } else {
            $this->method=$method;
        }

        // Dynamically generate an "IV" i.eI and initialization vector that will ensure cypher to be unique
        // (http://stackoverflow.com/questions/11821195/use-of-initialization-vector-in-openssl-encrypt)
        // And concatenate that "IV" to the encrypted texte

        $iv_size = @\mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $this->iv= @\mcrypt_create_iv($iv_size, MCRYPT_RAND);

    } // function __construct()

    /**
    * Encrypt a string by using SSL
    *
    * @param  string $data     The string that should be encrypted
    * @param  string $password OPTIONAL, If not mentionned, use the password define during the class initialization
    * @param  string $method   OPTIONAL, If not mentionned, will be 'aes-256-ctr'
    * @return type             The string, encrypted.  The first characters will
    *                          contains the "Initialization vector", required for the decryption
    */
    public function sslEncrypt(string $data, $password) : string
    {

        if ($password===null) {
            $password=$this->password;
        }

        if (function_exists('openssl_encrypt')) {
            return urlencode($this->iv.\openssl_encrypt(urlencode($data), $this->method, $password, 0, $this->iv));
        } else {
            return urlencode(
                $this->iv.exec(
                    "echo \"".urlencode($data)."\" | openssl enc -".urlencode($this->method).
                    " -base64 -nosalt -K ".bin2hex($password)." -iv ".bin2hex($this->_iv)
                )
            );
        }
    } // function sslEncrypt()

    /**
    * Decrypt a SSL encrypted string
    *
    * @param  string $data     The string to decrypt.  The first characters contains the "Initialiation vector"
    * @param  string $password OPTIONAL, If not mentionned, use the password define during the class initialization
    * @return type          The string, decrypted
    */
    public function sslDecrypt(string $data, $password) : string
    {

        if ($password===null) {
            $password=$this->password;
        }

        $tmp=urldecode($data);

        $iv_size = @\mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = substr($tmp, 0, $iv_size);

        if (function_exists('openssl_decrypt')) {
            return trim(urldecode(\openssl_decrypt(substr($tmp, $iv_size), $this->method, $password, 0, $iv)));
        } else {
            return trim(
                urldecode(
                    exec(
                        "echo \"".$tmp."\" | openssl enc -".$this->method." -d -base64 -nosalt -K ".
                        bin2hex($password)." -iv ".bin2hex($this->iv)
                    )
                )
            );
        }
    } // function sslDecrypt()

    /**
    * This function will scan the $markdown variable and search if there are <encrypt> tags in it.
    * For un-encrypted content, the function will encrypt them and then save the new file content
    *
    * Then, when the file has been rewritten (with <encrypt data-encrypt="TRUE">), each encrypted part
    * will be un-encrypted and special tag (<i class="icon_encrypted">) will be added before and after the
    * un-encrypted content.  That new string will be sent as the result of the function.
    *
    * @param  string $filename  Absolute filename
    * @param  string $markdown  Content
    * @param  bool   $bEditMode TRUE only when to $markdown content will be displayed in the Edit form => show unencrypted information back Edit form => show unencrypted information back
    *                            Edit form => show unencrypted information back
    * @return array
    *    bool $bReturn           TRUE when the content of the .md file has been rewritten on the disk
    *                            (=> encryption saved)
    *    string $markdown        The new content; once <encrypt> content has been correctly processed.
    */
    public function HandleEncryption(string $filename, string $markdown, bool $bEditMode = false) : array
    {

        $aeSettings=\AeSecure\Settings::getInstance();

        $bReturn=false;

        // Check if there are <encrypt> tags.  If yes, check the status (encrypted or not) and retrieve its content
        $matches = array();
        // ([\\S\\n\\r\\s]*?)  : match any characters, included new lines
        preg_match_all('/<encrypt[[:blank:]]*([^>]*)>([\\S\\n\\r\\s]*?)<\/encrypt>/', $markdown, $matches);

        // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
        if (count($matches[1])>0) {
            $icon_stars='<i class="icon_encrypted fa fa-lock onlyscreen" aria-hidden="true" '.
            'data-encrypt="true" title="'.str_replace('"', '\"', $aeSettings->getText('is_encrypted', 'This information is encrypted in the original file and decoded here for screen display')).'"></i>';

            // Initialize the encryption class
            $aesEncrypt=new Encrypt($aeSettings->getEncryptionPassword(), $aeSettings->getEncryptionMethod());

            $j=count($matches[0]);

            $i=0;

            $rewriteFile=false;

            // Loop and process every <encrypt> tags
            // For instance : <encrypt data-encrypt="true">ENCRYPTED TEXT</encrypt>

            for ($i; $i<$j; $i++) {
                // Retrieve the attributes (f.i. data-encrypt="true")
                $attributes=$matches[1][$i];

                $isEncrypted=false;

                $tmp=array();
                preg_match('#data-encrypt="(.*)"#', $attributes, $tmp);

                if (count($tmp)>0) {
                    // Only when data-encrypt="true" is found, consider the content has an encrypted one.
                    $isEncrypted=(strcasecmp(rtrim($tmp[1]), 'true')===0?true:false);
                }

                // Retrieve the text (encrypted if data-encrypt was found and set on "true"; uncrypted otherwise)
                $words=$matches[2][$i];

                // If we need to crypt we a new password,
                // NULL = try to use the current password, defined in the settings.json file
                //$decrypt=$aesEncrypt->sslDecrypt($words,NULL);
                //if (!ctype_print($decrypt)) {  // ctype_print will return FALSE when the string still
                //   contains binary info => decrypt has failed
                //   $words=$aesEncrypt->sslDecrypt($words,'');
                //   $isEncrypted=FALSE;
                //}

                if (!$isEncrypted) {
                    // At least one <encrypt> tag found without attribute data-encrypt="true" => the content
                    // should be encrypted and the file should be override with encrypted data

                    $encrypted=$aesEncrypt->sslEncrypt($words, null);

                    $markdown=str_replace(
                        $matches[0][$i],
                        utf8_encode(
                            '<encrypt data-encrypt="true">'.
                            $encrypted.'</encrypt>'
                        ),
                        $markdown
                    );

                    $rewriteFile=true;
                } // if (!$isEncrypted)
            } // for($i;$i<$j;$i++)

            if ($rewriteFile===true) {
                $bReturn=\AeSecure\Files::rewriteFile($filename, $markdown);
            }

            // --------------------------------------------------------------------------------------------
            //
            // Add a three-stars icon (only for the display) to inform the user about the encrypted feature

            $matches = array();
            // ([\\S\\n\\r\\s]*?)  : match any characters, included new lines
            preg_match_all('/<encrypt[[:blank:]]*[^>]*>([\\S\\n\\r\\s]*?)<\/encrypt>/', $markdown, $matches);

            // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
            if (count($matches[1])>0) {
                $j=count($matches[0]);

                $i=0;

                for ($i; $i<$j; $i++) {
                    // Great, the info is already encrypted

                    //$icons='<i id="icon_lock" class="fa fa-lock" aria-hidden="true"></i>';

                    $decrypt=$aesEncrypt->sslDecrypt($matches[1][$i], null);

                    if ($bEditMode===true) {
                        // Replace the <encrypt data-encrypt="TRUE">ENCRYPTED DATA</encrypt> by
                        // <encrypt>UNENCRYPTED DATA</encrypt>.
                        //
                        // Needed by the Edit form, to be able to display unencrypted note
                        $markdown=str_replace($matches[0][$i], '<encrypt>'.$decrypt.'</encrypt>', $markdown);
                    } else { // if($bEditMode===TRUE)

                        // This isn't the edit mode : show the lock icon ($icon_stars)
                        $markdown=str_replace($matches[1][$i], $icon_stars.$decrypt.$icon_stars, $markdown);
                    } // if($bEditMode===TRUE)
                } // for($i;$i<$j;$i++)
            } // if (count($matches[1])>0)

            // Release

            unset($aesEncrypt);
        } // if (count($matches[1])>0)

        return array($bReturn, $markdown);
    } // function HandleEncryption()
} // class Encrypt
