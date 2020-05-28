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
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              SkuManager $sku_manager,
                              SkuImagesManager $sku_image_manager,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->skuManager = $sku_manager;
    $this->skuImageManager = $sku_image_manager;
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
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('config.factory')
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

    $entity = $vars['node'];
    $sku = $this->skuManager->getSkuForNode($entity);
    $sku_entity = SKU::loadFromSku($sku);
    $vars['sku'] = $sku_entity;

    // Get gallery data for the main product.
    if ($sku_entity instanceof SKUInterface) {
      $vars['#attached']['drupalSettings']['productInfo'][$sku]['rawGallery'] = $this->getGalleryVariables($sku_entity);
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
        'logo' => $vars['elements']['brand_logo']['#uri'],
        'title' => $vars['elements']['brand_logo']['#title'],
        'alt' => $vars['elements']['brand_logo']['#alt'],
      ];
    }

    $options = [];
    // Get gallery and combination data for product variants.
    if ($sku_entity->bundle() == 'configurable') {
      $product_tree = Configurable::deriveProductTree($sku_entity);
      $combinations = $product_tree['combinations'];
      $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['bySku'] = $combinations['by_sku'];
      foreach ($combinations['by_sku'] ?? [] as $child_sku => $combination) {
        $child = SKU::loadFromSku($child_sku);
        if (!$child instanceof SKUInterface) {
          continue;
        }
        $options = NestedArray::mergeDeepArray([$options, $this->skuManager->getCombinationArray($combination)], TRUE);
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['combinations'] = $options;
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['byAttribute'] = $combinations['by_attribute'];
        $vars['#attached']['drupalSettings']['configurableCombinations'][$sku]['configurables'] = $product_tree['configurables'];
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

}
