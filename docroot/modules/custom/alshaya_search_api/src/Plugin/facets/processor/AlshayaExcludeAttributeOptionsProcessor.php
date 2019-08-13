<?php

namespace Drupal\alshaya_search_api\Plugin\facets\processor;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\facets\Plugin\facets\processor\ExcludeSpecifiedItemsProcessor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Exclude unwanted attribute options from facet items.
 *
 * @FacetsProcessor(
 *   id = "alshaya_exclude_attribute_options",
 *   label = @Translation("Alshaya exclude attribute options"),
 *   description = @Translation("Exclude unwanted attribute options from the facet items (Only for PLP)."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class AlshayaExcludeAttributeOptionsProcessor extends ExcludeSpecifiedItemsProcessor implements ContainerFactoryPluginInterface {

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaRemoveCurrentTermProcessor constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'exclude' => implode(',', $this->configFactory->get('alshaya_acm_product.settings')->get('excluded_attribute_options')),
      'regex' => 0,
    ];
  }

}
