<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_commerce\SKUInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\Html;
use Drupal\alshaya_acm_product\SkuManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\alshaya_product_options\ProductOptionsHelper;

/**
 * Provides the default laypout for PDP.
 *
 * @PdpLayout(
 *   id = "magazine_v2",
 *   label = @Translation("Magazine 2.0"),
 * )
 */
class MagazineV2PdpLayout extends PdpLayoutBase implements ContainerFactoryPluginInterface {

  const PDP_LAYOUT_MAGAZINE_V2 = 'pdp-magazine_v2';

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
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              SkuManager $sku_manager,
                              SkuImagesManager $sku_image_manager,
                              ConfigFactoryInterface $config_factory,
                              ProductOptionsHelper $options_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->skuManager = $sku_manager;
    $this->skuImageManager = $sku_image_manager;
    $this->configFactory = $config_factory;
    $this->optionsHelper = $options_helper;
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
      $container->get('alshaya_product_options.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplateName(array &$suggestions) {
    $suggestions[] = 'node__acq_product__full_magazine_v2';
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderArray(array &$vars) {
    $vars['#attached']['library'][] = 'alshaya_pdp_react/pdp_magazine_v2_layout';
    $vars['#attached']['library'][] = 'alshaya_white_label/magazine-layout-v2';
    $vars['#attached']['library'][] = 'alshaya_spc/cart_utilities';

    $entity = $vars['node'];
    $sku = $this->skuManager->getSkuForNode($entity);
    $sku_entity = SKU::loadFromSku($sku);
    $vars['sku'] = $sku_entity;

    // Get gallery data for the main product.
    if ($sku_entity instanceof SKUInterface) {
      $vars['#attached']['drupalSettings']['productInfo'][$sku]['rawGallery'] = $this->getGalleryVariables($sku_entity);
      $max_sale_qty = 0;
      if ($this->configFactory->get('alshaya_acm.settings')->get('quantity_limit_enabled')) {
        // We will take lower value for quantity options from
        // available quantity and order limit.
        $plugin = $sku_entity->getPluginInstance();
        $max_sale_qty = $plugin->getMaxSaleQty($sku_entity->getSku());
      }
      $quantity = $this->skuManager->getStockQuantity($sku_entity);
      $vars['#attached']['drupalSettings']['productInfo'][$sku]['stockQty'] = (!empty($max_sale_qty) && ($quantity > $max_sale_qty)) ? $max_sale_qty : $quantity;
    }

    // Get the product description.
    $vars['#attached']['drupalSettings']['productInfo'][$sku]['description'] = $vars['elements']['description'];
    $vars['#attached']['drupalSettings']['productInfo'][$sku]['shortDesc'] = strip_tags($vars['elements']['short_desc']['value']['#markup']);
    $vars['#attached']['drupalSettings']['productInfo'][$sku]['title'] = [
      'label' => $entity->label(),
    ];
    $vars['#attached']['drupalSettings']['productInfo'][$sku]['finalPrice'] = _alshaya_acm_format_price_with_decimal((float) $sku_entity->get('final_price')->getString());

    // Get the product brand logo.
    // Todo: To be shifted in the specific brand module.
    if (!empty($vars['elements']['brand_logo'])) {
      $vars['#attached']['drupalSettings']['productInfo'][$sku]['brandLogo'] = [
        'logo' => file_create_url($vars['elements']['brand_logo']['#uri']),
        'title' => $vars['elements']['brand_logo']['#title'],
        'alt' => $vars['elements']['brand_logo']['#alt'],
      ];
    }

    $options = [];
    $values = [];

    // Get gallery and combination data for product variants.
    if ($sku_entity->bundle() == 'configurable') {
      $product_tree = Configurable::deriveProductTree($sku_entity);
      $combinations = $product_tree['combinations'];
      $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['bySku'] = $combinations['by_sku'];
      $swatch_processed = FALSE;

      $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['byAttribute'] = $combinations['by_attribute'];
      $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'] = $product_tree['configurables'];

      // Get size guide link information.
      $field_category = $entity->get('field_category')->first();
      if (!empty($field_category)) {
        $category = $field_category->entity;
        $size_guide = _alshaya_acm_product_get_size_guide_info($category);
      }

      // Prepare group and swatch attributes.
      foreach ($product_tree['configurables'] as $key => $configurable) {
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['isGroup'] = FALSE;
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['isSwatch'] = FALSE;
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['isSize'] = FALSE;
        if (!$swatch_processed && in_array($key, $this->skuManager->getPdpSwatchAttributes())) {
          $swatch_processed = TRUE;
          $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['isSwatch'] = TRUE;
          foreach ($configurable['values'] as $value => $label) {
            if (empty($value)) {
              continue;
            }

            $swatch_sku = $this->skuManager->getChildSkuFromAttribute($sku_entity, $key, $value);
            if ($swatch_sku instanceof SKU) {
              $swatch_image_url = $this->skuImageManager->getPdpSwatchImageUrl($swatch_sku);
              if ($swatch_image_url) {
                $swatch_image = file_url_transform_relative($swatch_image_url);
                $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['values'][$value]['swatch_image'] = $swatch_image;
              }
            }
          }
        }
        elseif ($alternates = $this->optionsHelper->getSizeGroup($key)) {
          $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['isGroup'] = TRUE;
          $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['alternates'] = $alternates;
          $combinations = $this->skuManager->getConfigurableCombinations($sku_entity);
          foreach ($configurable['values'] as $value => $label) {
            foreach ($combinations['attribute_sku'][$key][$value] ?? [] as $child_sku_code) {
              $child_sku = SKU::loadFromSku($child_sku_code, $sku_entity->language()->getId());

              if (!($child_sku instanceof SKU)) {
                continue;
              }

              $values[$value] = $this->getAlternativeValues($alternates, $child_sku);
            }

          }
          $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['values'] = $values;
        }

        // Set size guide information in drupal settings.
        if (isset($size_guide['link'])) {
          if (in_array($key, $size_guide['attributes'])) {
            $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['isSize'] = TRUE;
            $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'][$key]['sizeGuide'] = $size_guide['link'];
          }
        }
      }

      foreach ($combinations['by_sku'] ?? [] as $child_sku => $combination) {
        $child = SKU::loadFromSku($child_sku);
        if (!$child instanceof SKUInterface) {
          continue;
        }

        $options = NestedArray::mergeDeepArray([$options, $this->skuManager->getCombinationArray($combination)], TRUE);
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['combinations'] = $options;
        // Get the first child from attribute_sku.
        $sorted_variants = array_values(array_values($combinations['attribute_sku'])[0])[0];
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['firstChild'] = reset($sorted_variants);
        $vars['#attached']['drupalSettings']['productInfo'][$sku]['variants'][$child_sku]['rawGallery'] = $this->getGalleryVariables($child);
        $vars['#attached']['drupalSettings']['productInfo'][$sku]['variants'][$child_sku]['finalPrice'] = _alshaya_acm_format_price_with_decimal((float) $child->get('final_price')->getString());
        if ($child_sku == reset($sorted_variants)) {
          $vars['#attached']['drupalSettings']['productInfo'][$sku]['rawGallery'] = $this->getGalleryVariables($child);
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

        $pager_flag = count($thumbnails) > $pdp_gallery_pager_limit ? 'pager-yes' : 'pager-no';

        $sku_identifier = Unicode::strtolower(Html::cleanCssIdentifier($sku_entity->getSku()));

        $labels = [
          'labels' => $this->skuManager->getLabels($sku_entity, 'pdp'),
          'sku' => $sku_identifier,
          'mainsku' => $sku_identifier,
          'type' => 'pdp',
        ];

        $gallery = [
          'sku' => $sku,
          'thumbnails' => $thumbnails,
          'pager_flag' => $pager_flag,
          'labels' => $labels,
          'lazy_load_placeholder' => $this->configFactory->get('alshaya_master.settings')->get('lazy_load_placeholder'),
        ];

      }
    }
    return $gallery;
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

}
