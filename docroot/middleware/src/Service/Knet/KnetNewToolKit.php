<?php

namespace App\Service\Knet;

/**
 * Class KnetNewToolKit.
 */
class KnetNewToolKit extends E24PaymentPipe {

  /**
   * Tranportal id.
   *
   * @var mixed
   */
  protected $tranportalId = NULL;

  /**
   * Tranportal password.
   *
   * @var mixed
   */
  protected $tranportalPassword = NULL;

  /**
   * Terminal resource key.
   *
   * @var mixed
   */
  protected $terminalResourceKey = NULL;

  /**
   * Redirect url for K-Net.
   *
   * @var mixed
   */
  protected $redirectUrl = NULL;

  /**
   * K-Net PG url.
   *
   * @var mixed
   */
  protected $knetUrl = NULL;

  /**
   * Set tranportal id.
   *
   * @param string $tranportal_id
   *   Tranportal id.
   */
  public function setTranportalId($tranportal_id) {
    $this->tranportalId = $tranportal_id;
  }

  /**
   * Set tranportal password.
   *
   * @param string $tranportal_password
   *   Tranportal password.
   */
  public function setTranportalPassword($tranportal_password) {
    $this->tranportalPassword = $tranportal_password;
  }

  /**
   * Set terminal resource key.
   *
   * @param string $terminal_resource_key
   *   Terminal resource key.
   */
  public function setTerminalResourceKey($terminal_resource_key) {
    $this->terminalResourceKey = $terminal_resource_key;
  }

  /**
   * Set the K-net Url.
   *
   * @param string $knet_url
   *   K-Net url.
   */
  public function setKnetUrl($knet_url) {
    $this->knetUrl = $knet_url;
  }

  /**
   * Set redirect url.
   *
   * @param string $redirect_url
   *   Redirect url.
   */
  public function setRedirectUrl($redirect_url) {
    $this->redirectUrl = $this->knetUrl . '?param=paymentInit&trandata=' . $redirect_url;
  }

  /**
   * {@inheritdoc}
   */
  public function getRedirectUrl() {
    return $this->redirectUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function performPaymentInitialization() {
    // If id, password, key or url is not available, then don't process
    // further.
    if (empty($this->tranportalId)
      || empty($this->tranportalPassword)
      || empty($this->terminalResourceKey)
      || empty($this->knetUrl)) {
      $this->error = 'K-Net required parameters not available.';
      return FALSE;
    }

    $url = 'id=' . $this->tranportalId . '&password=' . $this->tranportalPassword;

    if ($this->amt) {
      $url .= '&amt=' . $this->amt;
    }

    if ($this->trackId) {
      $url .= '&trackid=' . $this->trackId;
    }

    if ($this->currency) {
      $url .= '&currencycode=' . $this->currency;
    }

    if ($this->language) {
      $url .= '&langid=' . $this->language;
    }

    if ($this->action) {
      $url .= '&action=' . $this->action;
    }

    if ($this->responseUrl) {
      $url .= '&responseURL=' . $this->responseUrl;
    }

    if ($this->errorURL) {
      $url .= '&errorURL=' . $this->errorURL;
    }

    if ($this->udf1) {
      $url .= '&udf1=' . $this->udf1;
    }

    if ($this->udf2) {
      $url .= '&udf2=' . $this->udf2;
    }

    if ($this->udf3) {
      $url .= '&udf3=' . $this->udf3;
    }

    if ($this->udf4) {
      $url .= '&udf4=' . $this->udf4;
    }

    if ($this->udf5) {
      $url .= '&udf5=' . $this->udf5;
    }

    // Encrypt the request url.
    $url = $this->encryptAes($url, $this->terminalResourceKey);

    $url .= '&tranportalId=' . $this->tranportalId . '&responseURL=' . $this->responseUrl . '&errorURL=' . $this->errorURL;

    if (strlen($url) == 0) {
      $this->error = 'Payment Initialization failed.';
      return FALSE;
    }

    $this->setRedirectUrl($url);
    return TRUE;
  }

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
  public function encryptAes($str, $key) {
    $str = $this->pkcs5Pad($str);
    $encrypted = openssl_encrypt($str, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $key);
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
    $blocksize = 16;
    $pad = $blocksize - (strlen($text) % $blocksize);
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
    $chars = array_map("chr", $byteArray);
    // @codingStandardsIgnoreLine
    $bin = join($chars);
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
    $iv = $key;
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
    $chars = array_map("chr", $byteArray);
    // @codingStandardsIgnoreLine
    return join($chars);
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
