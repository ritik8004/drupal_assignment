<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_knet\Helper\KnetHelper;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get final status and data of transaction.
 *
 * @RestResource(
 *   id = "knet_finalize_request",
 *   label = @Translation("K-Net get final status and data of transaction"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/knet/finalize/{state_key}"
 *   }
 * )
 */
class KnetFinalizeRequestResource extends ResourceBase {

  /**
   * K-Net Helper.
   *
   * @var \Drupal\alshaya_knet\Helper\KnetHelper
   */
  private $knetHelper;

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  private $mobileAppUtility;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create(
      $container,
      $configuration,
      $plugin_id,
      $plugin_definition
    );

    $instance->setMobileAppUtility($container->get('alshaya_mobile_app.utility'));
    if ($container->has('alshaya_knet.helper')) {
      $instance->setKnetHelper($container->get('alshaya_knet.helper'));
    }

    return $instance;
  }

  /**
   * Setter for mobile app utility object.
   *
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   Mobile app utility service.
   */
  public function setMobileAppUtility(MobileAppUtility $mobile_app_utility) {
    $this->mobileAppUtility = $mobile_app_utility;
  }

  /**
   * Setter for K-Net helper object.
   *
   * @param \Drupal\alshaya_knet\Helper\KnetHelper $knet_helper
   *   K-Net helper service.
   */
  public function setKnetHelper(KnetHelper $knet_helper) {
    $this->knetHelper = $knet_helper;
  }

  /**
   * Responds to GET requests.
   *
   * K-Net get final status and data of transaction.
   *
   * @param string $state_key
   *   State Key.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   Non-cacheable response object.
   */
  public function get(string $state_key) {
    if (empty($state_key)) {
      $this->mobileAppUtility->throwException();
    }

    try {
      $response = $this->knetHelper->getKnetStatus($state_key);
    }
    catch (\Exception $e) {
      // Log message in watchdog.
      $this->logger->error($e->getMessage());
      $response = [];
    }

    return new ModifiedResourceResponse($response);
  }

}
