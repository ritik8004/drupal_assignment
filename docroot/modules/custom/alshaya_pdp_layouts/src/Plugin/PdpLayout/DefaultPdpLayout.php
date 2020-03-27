<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

use Drupal\Core\Entity\EntityInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\node\NodeInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Component\Utility\Html;
use Drupal\alshaya_pdp_layouts\Plugin\PdpLayoutBase;

/**
 * Provides the default laypout for PDP.
 *
 * @PdpLayout(
 *   id = "full",
 *   label = @Translation("Default Layout"),
 * )
 */
class DefaultPdpLayout extends PdpLayoutBase implements ContainerFactoryPluginInterface {

  /**
   * The sku manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * DefaultPdpLayout constructor.
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
      $container->get('alshaya_acm_product.skumanager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderArray(array &$build, EntityInterface $entity) {
    $sku = $this->skuManager->getSkuForNode($entity, TRUE);
    $sku_entity = SKU::loadFromSku($sku);
    $sku_identifier = strtolower(Html::cleanCssIdentifier($sku));
    if (empty($sku)) {
      throw new NotFoundHttpException();
    }

    $skuNode = $this->skuManager->getDisplayNode($sku, FALSE);

    // Show 404 if current node is color node.
    // Redirecting to proper node may make it indexed and we don't want
    // this to be indexed or known as valid url in any case.
    if ($skuNode instanceof NodeInterface && $skuNode->id() != $entity->id()) {
      throw new NotFoundHttpException();
    }

    // Do not display PDP for free gift.
    if ($this->skuManager->isSkuFreeGift($sku_entity)) {
      throw new NotFoundHttpException();
    }

    // This is required to allow showing different gallery on page load
    // when user clicked on swatch.
    $build['#cache']['contexts'][] = 'url.query_args:selected';

    if (!$this->skuManager->isProductInStock($sku_entity)) {
      $build['#attributes']['class'][] = 'product-out-of-stock';
    }

    $build['price_block_identifier']['#markup'] = 'price-block-' . $sku_identifier;

    $build['brand_logo'] = alshaya_acm_product_get_brand_logo($sku_entity);

    $build['item_code']['#markup'] = $sku;

    // If delivery available for the SKU.
    if (!empty($delivery_data = _alshaya_acm_product_get_delivery_link($sku_entity))) {
      $build['delivery_link'] = [
        '#markup' => $delivery_data['link'],
      ];
    }

    // Initialise home delivery variable.
    $build['home_delivery'] = [];

    // Display delivery options only if product is buyable.
    if (alshaya_acm_product_is_buyable($sku_entity)) {
      // Check if home delivery is available for this product.
      if (alshaya_acm_product_available_home_delivery($sku)) {
        $home_delivery_config = alshaya_acm_product_get_home_delivery_config();

        // @TODO: Next day delivery not available for now.
        $build['home_delivery'] = [
          '#theme' => 'pdp_delivery_option',
          '#title' => $home_delivery_config['title'],
          '#subtitle' => $home_delivery_config['subtitle'],
          '#options' => [
            'standard_title' => $home_delivery_config['standard_title'],
            'standard_subtitle' => $home_delivery_config['standard_subtitle'],
          ],
        ];
      }
    }

    return $build;
  }

}
