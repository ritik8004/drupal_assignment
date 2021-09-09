<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get product config.
 *
 * @RestResource(
 *   id = "config_product",
 *   label = @Translation("Product config"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/config/product"
 *   }
 * )
 */
class ConfigProduct extends ResourceBase {

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
   * Lists configs to be sent as response.
   *
   * If you don't specify the individual keys, all items will be returned.
   *
   * @return array
   *   The list of configs.
   */
  private function getList() {
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
      'alshaya_acm_product.home_delivery',
      'alshaya_click_collect.settings',
      'acq_commerce.currency',
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
   */
  private function addCacheTags($tags) {
    $this->cacheTags = array_unique(array_merge($this->cacheTags, $tags));
  }

  /**
   * Returns product configuration.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing configuration.
   */
  public function get() {
    $data = [];
    foreach ($this->getList() as $config => $name) {
      if (is_array($name)) {
        foreach ($name as $item) {
          $data[$config][$item] = $this->getConfig($config, $item);
        }
      }
      else {
        $data[$name] = $this->getAllConfigs($name);
      }
    }

    // Translate strings.
    array_walk_recursive($data, function (&$value) {
      if (is_string($value)) {
        // @codingStandardsIgnoreStart
        $value = new TranslatableMarkup($value);
        // @codingStandardsIgnoreEnd
      }
    });

    $response = new ResourceResponse($data);
    $cacheableMetadata = $response->getCacheableMetadata();
    $cacheableMetadata->addCacheTags($this->getCacheTags());
    $cacheableMetadata->addCacheContexts(['url.query_args']);
    $response->addCacheableDependency($cacheableMetadata);
    return $response;
  }

  /**
   * Get config for a given config name and key.
   *
   * @param string $name
   *   The config name.
   * @param string $key
   *   The config key.
   *
   * @return array|mixed
   *   The config.
   */
  private function getConfig($name, $key) {
    $config = $this->configFactory->get($name);

    $this->addCacheTags($config->getCacheTags());

    return $config->get($key);
  }

  /**
   * Get all items for a given config name.
   *
   * @param string $name
   *   The config name.
   *
   * @return array|mixed
   *   The config.
   */
  private function getAllConfigs($name) {
    $config = $this->configFactory->get($name);
    $configs = $config->getRawData();

    // Remove unwanted item.
    unset($configs['_core']);

    $this->addCacheTags($config->getCacheTags());

    return $configs;
  }

}
