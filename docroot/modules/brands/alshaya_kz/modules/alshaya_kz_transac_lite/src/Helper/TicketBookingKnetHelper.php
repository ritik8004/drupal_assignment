<?php

namespace Drupal\alshaya_kz_transac_lite\Helper;

use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\alshaya_knet\Helper\KnetHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class TicketBookingKnetHelper.
 *
 * @package Drupal\alshaya_kz_transac_lite\Helper
 */
class TicketBookingKnetHelper extends KnetHelper {

  /**
   * K-Net Helper class.
   *
   * @var \Drupal\alshaya_knet\Helper\KnetHelper
   */
  protected $knetHelper;

  /**
   * TicketBookingKnetHelper constructor.
   *
   * @param \Drupal\alshaya_knet\Helper\KnetHelper $knet_helper
   *   K-net helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\State\StateInterface $state
   *   State object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger channel.
   */
  public function __construct(KnetHelper $knet_helper,
                              ConfigFactoryInterface $config_factory,
                              StateInterface $state,
                              LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($config_factory, $state, $logger_factory->get('alshaya_kz_transac_lite_knet'));
    $this->knetHelper = $knet_helper;

  }

  /**
   * {@inheritdoc}
   */
  public function processKnetResponse(array $response = []) {
    $state_key = $response['state_key'];
    $state_data = $this->state->get($state_key);

    if ($response['result'] == 'CAPTURED') {
      $url = Url::fromRoute('alshaya_kz_transac_lite.payemnt_option', ['option' => 'success'])->toString();
    }
    else {
      $url = Url::fromRoute('alshaya_kz_transac_lite.payemnt_option', ['option' => 'failed'])->toString();
    }

    $this->logger->info('KNET update for Response: @message State: @state', [
      '@message' => json_encode($response),
      '@state' => json_encode($state_data),
    ]);
    return new RedirectResponse($url);
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetSuccess(string $state_key, array $data = []) {
    if ($data['result'] !== 'CAPTURED') {
      return $this->processKnetFailed($state_key);
    }

    $this->logger->info('KNET payment complete for @quote_id.<br>@message', [
      '@quote_id' => $data['quote_id'],
      '@message' => json_encode($data),
    ]);

    $url = Url::fromRoute('alshaya_kz_transac_lite.payemnt_option', ['option' => 'success'])->toString();
    return new RedirectResponse($url);
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetFailed(string $state_key) {
    $data = $this->state->get($state_key);
    parent::processKnetFailed($state_key);

    $this->logger->error('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.</br> Transaction ID: @transaction_id Payment ID: @payment_id Result code: @result_code', [
      '@transaction_id' => !empty($data['transaction_id']) ? $data['transaction_id'] : '',
      '@payment_id' => $data['payment_id'],
      '@result_code' => $data['result'],
    ]);

    $url = Url::fromRoute('alshaya_kz_transac_lite.payemnt_option', ['option' => 'failed'])->toString();
    return new RedirectResponse($url, 302);
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetError(string $quote_id) {
    $message = $this->t('User either cancelled or response url returned error.');

    $message .= PHP_EOL . $this->t('Debug info:') . PHP_EOL;
    foreach ($_GET as $key => $value) {
      $message .= $key . ': ' . $value . PHP_EOL;
    }

    $this->logger->error('KNET payment failed for @quote_id: @message', [
      '@quote_id' => $quote_id,
      '@message' => $message,
    ]);

    $url = Url::fromRoute('alshaya_kz_transac_lite.payemnt_option', ['option' => 'failed'])->toString();
    return new RedirectResponse($url, 302);
  }

}
