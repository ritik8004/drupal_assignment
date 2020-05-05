<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

use Drupal\alshaya_pdp_layouts\Event\PreprocessMagazineEvent;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_commerce\SKUInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\Html;
use Drupal\alshaya_acm_product\SkuManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SkuManager $sku_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->skuManager = $sku_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_acm_product.skumanager')
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

    $entity = $vars['node'];
    $sku = $this->skuManager->getSkuForNode($entity);
    $sku_entity = SKU::loadFromSku($sku);
    $vars['sku'] = $sku_entity;
    $gallery = [];

    if ($sku_entity instanceof SKUInterface) {
      $sku_image_manager = \Drupal::service('alshaya_acm_product.sku_images_manager');
      $media = $sku_image_manager->getProductMedia($sku_entity, self::PDP_LAYOUT_MAGAZINE_V2, FALSE);
      if (!empty($media)) {
        $mediaItems = $sku_image_manager->getThumbnailsFromMedia($media, FALSE);
        $thumbnails = $mediaItems['thumbnails'];
        // If thumbnails available.
        if (!empty($thumbnails)) {
          $pdp_gallery_pager_limit = \Drupal::config('alshaya_acm_product.settings')
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
            'lazy_load_placeholder' => \Drupal::config('alshaya_master.settings')->get('lazy_load_placeholder'),
          ];

          $vars['#attached']['drupalSettings']['pdpGallery'][$sku] = $gallery;

          // Get the product description.
          $event = new PreprocessMagazineEvent($vars);
          $event_dispatcher = \Drupal::service('event_dispatcher');
          $event_dispatcher->dispatch(PreprocessMagazineEvent::EVENT_NAME, $event);
          $product_description = $event->getVariables();
          $vars['#attached']['drupalSettings']['pdpGallery'][$sku]['description'] = $product_description['description'];
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getContextFromPluginId($context, $pdp_layout) {
    return $context . '-' . $pdp_layout;
  }

}
