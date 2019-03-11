<?php

namespace Drupal\alshaya_acm_product\Plugin\Field\FieldFormatter;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\Field\FieldFormatter\SKUFieldFormatter;
use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
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
   * SKU Images Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Price Helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuPriceHelper
   */
  protected $priceHelper;

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
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $skuImagesManager
   *   SKU Images Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Sku Manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   * @param \Drupal\alshaya_acm_product\Service\SkuPriceHelper $price_helper
   *   Price Helper.
   */
  public function __construct($plugin_id,
                              $plugin_definition,
                              FieldDefinitionInterface $field_definition,
                              array $settings,
                              $label,
                              $view_mode,
                              array $third_party_settings,
                              SkuManager $skuManager,
                              SkuImagesManager $skuImagesManager,
                              ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $language_manager,
                              SkuPriceHelper $price_helper) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->skuManager = $skuManager;
    $this->skuImagesManager = $skuImagesManager;
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->priceHelper = $price_helper;
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
    if (empty($items->getValue())) {
      return [];
    }

    $context = 'search';
    $skus = [];

    $elements = [];
    $product_url = $product_base_url = $product_label = '';

    $color = '';

    // Fetch Product in which this sku is referenced.
    $entity_adapter = $items->first()->getParent()->getParent();
    if (($entity_adapter instanceof EntityAdapter) &&
      ($this->skuManager->isListingModeNonAggregated()) {
      $currentLangCode = $this->languageManager->getCurrentLanguage()->getId();

      /** @var \Drupal\node\NodeInterface $colorNode */
      $colorNode = $entity_adapter->getValue();

      if ($colorNode->hasTranslation($currentLangCode)) {
        $colorNode = $colorNode->getTranslation($currentLangCode);
      }

      $color = $colorNode->get('field_product_color')->getString();
    }

    foreach ($items as $delta => $item) {
      /** @var \Drupal\acq_sku\Entity\SKU $sku */
      $sku = $this->viewValue($item);
      $skus[$delta] = $sku;
      if ($sku instanceof SKU) {
        $node = $this->skuManager->getDisplayNode($sku, FALSE);

        if (!($node instanceof Node)) {
          continue;
        }

        $product_base_url = $product_url = $node->url();
        $product_label = $node->getTitle();

        try {
          $sku_for_gallery = $this->skuImagesManager->getSkuForGalleryWithColor($sku, $color);

          if (!($sku_for_gallery instanceof SKUInterface)) {
            $sku_for_gallery = $sku;
          }

          $sku_gallery = $this->skuImagesManager->getGallery($sku_for_gallery, 'search', $product_label);

          // Do not add selected param if we are using parent sku itself for
          // gallery. This is normal for PB, MC, etc.
          if (($sku_for_gallery->id() != $sku->id()) &&
            ($this->skuManager->isListingModeNonAggregated()) {
            $product_url .= '?selected=' . $sku_for_gallery->id();
          }
        }
        catch (\Exception $e) {
          $sku_gallery = [];
        }

        $promotions = $this->skuManager->getPromotionsForSearchViewFromSkuId($sku);

        $promotion_cache_tags = [];
        foreach ($promotions as $key => $promotion) {
          $promotion_cache_tags[] = 'node:' . $key;
        }

        $stock_placeholder = NULL;

        if (alshaya_acm_product_is_buyable($sku) && !$this->skuManager->isProductInStock($sku)) {
          $stock_placeholder = [
            '#markup' => '<div class="out-of-stock"><span class="out-of-stock">' . t('out of stock') . '</span></div>',
          ];
        }

        $elements[$delta] = [
          '#theme' => 'sku_teaser',
          '#gallery' => $sku_gallery,
          '#product_url' => $product_url,
          '#product_base_url' => $product_base_url,
          '#product_label' => $product_label,
          '#promotions' => $promotions,
          '#stock_placeholder' => $stock_placeholder,
          '#cache' => [
            'tags' => array_merge($promotion_cache_tags, $sku->getCacheTags()),
          ],
          '#color' => $color,
        ];

        $elements[$delta]['#price_block'] = $this->priceHelper->getPriceBlockForSku($sku, ['color' => $color]);

        $sku_identifier = strtolower(Html::cleanCssIdentifier($sku->getSku()));
        $elements[$delta]['#price_block_identifier']['#markup'] = 'price-block-' . $sku_identifier;

        $elements[$delta]['#attached']['library'][] = 'alshaya_acm_product/stock_check';

        if ($this->configFactory->get('alshaya_acm_product.display_settings')->get('color_swatches_show_product_image')) {
          $elements[$delta]['#attached']['library'][] = 'alshaya_white_label/plp-swatch-hover';
        }

        $elements[$delta]['#attached']['library'][] = 'alshaya_acm_product/sku_gallery_format';
      }
    }

    foreach ($elements as $delta => &$element) {
      $sku = $skus[$delta];

      if ($sku instanceof SKUInterface) {
        // Invoke the alter hook to allow all modules to update the element.
        \Drupal::moduleHandler()->alter(
          'alshaya_acm_product_build', $element, $sku, $context
        );
      }
    }

    foreach ($elements as $delta => $element) {
      // If main image is empty.
      if (empty($element['#gallery']['#mainImage'])) {
        $default_image = $this->skuImagesManager->getProductDefaultImage();
        if ($default_image) {
          $main_image = $this->skuManager->getSkuImage(['file' => $default_image], '291x288');
          $elements[$delta]['#gallery']['#mainImage'] = $main_image;
          $elements[$delta]['#gallery']['#class'] = 'product-default-image';
        }
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
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('alshaya_acm_product.price_helper')
    );
  }

}
