<?php

namespace Drupal\alshaya_customer_portal\Utility;

/**
 * Aes encryption.
 */
class Aes {

  /**
   * The 32 character encryption key.
   *
   * @var string
   */
  protected $key;

  /**
   * The data to encode.
   *
   * @var string
   */
  protected $data;

  /**
   * The method used to encode.
   *
   * @var string
   */
  protected $method;

  /**
   * Available OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING.
   *
   * @var int
   */
  protected $options = 0;

  /**
   * The constructor function.
   *
   * @param string $data
   *   The data to encode.
   * @param string $key
   *   The key used to encode.
   * @param string $blockSize
   *   The block size of encoding.
   * @param string $mode
   *   The mode of encoding.
   */
  public function __construct($data = NULL, $key = NULL, $blockSize = NULL, $mode = 'CBC') {
    $this->setData($data);
    $this->setKey($key);
    $this->setMethod($blockSize, $mode);
  }

  /**
   * Set the data to encode.
   *
   * @param string $data
   *   The data to encode.
   */
  public function setData($data) {
    $this->data = $data;
  }

  /**
   * Set the encryption key to use.
   *
   * @param string $key
   *   The 32 character encryption key.
   */
  public function setKey($key) {
    $this->key = $key;
  }

  /**
   * Set the mode of encryption.
   *
   * Available values are:
   * CBC 128 192 256
   * CBC-HMAC-SHA1 128 256
   * CBC-HMAC-SHA256 128 256
   * CFB 128 192 256
   * CFB1 128 192 256
   * CFB8 128 192 256
   * CTR 128 192 256
   * ECB 128 192 256
   * OFB 128 192 256
   * XTS 128 256
   *
   * @param string $blockSize
   *   The size of encoding block.
   * @param string $mode
   *   The mode of encryption.
   */
  public function setMethod($blockSize, $mode = 'CBC') {
    if ($blockSize === 192
      && in_array('', ['CBC-HMAC-SHA1', 'CBC-HMAC-SHA256', 'XTS'])) {
      $this->method = NULL;
      throw new \Exception('Invlid block size and mode combination!');
    }
    $this->method = 'AES-' . $blockSize . '-' . $mode;
  }

  /**
   * Validates if parameters set are correct or not.
   *
   * @return bool
   *   If valid, TRUE is returned, else FALSE.
   */
  public function validateParams() {
    if ($this->data != NULL && $this->method != NULL) {
      // Check if padding needs to be added or not.
      if ($pad = (32 - (strlen($this->data) % 32))) {
        $this->addPadding($pad);
      }
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Add 0 Padding.
   */
  protected function addPadding($multiplier) {
    $this->data .= '&';
    $multiplier--;
    $this->data .= str_repeat(0, $multiplier);
  }

  /**
   * It must be the same when you encrypt and decrypt.
   *
   * @return string|false
   *   The generated string of bytes on success else FALSE.
   */
  protected function getIv() {
    return openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->method));
  }

  /**
   * Encrypts data and returns it.
   *
   * @return string
   *   The encrypted string.
   *
   * @throws Exception
   */
  public function encrypt() {
    if ($this->validateParams()) {
      return trim(openssl_encrypt($this->data, $this->method, $this->key, OPENSSL_ZERO_PADDING, $this->getIv()));
    }
    else {
      throw new \Exception('Invlid params!');
    }
  }

  /**
   * Decrypts the given string.
   *
   * @return string|false
   *   The decrypted string on success or false on failure.
   *
   * @throws Exception
   */
  public function decrypt() {
    if ($this->validateParams()) {
      $ret = openssl_decrypt($this->data, $this->method, $this->key, $this->options, $this->getIv());
      return trim($ret);
    }
    else {
      throw new \Exception('Invlid params!');
    }
  }

}
