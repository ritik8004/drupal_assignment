<?php

namespace Drupal\alshaya_knet\Knet;

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
  protected $knetUrl = 'https://kpaytest.com.kw/kpg/PaymentHTTP.htm';

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
    // If id, password or key is not available, then don't process
    // further.
    if (empty($this->tranportalId) || empty($this->tranportalPassword) || empty($this->terminalResourceKey)) {
      $this->error = 'Required parameters not available.';
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

    $enc_dec = new KnetEncryptDecypt();

    // Encrypt the request url.
    $url = $enc_dec->encryptAes($url, $this->terminalResourceKey);

    $url .= '&tranportalId=' . $this->tranportalId . '&responseURL=' . $this->responseUrl . '&errorURL=' . $this->errorURL;

    if (strlen($url) == 0) {
      $this->error = 'Payment Initialization failed.';
      return FALSE;
    }

    $this->setRedirectUrl($url);
    return TRUE;
  }

}
