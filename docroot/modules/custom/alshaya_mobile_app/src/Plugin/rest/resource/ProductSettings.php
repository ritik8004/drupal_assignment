<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get product config.
 *
 * @RestResource(
 *   id = "product_general_settings",
 *   label = @Translation("Product General Settings"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/product-general-settings"
 *   }
 * )
 */
class ProductSettings extends ResourceBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The list of config cache tags.
   *
   * @var array
   */
  protected $cacheTags = [];

  /**
   * ConfigProduct Resource constructor.
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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    LanguageManagerInterface $language_manager,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->languageManager = $language_manager;
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
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('language_manager'),
      $container->get('config.factory')
    );
  }

  /**
   * Lists configs to be sent in the response.
   *
   * If you don't specify the individual config items,
   * all items will be exposed.
   *
   * @return array
   *   The list of configs.
   */
  private function getConfigList() {
    return [
      'alshaya_acm_product.settings' => [
        'all_products_buyable',
        'non_refundable_text',
        'non_refundable_tooltip',
        'same_day_delivery_text',
        'same_day_delivery_sub_text',
        'delivery_in_only_city_text',
        'delivery_in_only_city_key',
        'legal_notice_enabled',
        'legal_notice_label',
        'legal_notice_summary',
      ],
      'alshaya_acm_product.home_delivery' => [],
      'alshaya_click_collect.settings' => [],
      'acq_commerce.currency' => [],
    ];
  }

  /**
   * Getter for cacheTags.
   *
   * @return array
   *   Cache tags.
   */
  private function getCacheTags() {
    return $this->cacheTags;
  }

  /**
   * Setter for cacheTags.
   *
   * @param array $tags
   *   The array of tags.
   */
  private function addCacheTags(array $tags) {
    $this->cacheTags = array_unique(array_merge($this->cacheTags, $tags));
  }

  /**
   * Returns product configuration.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing configuration.
   */
  public function get() {
    $list = $this->getConfigList();

    // Build an array with config data.
    $data = [];
    foreach (array_keys($list) as $config_name) {
      $values = $this->getConfig($config_name);
      if (empty($list[$config_name])) {
        // If individual configs were not specified, get all config values.
        foreach (array_keys($values->getRawData()) as $item) {
          // Exclude certain configs.
          if (in_array($item, ['_core'])) {
            continue;
          }
          $data[$config_name][$item] = $values->get($item);
        }
      }
      else {
        // Get listed configs only.
        foreach ($list[$config_name] as $item) {
          $data[$config_name][$item] = $values->get($item);
        }
      }
    }

    $response = new ResourceResponse($data);
    $cacheableMetadata = $response->getCacheableMetadata();
    $cacheableMetadata->addCacheTags($this->getCacheTags());
    $response->addCacheableDependency($cacheableMetadata);

    return $response;
  }

  /**
   * Get all values for a given config name, provides cache tag info.
   *
   * @param string $name
   *   The config name.
   *
   * @return array|mixed
   *   The config.
   */
  private function getConfig($name) {
    $config = $this->configFactory->get($name);
    $this->addCacheTags($config->getCacheTags());

    return $config;
  }

}
