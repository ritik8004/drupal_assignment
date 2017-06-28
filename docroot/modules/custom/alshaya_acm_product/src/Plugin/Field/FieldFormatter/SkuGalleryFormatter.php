<?php

namespace Drupal\alshaya_acm_product\Plugin\Field\FieldFormatter;

use Drupal\acq_sku\Plugin\Field\FieldFormatter\SKUFieldFormatter;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'sku_gallery_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "sku_gallery_formatter",
 *   label = @Translation("Sku gallery formatter"),
 *   field_types = {
 *     "sku"
 *   }
 * )
 */
class SkuGalleryFormatter extends SKUFieldFormatter implements ContainerFactoryPluginInterface {

  /**
   * The Sku Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * SkuGalleryFormatter constructor.
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
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager service.
   */
  public function __construct($plugin_id,
                              $plugin_definition,
                              FieldDefinitionInterface $field_definition,
                              array $settings,
                              $label,
                              $view_mode,
                              array $third_party_settings,
                              SkuManager $skuManager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->skuManager = $skuManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Implement default settings.
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      // Implement settings form.
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \InvalidArgumentException
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $product_url = $product_label = '';

    // Fetch Product in which this sku is referenced.
    $entity_adapter = $items->first()->getParent()->getParent();
    if ($entity_adapter instanceof EntityAdapter) {
      $node = $entity_adapter->getValue();
      if ($node instanceof Node) {
        $product_url = $node->url();
        $product_label = $node->getTitle();
      }
    }

    foreach ($items as $delta => $item) {
      /** @var \Drupal\acq_sku\Entity\SKU $sku */
      $sku = $this->viewValue($item);

      // Get the image.
      $build['image_url'] = [];
      $sku_media = $this->skuManager->getSkuMedia($sku);
      $search_main_image = $thumbnails = [];

      // Loop through all media items and prepare thumbnails array.
      foreach ($sku_media as $key => $media_item) {
        // For now we are displaying only image slider on search results page
        // and PLP.
        if ($media_item['media_type'] === 'image') {
          if (empty($search_main_image)) {
            $search_main_image = $this->skuManager->getSkuImage($media_item, '291x288');
          }

          $thumbnails[] = $this->skuManager->getSkuImage($media_item, '59x60', '291x288');
        }
      }

      $promotions = $this->skuManager->getPromotionsFromSkuId($sku, TRUE);

      if (!empty($promotions)) {
        $promotions = [
          '#markup' => implode(', ', $promotions),
        ];
      }

      $sku_gallery = [
        '#theme' => 'alshaya_search_gallery',
        '#mainImage' => $search_main_image,
        '#thumbnails' => $thumbnails,
        '#attached' => [
          'library' => [
            'alshaya_search/alshaya_search',
          ],
        ],
      ];

      $elements[$delta] = [
        '#theme' => 'sku_teaser',
        '#gallery' => $sku_gallery,
        '#product_url' => $product_url,
        '#product_label' => $product_label,
        '#promotions' => $promotions,
        '#cache' => [
          'tags' => ['sku:' . $sku->id()],
        ],
      ];

      $this->skuManager->buildPrice($elements[$delta], $sku);

      $elements[$delta]['#price'] = isset($elements[$delta]['price']) ? $elements[$delta]['price'] : '';
      $elements[$delta]['#final_price'] = isset($elements[$delta]['final_price']) ? $elements[$delta]['final_price'] : '';
      $elements[$delta]['#discount'] = isset($elements[$delta]['discount']) ? $elements[$delta]['discount'] : '';

      if (!alshaya_acm_is_product_in_stock($sku)) {
        $elements[$delta]['#out_of_stock'] = [
          '#markup' => '<span>' . $this->t('out of stock') . '</span>',
        ];
      }
    }

    return $elements;
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
      $container->get('alshaya_acm_product.skumanager')
    );
  }

}
