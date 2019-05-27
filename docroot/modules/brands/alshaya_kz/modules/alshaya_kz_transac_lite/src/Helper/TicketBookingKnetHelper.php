<?php

namespace Drupal\alshaya_kz_transac_lite\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\alshaya_knet\Helper\KnetHelper;

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
    // @Todo - process the response from k-net.
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetSuccess(string $state_key, array $data = []) {
    // @Todo - process the response from k-net.
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetFailed(string $state_key) {
    // @Todo - process the response from k-net.
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetError(string $quote_id) {
    // @Todo - process the response from k-net.
  }

}
