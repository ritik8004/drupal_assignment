<?php

namespace Drupal\alshaya_acm_product\Plugin\Field\FieldFormatter;

use Drupal\acq_sku\Plugin\Field\FieldFormatter\SKUFieldFormatter;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'sku_carousel' formatter.
 *
 * @FieldFormatter(
 *   id = "sku_carousel",
 *   label = @Translation("SKU Carousel"),
 *   field_types = {
 *     "sku"
 *   }
 * )
 */
class SkuCarousel extends SKUFieldFormatter implements ContainerFactoryPluginInterface {

  /**
   * The Sku Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * Node View Builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  private $nodeViewBuilder;

  /**
   * SkuCarousel constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   Sku Manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct($plugin_id,
                              $plugin_definition,
                              FieldDefinitionInterface $field_definition,
                              array $settings,
                              $label,
                              $view_mode,
                              array $third_party_settings,
                              SkuManager $sku_manager,
                              EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->skuManager = $sku_manager;
    $this->nodeViewBuilder = $entity_type_manager->getViewBuilder('node');
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \InvalidArgumentException
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $values = $items->getValue();
    if (empty($values)) {
      return [];
    }

    $skus = array_column($values, 'value');

    $elements = [];
    foreach ($skus as $sku_code) {
      $node = $this->skuManager->getDisplayNode($sku_code);
      if (!($node instanceof NodeInterface)) {
        continue;
      }

      $elements[$node->id()] = $this->nodeViewBuilder->view($node, $this->getSetting('view_mode'));
    }

    return $elements;
  }

}
