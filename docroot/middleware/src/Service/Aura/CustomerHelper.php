<?php

namespace App\Service\Aura;

use App\Service\Drupal\Drupal;
use App\Service\Magento\MagentoApiWrapper;
use App\Service\Utility;
use Psr\Log\LoggerInterface;

/**
 * Class CustomerHelper.
 *
 * @package App\Service\Aura
 */
class CustomerHelper {

  /**
   * Magento API Wrapper service.
   *
   * @var \App\Service\Magento\MagentoApiWrapper
   */
  protected $magentoApiWrapper;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Drupal service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

  /**
   * Utility service.
   *
   * @var \App\Service\Utility
   */
  protected $utility;

  /**
   * AuraHelper constructor.
   *
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API wrapper service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(
    MagentoApiWrapper $magento_api_wrapper,
    LoggerInterface $logger,
    Drupal $drupal,
    Utility $utility
  ) {
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->logger = $logger;
    $this->drupal = $drupal;
    $this->utility = $utility;
  }

  /**
   * Get Customer Points.
   *
   * @return array
   *   Return customer's point details.
   */
  public function getCustomerPoints($customer_id) {
    try {
      $endpoint = sprintf('/customers/apc-points-balance/%s', $customer_id);
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);

      return $response;
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to fetch loyalty points for user with customer id @customer_id.', [
        '@customer_id' => $customer_id,
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Get Customer Tier.
   *
   * @return array
   *   Return customer's tier.
   */
  public function getCustomerTier($customer_id) {
    try {
      $endpoint = sprintf('/customers/apc-tiers/%s', $customer_id);
      $response = $this->magentoApiWrapper->doRequest('GET', $endpoint);

      return $response;
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to fetch tier information for user with customer id @customer_id.', [
        '@customer_id' => $customer_id,
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

}
