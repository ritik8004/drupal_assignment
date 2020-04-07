<?php

namespace App\Service\Knet;

use App\Service\Config\SystemSettings;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class KnetHelper.
 *
 * @package App\Service\Knet
 */
class KnetHelper {

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $settings;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * KnetHelper constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(RequestStack $request,
                              SystemSettings $settings,
                              LoggerInterface $logger) {
    $this->request = $request->getCurrentRequest();
    $this->settings = $settings;
    $this->logger = $logger;
  }

  /**
   * Initialize knet request.
   *
   * @return array
   *   Array containing url and state key.
   */
  public function initKnetRequest($amount,
                                  int $cart_id,
                                  string $order_id,
                                  int $customer_id): array {

    // Get K-Net creds for new toolkit.
    $knet_creds = $this->getNewKnetToolkitCreds();

    // If not configured K-Net.
    if (empty($knet_creds)) {
      throw new \RuntimeException('K-Net PG is not configured.');
    }

    $knet_settings = $this->settings->getSettings('alshaya_knet.settings');

    // We store the cart id as cart id here and change it to quote id in
    // response so no one can directly use the state key from URL in error
    // and use it for success page.
    $state_data = [
      'cart_id' => $cart_id,
      'order_id' => $order_id,
    ];

    // This is just to have the key unique for state data.
    $state_key = md5(json_encode($state_data));

    // Get K-Net toolkit.
    $pipe = new KnetNewToolKit();
    $pipe->setLanguage($this->settings->getRequestLanguage());

    $pipe->setTranportalId($knet_creds['tranportal_id']);
    $pipe->setTranportalPassword($knet_creds['tranportal_password']);
    $pipe->setTerminalResourceKey($knet_creds['terminal_resource_key']);
    $pipe->setKnetUrl($knet_settings['knet_url']);

    // We hard code HTTPS here as varnish request to middleware is always http.
    $host = 'https://' . $this->request->getHttpHost() . '/middleware/public/payment/';
    $response_url = $host . 'knet-response';
    $pipe->setResponseUrl($response_url);

    $error_url = $host . 'knet-error/' . $state_key;
    $pipe->setErrorUrl($error_url);

    $pipe->setAmt($amount);
    $pipe->setTrackId($order_id);
    $pipe->setUdf2($customer_id);
    $pipe->setUdf3($cart_id);

    $pipe->setUdf4($state_key);

    $udf5_prefix = $knet_settings['knet_udf5_prefix'];
    $pipe->setUdf5($udf5_prefix . ' ' . $order_id);

    $pipe->performPaymentInitialization();

    // Check again once if there is any error.
    if ($error = $pipe->getErrorMsg()) {
      throw new \RuntimeException($error);
    }

    $this->logger->info('Payment info for K-Net toolkit version:@version quote id is @quote_id. Reserved order id is @order_id. State key: @state_key', [
      '@order_id' => $order_id,
      '@quote_id' => $cart_id,
      '@state_key' => $state_key,
      '@version' => 'v2',
    ]);

    $state_data['amount'] = $amount;
    $state_data['langcode'] = $this->settings->getRequestLanguage();

    return [
      'id' => $state_key,
      'data' => $state_data,
      'redirectUrl' => $pipe->getRedirectUrl(),
    ];
  }

  /**
   * Get tranportal id, password and resource key for new K-Net toolkit.
   *
   * @return array
   *   Array of credentials.
   */
  public function getNewKnetToolkitCreds() {
    // Get the K-Net keys etc from settings. These settings are stored in
    // secret settings file. See `post-settings/zzz_overrides`.
    $knet_settings = $this->settings->getSettings('knet');

    if (empty($knet_settings)) {
      return [];
    }

    return [
      'tranportal_id' => $knet_settings['tranportal_id'] ?? '',
      'tranportal_password' => $knet_settings['tranportal_password'] ?? '',
      'terminal_resource_key' => $knet_settings['terminal_key'] ?? '',
    ];
  }

  /**
   * Parse and prepare K-Net response data for new toolkit.
   *
   * @param array $input
   *   Data to parse.
   *
   * @return array
   *   Data to return after parse.
   *
   * @throws \Exception
   */
  public function parseAndPrepareKnetData(array $input) {
    // If error is available.
    if (!empty($input['ErrorText']) || !empty($input['Error'])) {
      $this->logger->error('K-Net response contains Error: @error', [
        '@error' => json_encode($input),
      ]);
      return $input;
    }

    $en_dec = new KnetNewToolKit();
    $knet_creds = $this->getNewKnetToolkitCreds();

    // If K-Net is not configured or key is not available.
    if (empty($knet_creds) || empty($knet_creds['terminal_resource_key'])) {
      $message = 'K-Net is not configured or resource key is not available';
      $this->logger->error($message);
      throw new \Exception($message);
    }

    $terminal_resource_key = $knet_creds['terminal_resource_key'];

    // Decrypted data contains a string which seperates values by `&`, so we
    // need to explode this. Example - 'paymentId=123&amt=4545'.
    $output = [];
    $decrypted_data = array_filter(explode('&', $en_dec->decrypt($input['trandata'], $terminal_resource_key)));
    array_walk($decrypted_data, function ($val, $key) use (&$output) {
      $key_value = explode('=', $val);
      $output[$key_value[0]] = $key_value[1];
    });

    return $output;
  }

}
