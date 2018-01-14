<?php
/**
 * Encrypt / Decrypt part of the .md file included between
 * <encrypt> tags.
 * Use php_openssl so that extension should be loaded in php.ini.
 *
 *	1. Check that extension=php_openssl.dll is present in your
 * php.ini file
 *	2. Verify that the extension_dir variable is correctly
 * initialized
 *
 * @link https://github.com/starekrow/lockbox
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

			$aeFolders = \MarkNotes\Folders::getInstance();
			$lib = __DIR__.'/encrypt/libs/lockbox/';

			if ($aeFolders->exists($lib)) {
				// Include Lockbox
				/*require_once $lib."CryptoCore.php";
				require_once $lib."CryptoCoreLoader.php";
				require_once $lib."CryptoCoreFailed.php";
				require_once $lib."CryptoCoreBuiltin.php";
				require_once $lib."CryptoCoreOpenssl.php";
				require_once $lib."Crypto.php";
				require_once $lib."CryptoKey.php";
				require_once $lib."Secret.php";
				require_once $lib."Vault.php";*/
			}

			// Run the initialization code only once
			static::$Initialized=1;
		}
		return true;
	}

	/**
	* Decrypt a SSL encrypted string
	*
	* REMARK : OBSOLETE. As from v2, marknotes use the LockBox
	* library. This is done through task.encrypt.unencrypt.
	*
	* This function is only needed during the upgrade of notes
	* from marknotes v1 till marknotes v2. The upgrade is done thanks
	* the /utils/upgrade_encryption.php script
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
		// For instance :
		// <encrypt data-encrypt="true">ENCRYPTED TEXT</encrypt>
		for ($i; $i < $j; $i++) {
			// Retrieve the attributes (f.i. data-encrypt="true")
			$attributes = $matches[1][$i];

			$isEncrypted = false;

			// Search for data-encrypt="xxxx" if present and if so
			// check if the value is "true" meaning that the content
			// is already encrypted
			$tmp = array();
			preg_match('#data-encrypt="([^"]*)"#', $attributes, $tmp);

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

				// New way of encryption, call the
				// task.encrypt.encrypt
				$aeEvents = \MarkNotes\Events::getInstance();
				$aeEvents->loadPlugins('task.encrypt.encrypt');

				$info = array('data'=>$words);

				$args=array(&$info);
				// Call the encryption tool; method : encrypt
				$aeEvents->trigger('task.encrypt.encrypt::encrypt', $args);

				// And get the unencrypted data
				$encrypted = $args[0]['data'];

				// OK, it's fine, encryption is a success
				$markdown = str_replace(
					$matches[0][$i],
					utf8_encode(
						'<encrypt data-encrypt="true" data-mode="1">'.
						$encrypted.'</encrypt>'
					),
					$markdown
				);
				$rewriteFile = true;
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

		// The note should be unencrypted in case of editing
		// or when the search is fired edit.form : the unencrypted
		// version should be editable
		// search : we also need to search in encrypted text
		$task = $aeSession->get('task');

		// In some cases, the encrypted info shouldn't  stay encrypted.
		// In the edit.form f.i. or when the search task is running.
		//
		// To indicate that encryption should be removed,
		// the calling code should initialize $params['encryption']=0
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

					$encrypted = $encrypted_portion[$i];

					// Check if there is a data-mode="999" attribute
					// in the attribute of the <encrypt> tag

					$tmp = array();
					preg_match('#data-mode="([^"]*)"#', $attributes[$i], $tmp);

					$mode=0;
					if (count($tmp) > 0) {
						// Attribute data-mode="999" is found,
						// this means the encryption tool used is
						// no more MarkNotes v1.x but the
						// tool introduced with Marknotes v2
						// (i.e. Lockbox)
						$mode = $tmp[1];
					}

					if ($mode>0) {
						// New way of encryption, call the
						// task.encrypt.unencrypt
						$aeEvents = \MarkNotes\Events::getInstance();
						$aeEvents->loadPlugins('task.encrypt.unencrypt');

						$info = array('data'=>$encrypted);

						$args=array(&$info);
						// Call the encryption tool; method : encrypt
						$aeEvents->trigger('task.encrypt.unencrypt::unencrypt', $args);

						// And get the unencrypted data
						$decrypt = $args[0]['data'];
					} else {
						// ------------------------------
						// @DEPRECATED
						// If the code comes here, this means that
						// the encryption used is the one used by
						// old version of marknotes (before v2.0
						// launched in 2018). The encryption used
						// was intern and not based on Lockbox.
						// A tool is available under the /utils
						// folder to convert notes from the old
						// encryption method to the new one.
						// Please run it !!!
						// ------------------------------

						// The migration tool
						// utils/upgrade_encryption.php
						// should well be able to call this method
						// so check if running deprecated code is
						// allowed. (Allow_Deprecated_Code is set
						// to 1 by the utility)

						$aeSession = \MarkNotes\Session::getInstance();
						$bAllow = $aeSession->get('Allow_Deprecated_Code', 0);

						if (!$bAllow) {
							$aeFunctions = \MarkNotes\Functions::getInstance();
							$url = $aeFunctions->getCurrentURL();

							$msg = "Your note is using the old way of ".
								"storing encrypted data. Please use ".
								"the ".$url."utils/upgrade_encryption.php";
							throw new \Exception($msg, 999);
						}
						$decrypt = self::sslDecrypt($encrypted);
					}

					$decrypt = ENCRYPT_MARKDOWN_TAG.$decrypt.ENCRYPT_MARKDOWN_TAG;

					$params['markdown'] = str_replace($pattern[$i], $decrypt, $params['markdown']);
				}
			}
		}

		return true;
	}
}
