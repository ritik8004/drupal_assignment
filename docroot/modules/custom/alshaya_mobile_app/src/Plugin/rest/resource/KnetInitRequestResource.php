<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_acm_knet\KnetHelper;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
   * @var \Drupal\alshaya_acm_knet\KnetHelper
   */
  private $knetHelper;

  /**
   * DeliveryMethodResource constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param \Drupal\alshaya_acm_knet\KnetHelper $knet_helper
   *   K-Net Helper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, KnetHelper $knet_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->knetHelper = $knet_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('alshaya_acm_knet.helper')
    );
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
      throw new NotFoundHttpException();
    }

    $cart = $this->knetHelper->getCart($cart_id);

    try {
      $response = $this->knetHelper->initKnetRequest(
        $cart['cart_id'],
        0,
        $cart['customer_id'],
        $cart['extension']['real_reserved_order_id'],
        $cart['totals']['grand'],
        'mobile'
      );
    }
    catch (\Exception $e) {
      // Log message in watchdog.
      $this->logger->error($e->getMessage());
      $response = [];
    }

    return new ModifiedResourceResponse($response);
  }

}
