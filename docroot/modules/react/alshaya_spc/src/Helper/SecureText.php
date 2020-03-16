<?php

namespace Drupal\alshaya_spc\Helper;

/**
 * Class SecureText.
 */
class SecureText {

  /**
   * Encrypt given value by a key.
   *
   * @param mixed $str
   *   String that need to encrypt.
   * @param mixed $key
   *   Key used for encryption.
   *
   * @return array|bool|string
   *   Encrypted value.
   */
  public function encrypt($str, $key) {
    $str = $this->pkcs5Pad($str);
    $iv = substr($key, 0, 16);
    $encrypted = openssl_encrypt($str, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
    $encrypted = base64_decode($encrypted);
    $encrypted = unpack('C*', ($encrypted));
    $encrypted = $this->byteArray2Hex($encrypted);
    $encrypted = urlencode($encrypted);
    return $encrypted;
  }

  /**
   * Internal method for encryption.
   *
   * @param mixed $text
   *   Text as input.
   *
   * @return string
   *   result string.
   */
  protected function pkcs5Pad($text) {
    $blockSize = 16;
    $pad = $blockSize - (strlen($text) % $blockSize);
    return $text . str_repeat(chr($pad), $pad);
  }

  /**
   * Internal method for encryption.
   *
   * @param mixed $byteArray
   *   Input value.
   *
   * @return string
   *   Result string.
   */
  protected function byteArray2Hex($byteArray) {
    $chars = array_map('chr', $byteArray);
    $bin = implode('', $chars);
    return bin2hex($bin);
  }

  /**
   * Decrypt given value by a key.
   *
   * @param mixed $code
   *   Code to decrypt.
   * @param mixed $key
   *   Key used for decryption.
   *
   * @return mixed
   *   Decrypted response.
   */
  public function decrypt($code, $key) {
    $code = $this->hex2ByteArray(trim($code));
    $code = $this->byteArray2String($code);
    $iv = substr($key, 0, 16);
    $code = base64_encode($code);
    $decrypted = openssl_decrypt($code, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
    return $this->pkcs5Unpad($decrypted);
  }

  /**
   * Internal method for decryption.
   *
   * @param mixed $hexString
   *   Input value.
   *
   * @return mixed
   *   Output value.
   */
  protected function hex2ByteArray($hexString) {
    $string = hex2bin($hexString);
    return unpack('C*', $string);
  }

  /**
   * Internal method for decryption.
   *
   * @param mixed $byteArray
   *   Input value.
   *
   * @return mixed
   *   Output value.
   */
  protected function byteArray2String($byteArray) {
    $chars = array_map('chr', $byteArray);
    return implode('', $chars);
  }

  /**
   * Internal method for decryption.
   *
   * @param mixed $text
   *   Input value.
   *
   * @return mixed
   *   Output value.
   */
  protected function pkcs5Unpad($text) {
    $pad = ord($text{strlen($text) - 1});
    if ($pad > strlen($text)) {
      return FALSE;
    }
    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
      return FALSE;
    }
    return substr($text, 0, -1 * $pad);
  }

}
