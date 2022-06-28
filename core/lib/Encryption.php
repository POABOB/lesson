<?php
namespace core\lib;
if ( ! defined('PPP')) exit('非法入口');
use core\lib\config;

Class Encryption {
    private $ENC_METHOD;
    private $ENC_KEY;
    private $ENC_IV;
    private $ENC_SALT;

    public static function factory() {
        return new self; 
    } 

    public function __construct($METHOD = 'AES-256-CBC', $KEY = 'RANDOM_KEY_With_API', $IV = 'Initialization_Vector_With_API', $SALT = 'sw$') {
        try {
            $this->ENC_METHOD = (isset($METHOD) && !empty($METHOD) && $METHOD != NULL) ? $METHOD : config::get('METHOD', 'encryption');
            $this->ENC_KEY = (isset($KEY) && !empty($KEY) && $KEY != NULL) ? $KEY : config::get('KEY', 'encryption');
            $this->ENC_IV = (isset($IV) && !empty($IV) && $IV != NULL) ? $IV : config::get('IV', 'encryption');
            $this->ENC_SALT = (isset($SALT) && !empty($SALT) && $SALT != NULL) ? $SALT : config::get('SALT', 'encryption');
        } catch (Exception $e) {
           return "Caught exception: ".$e->getMessage();
        }
    }

    //加密
    public function Encrypt($string) {
        try {
            $output = false;
            $key = hash('sha256', $this->ENC_KEY);
            // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
            $iv = substr(hash('sha256', $this->ENC_IV), 0, 16);
            $output = openssl_encrypt($string, $this->ENC_METHOD, $key, 0, $iv);
            $output = base64_encode($output);
            return $output;
       } catch (Exception $e) {
            return "Caught exception: ".$e->getMessage();
        }
    }

    //解密
    public function Decrypt($string) {
        try {
            $output = false;
            $key = hash('sha256', $this->ENC_KEY);
            // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
            $iv = substr(hash('sha256', $this->ENC_IV), 0, 16);
            $output = openssl_decrypt(base64_decode($string), $this->ENC_METHOD, $key, 0, $iv);
            return $output;
        } catch (Exception $e) {
            return "Caught exception: ".$e->getMessage();
        }
    }

    //密碼專用
    public function EncryptPassword($Input) {
        try {
            if (!isset($Input) || $Input == null || empty($Input)) { return false;}
            // PERFORM MD5 ENCRYPTION ON PASSWORD SALT.
            $SALT = $this->Encrypt($this->ENC_SALT);
            $SALT = md5($SALT);

            // ENCRYPT PASSWORD
            $Input = md5($this->Encrypt(md5($Input)));
            $Input = $this->Encrypt($Input);
            $Input =  md5($Input);

            // PERFORM ANOTHER ENCRYPTION FOR THE ENCRYPTED PASSWORD + SALT.
            $Encrypted = $this->Encrypt($SALT).$this->Encrypt($Input);
            $Encrypted = sha1($Encrypted.$SALT);

            // RETURN THE ENCRYPTED PASSWORD AS MD5
            return md5($Encrypted);
          } catch (Exception $e) {
            return "Caught exception: ".$e->getMessage();
        }
    }
}