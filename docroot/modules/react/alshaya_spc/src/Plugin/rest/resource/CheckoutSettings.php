<?php

namespace Drupal\alshaya_spc\Plugin\rest\resource;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get checkout settings.
 *
 * @RestResource(
 *   id = "checkout_settings",
 *   label = @Translation("Checkout Settings"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/checkout-settings"
 *   }
 * )
 */
class CheckoutSettings extends ResourceBase {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * CheckoutSettings constructor.
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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->configFactory = $config_factory;
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
      $container->get('logger.factory')->get('alshaya_spc'),
      $container->get('config.factory')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing checkout settings.
   */
  public function get() {
    $settings = $this->configFactory->get('alshaya_click_collect.settings');
    $data['cnc_subtitle_available'] = $settings->get('checkout_click_collect_available');
    $data['cnc_subtitle_unavailable'] = $settings->get('checkout_click_collect_unavailable');
    $data['checkout_hd_subtitle'] = $this->t('Standard delivery for purchases over KD 250');

    $response = new ResourceResponse($data);
    $response->getCacheableMetadata()->addCacheTags(['config:alshaya_click_collect.settings']);
    return $response;
  }

}
