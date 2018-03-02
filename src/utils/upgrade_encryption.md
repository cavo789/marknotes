# Upgrade encryption method

## Introduction

With his first version (marknotes v1), Marknotes was using the [mcrypt_get_iv_size](http://php.net/manual/fr/function.mcrypt-get-iv-size.php) PHP function for his encryption method offers by the encrypt plugin.

Unfortunately, this function is obsolete since PHP 7.1 and has been removed with PHP 7.2.

So, notes that have been encrypted with `mcrypt_get_iv_size` needs to be converted : the encrypted part should be unencryted so marknotes can encrypt it again but with a newer method.

In order to not have this problem anymore, I've choosen to use [Lockbox](https://github.com/starekrow/lockbox) which offers multiple encryption method and support PHP 5 till the latest one.

## How to use

So, we need to convert notes and use the new encryption method.

To do this, we can use the `/utils/upgrade_encryption.php` script. 

That script is straigt forward : he'll scan your website, retrieve any .md files under your documentation folder and, note by note, will open it, detect if there are encrypted part and if it's the case, will unencrypt these part using the old method and encrypt again using the same password but, this time with [Lockbox](https://github.com/starekrow/lockbox).

So you just need to run the script once and that's all.

## Remarks

### PHP version 

The script needs to unencrypt the current notes so **you need to  execute the script with PHP 7.0 or 7.1**. There is no choice to this.

PHP 7.0/7.1 should be used since `mcrypt_get_iv_size` doesn't exists anymore with PHP 7.2. So, if needed, please downgrade your PHP version.

### Batch mode

This utility is straigt-forward : running the script will start the conversion and the output will be done at the end of the execution.

If your website has a lot of notes, it'll take time to make the conversion and the script won't provide an Ajax interface with f.i. a progress bar. Don't be afraid if the script is running for a long time, just wait until he finished.

### Only to fire once (but safe to run more than once)

The script should be executed only once. No need to run it more than once. Every encrypted part will be converted and will use [Lockbox](https://github.com/starekrow/lockbox). The conversion script will then add a `data-mode="1"` attribute to the `<encrypt>` tag to determine that, that encryption is now using Lockbox. 

It's safe to run this script more than once (while it's not needed at all). If the note was already upgraded, the script will process the note again and will generate a new, random, hash but, of course, the decoding will give the same, unencrypted, value.

As soon as you'll use Marknotes v2 (and not a pre-version), yours notes will be encrypted using the new method.
