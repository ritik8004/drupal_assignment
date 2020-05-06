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
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;

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
   * The Event Dispatcher.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $eventDispatcher;

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
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $event_dispatcher
   *   Event Dispatcher object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              SkuManager $sku_manager,
                              SkuImagesManager $sku_image_manager,
                              ConfigFactoryInterface $config_factory,
                              ContainerAwareEventDispatcher $event_dispatcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->skuManager = $sku_manager;
    $this->skuImageManager = $sku_image_manager;
    $this->configFactory = $config_factory;
    $this->eventDispatcher = $event_dispatcher;
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
      $container->get('event_dispatcher'),
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

          $vars['#attached']['drupalSettings']['pdpGallery'][$sku] = $gallery;

          // Get the product description.
          $event = new PreprocessMagazineEvent($vars);
          $this->eventDispatcher->dispatch(PreprocessMagazineEvent::EVENT_NAME, $event);
          $product_description = $event->getVariables();
          $vars['#attached']['drupalSettings']['pdpGallery'][$sku]['description'] = $product_description['description'];
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

}
