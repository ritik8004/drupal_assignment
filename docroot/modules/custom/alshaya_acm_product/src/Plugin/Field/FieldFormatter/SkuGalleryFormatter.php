<?php

namespace Drupal\alshaya_acm_product\Plugin\Field\FieldFormatter;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\Field\FieldFormatter\SKUFieldFormatter;
use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Site\Settings;
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
   * Flag to specify if we need to release memory or not.
   *
   * @var bool
   */
  public static $releaseMemory = FALSE;

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
   * Module installer service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The memory cache.
   *
   * @var \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface
   */
  protected $memoryCache;

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
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   Module handler service.
   * @param \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface $memory_cache
   *   The memory cache.
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
                              SkuPriceHelper $price_helper,
                              ModuleHandler $module_handler,
                              MemoryCacheInterface $memory_cache) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->skuManager = $skuManager;
    $this->skuImagesManager = $skuImagesManager;
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->priceHelper = $price_helper;
    $this->moduleHandler = $module_handler;
    $this->memoryCache = $memory_cache;
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

    // Disable alshaya_color_split hook calls.
    SkuManager::$colorSplitMergeChildren = FALSE;
    $context = 'search';

    $skus = [];
    $elements = [];
    $color = '';

    // Fetch Product in which this sku is referenced.
    $entity_adapter = $items->first()->getParent()->getParent();
    if (($entity_adapter instanceof EntityAdapter) &&
      ($this->skuManager->isListingModeNonAggregated())) {
      $currentLangCode = $this->languageManager->getCurrentLanguage()->getId();

      /** @var \Drupal\node\NodeInterface $colorNode */
      $colorNode = $entity_adapter->getValue();

      if ($colorNode->hasTranslation($currentLangCode)) {
        $colorNode = $colorNode->getTranslation($currentLangCode);
      }

      $color = $colorNode->get('field_product_color')->getString();
    }

    $display_settings = $this->configFactory->get('alshaya_acm_product.display_settings');

    foreach ($items as $delta => $item) {
      /** @var \Drupal\acq_sku\Entity\SKU $sku */
      $sku = $this->viewValue($item);
      $skus[$delta] = $sku;
      if ($sku instanceof SKU) {
        $node = $this->skuManager->getDisplayNode($sku, FALSE);

        if (!($node instanceof Node)) {
          continue;
        }

        $cache_tags = ['node:' . $node->id()];
        $product_base_url = $product_url = $node->url();
        $product_label = $node->getTitle();

        $all_galleries = [];
        if (empty($color) && $display_settings->get('show_color_images_on_filter')) {
          $all_galleries = $this->skuImagesManager->getAllColorGallery($sku);
        }

        if (empty($all_galleries)) {
          try {
            $sku_for_gallery = $this->skuImagesManager->getSkuForGalleryWithColor($sku, $color);

            if (!($sku_for_gallery instanceof SKUInterface)) {
              $sku_for_gallery = $sku;
            }

            $sku_gallery = $this->skuImagesManager->getGallery($sku_for_gallery, 'search', $product_label);

            // Do not add selected param if we are using parent sku itself for
            // gallery. This is normal for PB, MC, etc.
            if (($sku_for_gallery->id() != $sku->id()) &&
              (!$this->moduleHandler->moduleExists('alshaya_color_split'))) {
              $product_url .= '?selected=' . $sku_for_gallery->id();
            }
          }
          catch (\Exception $e) {
            $sku_gallery = [];
          }
        }

        $promotions = $this->skuManager->getPromotionsForSearchViewFromSkuId($sku);
        foreach ($promotions as $key => $promotion) {
          $cache_tags[] = 'node:' . $key;
        }

        $stock_placeholder = NULL;
        if (alshaya_acm_product_is_buyable($sku) && !$this->skuManager->isProductInStock($sku)) {
          $stock_placeholder = [
            '#markup' => '<div class="out-of-stock"><span class="out-of-stock">' . $this->t('out of stock') . '</span></div>',
          ];
        }

        $elements[$delta] = [
          '#theme' => 'sku_teaser',
          '#gallery' => $sku_gallery ?? [],
          '#all_galleries' => $all_galleries,
          '#product_url' => $product_url,
          '#product_base_url' => $product_base_url,
          '#product_label' => $product_label,
          '#promotions' => $promotions,
          '#stock_placeholder' => $stock_placeholder,
          '#cache' => [
            'tags' => $cache_tags,
          ],
          '#color' => $color,
        ];

        $elements[$delta]['#price_block'] = $this->priceHelper->getPriceBlockForSku($sku, ['color' => $color]);

        $sku_identifier = strtolower(Html::cleanCssIdentifier($sku->getSku()));
        $elements[$delta]['#price_block_identifier']['#markup'] = 'price-block-' . $sku_identifier;
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
      if (empty($element['#all_galleries']) && empty($element['#gallery']['#mainImage'])) {
        $default_image = $this->skuImagesManager->getProductDefaultImage();
        if ($default_image) {
          $main_image = $this->skuManager->getSkuImage($default_image->getFileUri(), '', 'product_listing');
          $elements[$delta]['#gallery']['#mainImage'] = $main_image;
          $elements[$delta]['#gallery']['#class'] = 'product-default-image';
        }
      }
    }

    $this->releaseMemory();

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
      $container->get('alshaya_acm_product.price_helper'),
      $container->get('module_handler'),
      $container->get('entity.memory_cache')
    );
  }

  /**
   * Wrapper function to release memory.
   */
  protected function releaseMemory() {
    if (Settings::get('release_memory_on_listing', 0) && self::$releaseMemory) {
      $this->memoryCache->deleteAll();
      drupal_static_reset();
    }
  }

}
