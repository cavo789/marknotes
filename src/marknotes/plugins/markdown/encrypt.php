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

class Encrypt extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.encrypt';
	protected static $json_options = JSON_OPTIONS_ENCRYPT;

	private static $method = 'aes-256-ctr';
	private static $password = '';
	private static $Initialized = 0;

	private static $regex=
		'/<encrypt[[:blank:]]*'.
		// match the presence of attributes like data-encrypt="true" f.i.
		'([^>]*)'.
		'>'.
		// ([\\S\\n\\r\\s]*?) : match any characters, included new lines
		'([\\S\\n\\r\\s]*?)'.
		'<\/encrypt>/';
	/**
	 * Verify if the plugin is well needed and thus have a reason
	 * to be fired
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			$password = self::getOptions('password', '');

			if ($password !== '') {
				$bCanRun = true;
				// Run the initialization code only once
				if (static::$Initialized===0) {
					self::initEncryption();
				}
			}
		}

		return $bCanRun;
	}

	/**
	 * For optimization purposes, read the settings.json only once and
	 * initialize variables
	 */
	private static function initEncryption() : bool
	{
		if (static::$Initialized===0) {
			$aeSettings = \MarkNotes\Settings::getInstance();
			$arrOptions = $aeSettings->getPlugins(JSON_OPTIONS_ENCRYPT);

			static::$password = $arrOptions['password'] ?? '';

			if (isset($arrOptions['method'])) {
				if (trim($arrOptions['method'])!=='') {
					static::$method = $arrOptions['method'];
				}
			}

			// Run the initialization code only once
			static::$Initialized=1;
		}
		return true;
	}

	/**
	* Encrypt a string by using SSL
	*
	* @param  string $data  The string that should be encrypted
	* @return type          The string, encrypted.
	*						The first characters will contains the
	*                       "Initialization vector", required for the decryption
	* @link http://stackoverflow.com/questions/11821195/use-of-initialization-vector-in-openssl-encrypt)
	*/
	public static function sslEncrypt(string $data = '') : string
	{
		// Dynamically generate an "IV" i.eI and initialization vector
		// that will ensure cypher to be unique
		//
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
	* @param  string $encrypted  The string to decrypt.
	*							 The first characters contains the
	*							 "Initialiation vector"
	* @return type               The string, decrypted
	*/
	public static function sslDecrypt(string $encrypted = '') : string
	{
		if (trim($encrypted) === '') {
			return '';
		}

		$aeSession = \MarkNotes\Session::getInstance();
		$authenticated = boolval($aeSession->get('authenticated', 0));

		if ($authenticated) {
			// Decrypted only if the user is connected
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
		} else { // if ($authenticated) {
			$aeSettings = \MarkNotes\Settings::getInstance();
			$text = rtrim($aeSettings->getText('encrypted', 'encrypted data'), ' .');
			return '**'.$text.'**';
		}
	}

	/**
	 * Encrypt patterns i.e. capture every <encrypt>SECRETS</encrypt> and
	 * convert into <encrypt data-encrypt="true">xxxxxx</encrypt> and, also,
	 * rewrite the file on the disk.
	 */
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
				// Only when data-encrypt="true" is found,
				// consider the content has an encrypted one.
				$isEncrypted = (strcasecmp(rtrim($tmp[1]), 'true') === 0?true:false);
			}

			// Retrieve the text (encrypted if data-encrypt was found and set on "true"; uncrypted otherwise)
			$words = $matches[2][$i];

			if (!$isEncrypted) {
				// At least one <encrypt> tag found without attribute
				// data-encrypt="true" => the content
				// should be encrypted and the file should be override
				// with encrypted data
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
			// Rewrite the file on the disk
			$aeEvents = \MarkNotes\Events::getInstance();
			$aeEvents->loadPlugins('task.markdown.write');
			$params = array('markdown'=>$markdown);
			$args = array(&$params);
			$aeEvents->trigger('task.markdown.write::run', $args);
		}

		return $markdown;
	}

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		// Check if, in the markdown string, there is encrypt tags
		// like <encrypt>SOMETHING</encrypt> i.e. unencrypted content.
		if (preg_match_all(static::$regex, $params['markdown'], $matches)) {
			// Yes => encrypt these contents
			$params['markdown'] = self::encrypt($params['markdown'], $matches);
		}

		$aeSession = \MarkNotes\Session::getInstance();

		// The note should be unencrypted in case of editing or when the search is fired
		// edit.form : the unencrypted version should be editable
		// search : we also need to search in encrypted text
		$task = $aeSession->get('task');

		// In some cases, the encrypted info shouldn't  stay encrypted.
		// In the edit.form f.i. or when the search task is running.
		//
		// To indicate that encryption should be removed, the calling code
		// should initialize $params['encryption']=0
		$bKeepEncryption = boolval($params['encryption'] ?? 1);

		if (!$bKeepEncryption) {
			// Find encrypted portions
			if (preg_match_all(static::$regex, $params['markdown'], $matches)) {
				// Get the encrypted info
				list($pattern, $attributes, $encrypted_portion) = $matches;

				$j = count($matches[0]);

				$i = 0;

				for ($i; $i < $j; $i++) {
					$decrypt = self::sslDecrypt($encrypted_portion[$i]);

					// Editing form : add the encrypt tag
					// Don't add the tag when, f.i., the task is
					// txt since it isn't needed
					// to export the tag for a TXT file
					if ($task=='task.edit.form') {
						$decrypt='<encrypt>'.$decrypt.'</encrypt>';
					}

					$params['markdown'] = str_replace($pattern, $decrypt, $params['markdown']);
				}
			} // if (count($matches[1])>0)
		} else {
			if (preg_match_all(static::$regex, $params['markdown'], $matches)) {
				// Get the encrypted info
				list($pattern, $attributes, $encrypted_portion) = $matches;

				$j = count($matches[0]);
				for ($i=0; $i < $j; $i++) {
					$decrypt = self::sslDecrypt($encrypted_portion[$i]);
					$decrypt = ENCRYPT_MARKDOWN_TAG.$decrypt.ENCRYPT_MARKDOWN_TAG;
					$params['markdown'] = str_replace($pattern, $decrypt, $params['markdown']);
				}
			}
		}

		return true;
	}
}
