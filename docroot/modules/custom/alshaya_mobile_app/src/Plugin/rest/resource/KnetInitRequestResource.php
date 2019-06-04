<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_knet\Helper\KnetHelper;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to init k-net request and get url.
 *
 * @RestResource(
 *   id = "knet_init_request",
 *   label = @Translation("K-Net init request and get URL"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/knet/init/{cart_id}"
 *   }
 * )
 */
class KnetInitRequestResource extends ResourceBase {

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
   *   Knet helper service.
   */
  public function setKnetHelper(KnetHelper $knet_helper) {
    $this->knetHelper = $knet_helper;
  }

  /**
   * Responds to GET requests.
   *
   * Initialise k-net request and return state_key and url.
   *
   * @param string $cart_id
   *   Cart ID.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   Non-cacheable response object.
   */
  public function get(string $cart_id) {
    $cart_id = (int) $cart_id;

    if (empty($cart_id) || !$this->knetHelper->validateCart($cart_id)) {
      $this->mobileAppUtility->throwException();
    }

    $cart = $this->knetHelper->getCart($cart_id);

    try {
      $this->knetHelper->setCartId($cart['cart_id']);
      $this->knetHelper->setCurrentUserId(0);
      $this->knetHelper->setCustomerId($cart['customer_id']);
      $this->knetHelper->setOrderId($cart['extension']['real_reserved_order_id']);
      $response = $this->knetHelper->initKnetRequest($cart['totals']['grand'], 'mobile');
    }
    catch (\Exception $e) {
      // Log message in watchdog.
      $this->logger->error($e->getMessage());
      $response = [];
    }

    return new ModifiedResourceResponse($response);
  }

}
