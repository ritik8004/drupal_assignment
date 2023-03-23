<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\alshaya_product_options\ProductOptionsHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Cache\Cache;
use Drupal\alshaya_acm_product\DeliveryOptionsHelper;

/**
 * Provides the default laypout for PDP.
 *
 * @PdpLayout(
 *   id = "magazine_v2",
 *   label = @Translation("Magazine 2.0"),
 * )
 */
class MagazineV2PdpLayout extends PdpLayoutBase implements ContainerFactoryPluginInterface {

  public const PDP_LAYOUT_MAGAZINE_V2 = 'pdp-magazine_v2';

  /**
   * The SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * The SKU Image Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImageManager;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Product Options Helper.
   *
   * @var \Drupal\alshaya_product_options\ProductOptionsHelper
   */
  private $optionsHelper;

  /**
   * Delivery Options helper.
   *
   * @var \Drupal\alshaya_acm_product\DeliveryOptionsHelper
   */
  protected $deliveryOptionsHelper;

  /**
   * Constructs a new MagazineV2PdpLayout.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   The SKU Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_image_manager
   *   The SKU Image Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\alshaya_product_options\ProductOptionsHelper $options_helper
   *   Product Options Helper.
   * @param \Drupal\alshaya_acm_product\DeliveryOptionsHelper $delivery_options_helper
   *   Delivery Options Helper.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              SkuManager $sku_manager,
                              SkuImagesManager $sku_image_manager,
                              ConfigFactoryInterface $config_factory,
                              ProductOptionsHelper $options_helper,
                              DeliveryOptionsHelper $delivery_options_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->skuManager = $sku_manager;
    $this->skuImageManager = $sku_image_manager;
    $this->configFactory = $config_factory;
    $this->optionsHelper = $options_helper;
    $this->deliveryOptionsHelper = $delivery_options_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('config.factory'),
      $container->get('alshaya_product_options.helper'),
      $container->get('alshaya_acm_product.delivery_options_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplateName(array &$suggestions, string $bundle) {
    $suggestions[] = match ($bundle) {
      'rcs_product' => 'node__rcs_product__full_magazine_v2',
        default => 'node__acq_product__full_magazine_v2',
    };
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderArray(array &$vars) {
    $vars['#attached']['library'][] = 'alshaya_spc/cart_utilities';
    $vars['#attached']['library'][] = 'alshaya_pdp_react/pdp_magazine_v2_layout';
    $vars['#attached']['library'][] = 'alshaya_white_label/magazine-layout-v2';
    $vars['#attached']['library'][] = 'alshaya_spc/googlemapapi';
    $vars['#attached']['library'][] = 'alshaya_seo_transac/gtm_pdp_magazine_v2';

    $entity = $vars['node'];
    // Get sharethis settings.
    if (isset($vars['elements']['sharethis'])) {
      $this->getShareThisSettings($vars);
    }

    // Get cnc config info.
    $this->getCncSettings($vars);

    if ($entity->bundle() != 'acq_product') {
      return;
    }

    $sku = $this->skuManager->getSkuForNode($entity);
    $sku_entity = SKU::loadFromSku($sku);
    $vars['sku'] = $sku_entity;

    if (!($sku_entity instanceof SKUInterface)) {
      throw new NotFoundHttpException();
    }

    // Get gallery data for the main product.
    $gallery = $this->getGalleryVariables($sku_entity);
    if (!empty($gallery)) {
      $vars['#attached']['drupalSettings']['productInfo'][$sku]['rawGallery'] = $gallery;
    }
    $max_sale_qty = 0;
    if ($this->configFactory->get('alshaya_acm.settings')->get('quantity_limit_enabled')) {
      // We will take lower value for quantity options from
      // available quantity and order limit.
      $plugin = $sku_entity->getPluginInstance();
      $max_sale_qty = $plugin->getMaxSaleQty($sku_entity->getSku());
    }
    $quantity = $this->skuManager->getStockQuantity($sku_entity);
    $vars['#attached']['drupalSettings']['productInfo'][$sku]['stockQty'] = (!empty($max_sale_qty) && ($quantity > $max_sale_qty)) ? $max_sale_qty : $quantity;

    // Get the product's buyable status.
    $is_product_buyable = alshaya_acm_product_is_buyable($sku_entity);
    $vars['#attached']['drupalSettings']['productInfo'][$sku]['is_product_buyable'] = $is_product_buyable;
    $express_delivery_config = \Drupal::config('alshaya_spc.express_delivery');
    $vars['#cache']['tags'] = Cache::mergeTags($vars['#cache']['tags'] ?? [], $express_delivery_config->getCacheTags());
    // Checking if express delivery enabled.
    if ($this->deliveryOptionsHelper->ifSddEdFeatureEnabled()) {
      $vars['#attached']['drupalSettings']['productInfo'][$sku]['expressDelivery'] = TRUE;
      // Show delivery options as per order in express delivery config.
      $delivery_options = alshaya_acm_product_get_delivery_options($sku);
      $vars['#attached']['drupalSettings']['productInfo'][$sku]['deliveryOptions'] = $delivery_options['values'];
      $vars['#attached']['drupalSettings']['productInfo'][$sku]['expressDeliveryClass'] = $delivery_options['express_delivery_applicable'] ? 'active' : 'in-active';
    }

    $bigTickectProduct = FALSE;
    // Get the product's bidTicket status.
    if ($sku_entity->hasField('attr_white_glove_delivery')) {
      $bigTickectProduct = (bool) $sku_entity->get('attr_white_glove_delivery')->getString();
      $vars['#attached']['drupalSettings']['productInfo'][$sku]['bigTickectProduct'] = TRUE;
    }
    // Set delivery options only if product is buyable.
    // Hide home delivery default options if express delivery enabled.
    if (($is_product_buyable && !($this->deliveryOptionsHelper->ifSddEdFeatureEnabled())) || ($is_product_buyable && $bigTickectProduct)) {
      // Check if home delivery is available for this product.
      if (alshaya_acm_product_available_home_delivery($sku)) {
        $home_delivery_config = alshaya_acm_product_get_home_delivery_config();
        $vars['#attached']['drupalSettings']['homeDelivery'] = $home_delivery_config;
      }
    }

    // Check if product is in stock.
    $stock_status = $this->skuManager->isProductInStock($sku_entity);
    $vars['#attached']['drupalSettings']['productInfo'][$sku]['stockStatus'] = $stock_status;

    // Get share this settings.
    if (isset($vars['elements']['sharethis'])) {
      $this->getShareThisSettings($vars);
    }

    // Get cnc config info.
    $this->getCncSettings($vars);

    // Get the product description.
    $vars['#attached']['drupalSettings']['productInfo'][$sku]['description'] = $vars['elements']['description'] ?? [];
    $short_desc = $this->skuManager->getShortDescription($sku_entity, 'full');
    $vars['#attached']['drupalSettings']['productInfo'][$sku]['shortDesc'] = $short_desc['value']['#markup'] ?? '';
    $vars['#attached']['drupalSettings']['productInfo'][$sku]['title'] = [
      'label' => $entity->label(),
    ];
    $vars['#attached']['drupalSettings']['productInfo'][$sku]['finalPrice'] = _alshaya_acm_format_price_with_decimal((float) $sku_entity->get('final_price')->getString());

    // Get the product brand logo.
    // @todo To be shifted in the specific brand module.
    if (!empty($vars['elements']['brand_logo'])) {
      $vars['#attached']['drupalSettings']['productInfo'][$sku]['brandLogo'] = [
        'logo' => file_create_url($vars['elements']['brand_logo']['#uri']),
        'title' => $vars['elements']['brand_logo']['#title'],
        'alt' => $vars['elements']['brand_logo']['#alt'],
      ];
    }

    $options = [];
    $values = [];

    // Set product label data.
    $this->getProductLabels($sku, $sku_entity, $vars);

    // Set vat text data.
    $vat_text = $this->skuManager->getVatText();
    $vars['#attached']['drupalSettings']['vatText'] = $vat_text;

    // Set promo data.
    if (isset($vars['elements']['promotions'])) {
      $vars['#attached']['drupalSettings']['productInfo'][$sku]['promotions'] = $vars['elements']['promotions']['#markup'];
    }

    // Get gallery and combination data for product variants.
    if ($sku_entity->bundle() == 'configurable') {
      $product_tree = Configurable::deriveProductTree($sku_entity);
      $combinations = $product_tree['combinations'];
      $product_tree['configurables'] = $this->disableUnavailableOptions($sku_entity, $product_tree['configurables']);
      $swatch_processed = FALSE;
      if ($stock_status) {
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['bySku'] = $combinations['by_sku'];
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['byAttribute'] = $combinations['by_attribute'];
      }
      $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'] = $product_tree['configurables'];

      // Prepare group and swatch attributes.
      $size_attributes = $this->configFactory
        ->get('alshaya_acm_product.pdp_modal_links')
        ->get('size_guide_attributes');
      $size_attributes = explode(',', $size_attributes);
      foreach ($product_tree['configurables'] as $key => $configurable) {
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['isGroup'] = FALSE;
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['isSwatch'] = FALSE;
        if (!$swatch_processed && in_array($key, $this->skuManager->getPdpSwatchAttributes())) {
          $swatch_processed = TRUE;
          $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['isSwatch'] = TRUE;
          foreach ($configurable['values'] as $value => $label) {
            $value_id = $label['value_id'];
            if (empty($value_id)) {
              continue;
            }

            $swatch_sku = $this->skuManager->getChildSkuFromAttribute($sku_entity, $key, $value_id);
            if ($swatch_sku instanceof SKU) {
              $swatch_image_url = $this->skuImageManager->getPdpSwatchImageUrl($swatch_sku);
              if ($swatch_image_url) {
                $swatch_image = file_url_transform_relative($swatch_image_url);
                $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['values'][$value]['swatch_image'] = $swatch_image;
              }
              // Get the product label for the swatch sku. This will be required
              // on the PDP layout when user selects the different variants.
              $this->getProductLabels($swatch_sku->getSku(), $swatch_sku, $vars);
            }
          }
        }
        elseif ($alternates = $this->optionsHelper->getSizeGroup($key)) {
          $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['isGroup'] = TRUE;
          $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['alternates'] = $alternates;
          $combinations = $this->skuManager->getConfigurableCombinations($sku_entity);
          $index = 0;
          foreach ($configurable['values'] as $value => $label) {
            $value_id = $label['value_id'];
            foreach ($combinations['attribute_sku'][$key][$value_id] ?? [] as $child_sku_code) {
              $child_sku = SKU::loadFromSku($child_sku_code, $sku_entity->language()->getId());

              if (!($child_sku instanceof SKU)) {
                continue;
              }

              $values[$index][$value_id] = $this->getAlternativeValues($alternates, $child_sku);
              $this->getProductLabels($child_sku_code, $child_sku, $vars);

            }
            $index++;
          }
          $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['values'] = $values;
        }
        // Check for the non-grouped valid size attributes.
        elseif (in_array($key, $size_attributes)) {
          $size_values = [];
          foreach ($configurable['values'] as $size_value) {
            $size_values[][$size_value['value_id']] = $size_value;
          }
          $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['values'] = $size_values;
        }
      }

      foreach ($combinations['by_sku'] ?? [] as $child_sku => $combination) {
        $child = SKU::loadFromSku($child_sku);
        if (!$child instanceof SKUInterface) {
          continue;
        }

        // Setting the main pdp gallery
        // if child gallery is empty.
        $variant_gallery = $this->getGalleryVariables($child);
        if (empty($variant_gallery)) {
          $variant_gallery = $gallery;
        }

        $options = NestedArray::mergeDeepArray([
          $options,
          $this->skuManager->getCombinationArray($combination),
        ], TRUE);
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['combinations'] = $options;
        // Get the first child from attribute_sku.
        $sorted_variants = array_values(array_values($combinations['attribute_sku'])[0])[0];
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['firstChild'] = reset($sorted_variants);
        $vars['#attached']['drupalSettings']['productInfo'][$sku]['variants'][$child_sku]['rawGallery'] = $variant_gallery;
        $vars['#attached']['drupalSettings']['productInfo'][$sku]['variants'][$child_sku]['finalPrice'] = _alshaya_acm_format_price_with_decimal((float) $child->get('final_price')->getString());

        if ($child_sku == reset($sorted_variants)) {
          $vars['#attached']['drupalSettings']['productInfo'][$sku]['rawGallery'] = $variant_gallery;
        }
      }

      // Get the first child from variants of selected parent.
      $selected_parent_child_skus = Configurable::getChildSkus($product_tree['parent']);
      $sorted_child_skus = [];
      if (!empty($combinations['attribute_sku']) && is_array($combinations['attribute_sku'])) {
        $attr_sku_combination = array_values($combinations['attribute_sku']);
        if (!empty($attr_sku_combination[0]) && is_array($attr_sku_combination[0])) {
          $sorted_child_skus = array_values($attr_sku_combination[0]);
        }
      }
      foreach ($sorted_child_skus ?? [] as $child_sku) {
        if (in_array($child_sku[0], $selected_parent_child_skus) && isset($product_tree['products'][$child_sku[0]])) {
          $vars['#attached']['drupalSettings']['configurableCombinations'][$sku_entity->getSku()]['firstChild'] = $child_sku[0];
          break;
        }
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getCotextFromPdpLayout($context, $pdp_layout) {
    return $context . '-' . $pdp_layout;
  }

  /**
   * Helper function to get gallery variables of the product.
   *
   * @return array
   *   The gallery array.
   */
  public function getGalleryVariables($sku_entity) {
    $sku = $sku_entity->getSku();
    $gallery = [];
    $media = $this->skuImageManager->getProductMedia($sku_entity, self::PDP_LAYOUT_MAGAZINE_V2, FALSE);
    if (!empty($media)) {
      $mediaItems = $this->skuImageManager->getThumbnailsFromMedia($media, FALSE);
      $thumbnails = $mediaItems['thumbnails'];
      // If thumbnails available.
      if (!empty($thumbnails)) {
        $pdp_gallery_pager_limit = $this->configFactory->get('alshaya_acm_product.settings')
          ->get('pdp_gallery_pager_limit');

        $pager_flag = (is_countable($thumbnails) ? count($thumbnails) : 0) > $pdp_gallery_pager_limit ? 'pager-yes' : 'pager-no';

        $gallery = [
          'sku' => $sku,
          'thumbnails' => $thumbnails,
          'pager_flag' => $pager_flag,
        ];

      }
    }
    return $gallery;
  }

