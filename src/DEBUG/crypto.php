<?php

define ('DS', DIRECTORY_SEPARATOR);
echo '<h1>'.PHP_VERSION.'</h1>';
$w = stream_get_wrappers();
echo 'openssl: ',  extension_loaded  ('openssl') ? 'yes':'no', "<br/>";
echo 'http wrapper: ', in_array('http', $w) ? 'yes':'no', "<br/>";
echo 'https wrapper: ', in_array('https', $w) ? 'yes':'no', "<br/>";
echo '<hr/>';
/*<!-- build:debug -->*/
//die("<h1>Died in ".__FILE__.", line ".__LINE__." : </h1>");
/*<!-- endbuild -->*/
use starekrow\Lockbox\CryptoKey;
use starekrow\Lockbox\Secret;
use starekrow\Lockbox\Vault;

$lib = dirname(__DIR__).DS.'marknotes\plugins'.DS.
	'markdown\encrypt\libs\lockbox'.DS;

// Include Lockbox
require_once $lib."CryptoCore.php";
require_once $lib."CryptoCoreLoader.php";
require_once $lib."CryptoCoreFailed.php";
require_once $lib."CryptoCoreBuiltin.php";
require_once $lib."CryptoCoreOpenssl.php";
require_once $lib."Crypto.php";
require_once $lib."CryptoKey.php";
require_once $lib."Secret.php";
require_once $lib."Vault.php";

// CryptoKey defaults to AES-128-CBC encryption with a random key
$key = new CryptoKey();
$message = "You can't see me.";
echo $key->Lock( $message ).'<hr/>';

$key = new CryptoKey( "ILikeCheese", null, "AES-256-ECB" );
$no_see_um = $key->Lock( "This text is safe." );
echo $no_see_um.'<hr/>';
$see_um = $key->Unlock( $no_see_um );
echo $see_um.'<hr/>';
