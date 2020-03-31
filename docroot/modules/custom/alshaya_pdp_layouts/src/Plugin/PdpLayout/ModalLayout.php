<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

use Drupal\Core\Entity\EntityInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Component\Utility\Html;

/**
 * Provides the default laypout for PDP.
 *
 * @PdpLayout(
 *   id = "modal",
 *   label = @Translation("Modal"),
 * )
 */
class ModalLayout extends PdpLayoutBase implements ContainerFactoryPluginInterface {

  /**
   * The sku manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * ModalLayout constructor.
   *
   * @param array $configuration
   *   Configuration data.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   The sku manager object.
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
  public function getRenderArray(array &$build, EntityInterface $entity) {
    $sku = $this->skuManager->getSkuForNode($entity);
    $sku_entity = SKU::loadFromSku($sku);
    $sku_identifier = strtolower(Html::cleanCssIdentifier($sku));
    // Attach libraries.
    $build['#attached']['library'][] = 'alshaya_product_zoom/cloud_zoom_pdp_gallery';

    $build['price_block_identifier']['#markup'] = 'price-block-' . $sku_identifier;
    $build['item_code']['#markup'] = $sku;
    $build['brand_logo'] = alshaya_acm_product_get_brand_logo($sku_entity);

  }

}