  /**
   * Helper function to get share this settings.
   */
  public function getShareThisSettings(&$vars) {
    $sharethis = $vars['elements']['sharethis'];
    $sharethissettings = $sharethis['#attached']['drupalSettings'];
    $sharethissettings['sharethis']['content'] = $sharethis['#content']['st_spans'];
    $vars['#attached']['drupalSettings']['sharethis'] = $sharethissettings['sharethis'];
    $vars['#attached']['library'] = array_merge($vars['#attached']['library'], $sharethis['#attached']['library']);
  }

  /**
   * Helper function to get alternative group values of the variant.
   *
   * @return array
   *   The alternative array.
   */
  public function getAlternativeValues($alternates, $child) {
    $group_data = [];
    // Get all alternate labels from child sku.
    foreach ($alternates as $alternate => $alternate_label) {
      $attribute_code = 'attr_' . $alternate;
      $group_data[$alternate_label] = $child->get($attribute_code)->getString();
    }
    return $group_data;
  }

  /**
   * Helper function to get cnc config settings.
   */
  public function getCncSettings(&$vars) {
    $cc_config = $this->configFactory->get('alshaya_click_collect.settings');
    $cncFeatureStatus = $cc_config->get('feature_status') ?? 'enabled';
    $cnc_enabled = $cncFeatureStatus === 'enabled';
    $geolocation_config = $this->configFactory->get('geolocation_google_maps.settings');
    $store_finder_config = $this->configFactory->get('alshaya_stores_finder.settings');
    $vars['#attached']['drupalSettings']['clickNCollect']['cncEnabled'] = $cnc_enabled;
    $vars['#attached']['drupalSettings']['clickNCollect']['cncSubtitleAvailable'] = $cc_config->get('checkout_click_collect_available');
    $vars['#attached']['drupalSettings']['clickNCollect']['cncSubtitleUnavailable'] = $cc_config->get('checkout_click_collect_unavailable');
    $vars['#attached']['drupalSettings']['clickNCollect']['cncFormPlaceholder'] = $store_finder_config->get('store_search_placeholder');
    $vars['#attached']['drupalSettings']['clickNCollect']['countryCode'] = _alshaya_custom_get_site_level_country_code();
    $vars['#attached']['drupalSettings']['map']['google_api_key'] = $geolocation_config->get('google_map_api_key');
  }

  /**
   * Helper function to get pdp product label.
   */
  public function getProductLabels($sku, $sku_entity, &$vars) {
    $product_labels = $this->skuManager->getLabelsData($sku_entity, 'pdp');
    $vars['#attached']['drupalSettings']['productLabels'][$sku] = $product_labels;
  }

  /**
   * Returns the configurable options minus the disabled options.
   *
   * This function removes the configurable options which are disabled and
   * returns the remaining.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   The sku object.
   * @param array $configurables
   *   The configurables array.
   *
   * @return array
   *   The configurables array.
   *
   * @see \Drupal\alshaya_acm_product\SkuManager::disableUnavailableOptions()
   */
  public function disableUnavailableOptions(SKUInterface $sku, array $configurables) {
    if (!empty($configurables)) {
      $combinations = $this->skuManager->getConfigurableCombinations($sku);
      // Remove all options which are not available at all.
      foreach ($configurables as $index => $code) {
        foreach ($configurables[$index]['values'] as $key => $value) {
          if (isset($combinations['attribute_sku'][$index][$value['value_id']])) {
            continue;
          }
          unset($configurables[$index]['values'][$key]);
        }
      }
      return $configurables;
    }

    return [];
  }

}
