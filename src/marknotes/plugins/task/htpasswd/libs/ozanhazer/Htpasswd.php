<?php
/**
 * Htpasswd writer for Apache.
 * You can add or delete users or you can update their passwords...
 *
 * Features:
 *    Supports crypt, md5 and sha1 algorithms
 *    Locks the htpasswd file to prevent conflicts while writing.
 *    Throws an error on invalid usernames.
 *    Tested on windows and debian (apache only)
 *    Whole htpasswd file is read into the memory so be careful if you have lot's of users
 *    (In fact you should consider a different kind of authentication mechanism if you
 *     have that much users)
 *
 *
 * Usage:
 *    $htpasswd = new Htpasswd('.htpasswd');
 *    $htpasswd->addUser('ozan', '123456');
 *    $htpasswd->updateUser('ozan', '654321');
 *    $htpasswd->deleteUser('ozan');
 *
 * Apache htpasswd can be encrypted in three ways: crypt (unix only), a modified version of md5 and sha1.
 * You can define the encryption method when you're setting the password:
 *
 * $htpasswd->addUser('ozan', '123456', Htpasswd::ENCTYPE_APR_MD5);
 * $htpasswd->addUser('ozan', '123456', Htpasswd::ENCTYPE_SHA1);
 *
 * Make sure that you use either ENCTYPE_APR_MD5 or ENCTYPE_SHA1 on windows servers as
 * "crypt" is not available on windows servers.
 *
 * For encryption details, see: http://httpd.apache.org/docs/2.2/misc/password_encryptions.html
 */
class Htpasswd
{
    /**
     * @var string
     */
    protected $_filename;

    /**
     * @var array
     */
    protected $_users = array();

    const ENCTYPE_CRYPT   = 'crypt';
    const ENCTYPE_APR_MD5 = 'apr_md5';
    const ENCTYPE_SHA1    = 'sha1';

    public function __construct($filename)
    {
        if (!isset($filename) || !trim($filename)) {
            throw new InvalidArgumentException('File name not sent');
        }

        if (!file_exists($filename)) {
            throw new DomainException('Password file could not be found');
        }

        /**
         * Get the users into memory
         */
        $lines = file($filename);

        if (count($lines)) {
            foreach ($lines as $line) {
                $line = trim($line);
                list($user, $pass) = explode(':', $line);
                $this->_users[$user] = $pass;
            }
        }

        /**
         * Filename
         */
        $this->_filename = $filename;
    }

    public function userExists($username)
    {
        return array_key_exists($username, $this->_users);
    }

    public function getUsers()
    {
        return $this->_users;
    }

    public function addUser($username, $password, $encType = self::ENCTYPE_CRYPT)
    {
        if ($this->userExists($username)) {
            return false;
        }

        return $this->updateUser($username, $password, $encType);
    }

    public function deleteUser($username)
    {
        if (!$this->userExists($username)) {
            throw new Exception('User not found');
        }

        unset($this->_users[$username]);

        $this->_saveFile();
    }

    public function updateUser($username, $password, $encType = self::ENCTYPE_CRYPT)
    {
        $this->_validateUserName($username);

        $this->_users[$username] = $this->_encryptPassword($password, $encType);
        $this->_saveFile();

        return true;
    }

    protected function _encryptPassword($password, $encType)
    {
        if ($encType == self::ENCTYPE_CRYPT) {
            if (strlen($password) > 8) {
                trigger_error('Only the first 8 characters are taken into account when \'crypt\' algorithm is used.');
            }

            $chars     = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
            $len       = strlen($chars) - 1;
            $salt      = $chars[mt_rand(0, $len)] . $chars[mt_rand(0, $len)];
            $cryptPass = crypt($password, $salt);
        } elseif ($encType == self::ENCTYPE_APR_MD5) {
            $cryptPass = $this->_cryptApr1Md5($password);
        } elseif ($encType == self::ENCTYPE_SHA1) {
            $hash      = base64_encode(sha1($password, true));
            $cryptPass = '{SHA}' . $hash;
        } else {
            throw new Exception('Invalid encryption type');
        }

        return $cryptPass;
    }

    protected function _validateUserName($username)
    {
        if (strpos($username, ':')) {
            throw new Exception('Invalid username. Username cannot contain colon (:) character');
        }

        if (strlen($username) > 256) {
            throw new Exception('Usernames cannot be longer than 256 bytes');
        }
    }

    protected function _saveFile()
    {
        $data = '';
        foreach ($this->_users as $username => $pass) {
            $data .= "$username:$pass\n";
        }

        file_put_contents($this->_filename, $data, LOCK_EX);
    }

    protected function _cryptApr1Md5($plainpasswd)
    {
        $salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
        $len  = strlen($plainpasswd);
        $text = $plainpasswd . '$apr1$' . $salt;
        $bin  = pack("H32", md5($plainpasswd . $salt . $plainpasswd));
        for ($i = $len; $i > 0; $i -= 16) {
            $text .= substr($bin, 0, min(16, $i));
        }
        for ($i = $len; $i > 0; $i >>= 1) {
            $text .= ($i & 1) ? chr(0) : $plainpasswd{0};
        }
        $bin = pack("H32", md5($text));
        for ($i = 0; $i < 1000; $i++) {
            $new = ($i & 1) ? $plainpasswd : $bin;
            if ($i % 3) $new .= $salt;
            if ($i % 7) $new .= $plainpasswd;
            $new .= ($i & 1) ? $bin : $plainpasswd;
            $bin = pack("H32", md5($new));
        }

        $tmp = '';
        for ($i = 0; $i < 5; $i++) {
            $k = $i + 6;
            $j = $i + 12;
            if ($j == 16) $j = 5;
            $tmp = $bin[$i] . $bin[$k] . $bin[$j] . $tmp;
        }
        $tmp = chr(0) . chr(0) . $bin[11] . $tmp;
        $tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
            "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
            "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
        return "$" . "apr1" . "$" . $salt . "$" . $tmp;
    }
}
