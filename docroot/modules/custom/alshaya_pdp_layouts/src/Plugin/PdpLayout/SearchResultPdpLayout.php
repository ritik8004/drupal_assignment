<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

use Drupal\Core\Entity\EntityInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Utility\Html;
use Drupal\alshaya_pdp_layouts\Plugin\PdpLayoutBase;
use Drupal\acq_sku\Entity\SKU;

/**
 * Provides the default laypout for PDP.
 *
 * @PdpLayout(
 *   id = "search_result",
 *   label = @Translation("Search Result"),
 * )
 */
class SearchResultPdpLayout extends PdpLayoutBase implements ContainerFactoryPluginInterface {

  /**
   * The sku manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * SearchResultPdpLayout constructor.
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
    $build['labels'] = [
      '#theme' => 'product_labels',
      '#labels' => $this->skuManager->getLabels($sku_entity, 'plp'),
      '#sku' => $sku_identifier,
      '#mainsku' => $sku_identifier,
      '#type' => 'plp',
    ];
    return $build;
  }

}
