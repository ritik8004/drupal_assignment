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
   * @param string $state_key
   *   State Key.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   Non-cacheable response object.
   */
  public function get(string $state_key) {
    if (empty($state_key)) {
      throw new NotFoundHttpException();
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
