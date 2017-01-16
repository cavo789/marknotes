<?php
declare(strict_types=1);

/* REQUIRES PHP 7.x AT LEAST */

/**
 * Encryption class.  Use SSL for the encryption.  
 * 
 * Partially based on @link : http://php.net/manual/fr/function.openssl-decrypt.php#111832
 */
class aeSecureEncrypt {
   
   private $_method='';
   private $_password='';
   private $_iv='';
   
   /**
    * Initialize the class
    * 
    * @param string $password  The password to use for the encryption
    * @param string $method    OPTIONAL, If not mentionned, will be 'aes-256-ctr'
    */
   function __construct(string $password, string $method) {
      
      $this->_password=$password;
      
      if (trim($method)==NULL) {
         $this->_method='aes-256-ctr';
      } else {      
         $this->_method=$method;
      }

      // Dynamically generate an "IV" i.eI and initialization vector that will ensure cypher to be unique 
      // (http://stackoverflow.com/questions/11821195/use-of-initialization-vector-in-openssl-encrypt)
      // And concatenate that "IV" to the encrypted texte
      
      $iv_size = @mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
      $this->_iv= @mcrypt_create_iv($iv_size, MCRYPT_RAND);;

   } // function __construct()   
   
   /**
    * Encrypt a string by using SSL
    * 
    * @param string $data      The string that should be encrypted
    * @param string $password  OPTIONAL, If not mentionned, use the password define during the class initialization
    * @param string $method    OPTIONAL, If not mentionned, will be 'aes-256-ctr'
    * @return type             The string, encrypted.  The first characters will contains the "Initialization vector", required for the decryption
    */
   public function sslEncrypt(string $data, $password) : string {
      
      if ($password===NULL) $password=$this->_password;
      
      if(function_exists('openssl_encrypt')) {
         return urlencode($this->_iv.openssl_encrypt(urlencode($data), $this->_method, $password, 0, $this->_iv));
      } else {
         return urlencode($this->_iv.exec("echo \"".urlencode($data)."\" | openssl enc -".urlencode($this->_method)." -base64 -nosalt -K ".bin2hex($password)." -iv ".bin2hex($this->_iv)));
      }
      
   } // function sslEncrypt()
   
   /**
    * Decrypt a SSL encrypted string
    * 
    * @param string $data   The string to decrypt.  The first characters contains the "Initialiation vector"
    * @param string $password  OPTIONAL, If not mentionned, use the password define during the class initialization
    * @return type          The string, decrypted
    */
   public function sslDecrypt(string $data, $password) : string {
  
      if ($password===NULL) $password=$this->_password;
      
      $tmp=urldecode($data);
      
      $iv_size = @mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
      $iv = substr($tmp, 0, $iv_size);

      if(function_exists('openssl_decrypt')) {
         return trim(urldecode(openssl_decrypt(substr($tmp, $iv_size), $this->_method, $password, 0, $iv)));
      } else {
        return trim(urldecode(exec("echo \"".$tmp."\" | openssl enc -".$this->_method." -d -base64 -nosalt -K ".bin2hex($password)." -iv ".bin2hex($this->_iv))));
      }

   } // function sslDecrypt()
   
} // class aeSecureEncrypt