<?php
/**
 *
 * Intern plugin, don't answer to URL
 */
namespace MarkNotes\Plugins\Task\Encrypt;

defined('_MARKNOTES') or die('No direct access allowed');

use starekrow\Lockbox\CryptoKey;
use starekrow\Lockbox\Secret;

class Encrypt
{
	/**
	 * Generate the code for the upload form
	 */
	public static function run(&$params = null)
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arrSettings = $aeSettings->getPlugins('plugins.options.markdown.encrypt');

		// Only if we've a password for the encryption
		if (trim($arrSettings['password'])!=='') {
			// Get the password
			$password = $arrSettings['password'];
			$method = 'AES-256-ECB';

			// The info to encrypt is stored in $params['data']
			$info = $params['data'];

			// We can go further, load lockbox and crypt the info
			$lib = __DIR__.DS.'libs/lockbox'.DS;

			// Include Lockbox
			require_once $lib."CryptoCore.php";
			require_once $lib."CryptoCoreLoader.php";
			require_once $lib."CryptoCoreFailed.php";
			require_once $lib."CryptoCoreBuiltin.php";
			require_once $lib."CryptoCoreOpenssl.php";
			require_once $lib."Crypto.php";
			require_once $lib."CryptoKey.php";
			require_once $lib."Secret.php";

			$key = new CryptoKey($password, null, $method);
			$encrypted = $key->lock($info);

			// Be sure we can retrieve the information
			if ($key->Unlock($encrypted) === $info) {
				//echo 'ENCRYPTION SUCCESS '.$encrypted.'<hr/>';
				$params['data'] = $encrypted;
			}
		}
		return true;
	}

	/**
	 * Attach the function and responds to events
	 */
	public function bind(string $task)
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->bind('encrypt', __CLASS__.'::run', $task);
		return true;
	}
}
