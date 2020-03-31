<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

use Drupal\Core\Entity\EntityInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\alshaya_acm_product\SkuImagesManager;

/**
 * Provides the default laypout for PDP.
 *
 * @PdpLayout(
 *   id = "teaser",
 *   label = @Translation("Teaser"),
 * )
 */
class TeaserLayout extends PdpLayoutBase implements ContainerFactoryPluginInterface {

  /**
   * The sku manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * The sku manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * TeaserLayout constructor.
   *
   * @param array $configuration
   *   Configuration data.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   The sku manager object.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   The sku images manager object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SkuManager $sku_manager, SkuImagesManager $sku_images_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
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
  public function getRenderArray(array &$build, EntityInterface $entity) {
    // Get the image.
    $build['image'] = [];
    $sku = $this->skuManager->getSkuForNode($entity);
    $sku_entity = SKU::loadFromSku($sku);
    $sku_media = $this->skuImagesManager->getFirstImage($sku_entity);
    $priceHelper = _alshaya_acm_product_get_price_helper();
    $sku_identifier = strtolower(Html::cleanCssIdentifier($sku));
    $product_settings = $this->configFactory->get('alshaya_acm_product.settings');

    if (!empty($sku_media['drupal_uri'])) {
      $build['image'] = $this->skuManager->getSkuImage($sku_media['drupal_uri'], $sku_entity->label(), 'product_teaser');
    }

    // Do not render VAT text along with price for teaser.
    $build['price_block'] = $priceHelper->getPriceBlockForSku($sku_entity, []);
    $build['price_block_identifier']['#markup'] = 'price-block-' . $sku_identifier;

    $build['labels'] = [
      '#theme' => 'product_labels',
      '#labels' => $this->skuManager->getLabels($sku_entity, 'plp'),
      '#sku' => $sku_identifier,
      '#mainsku' => $sku_identifier,
      '#type' => 'plp',
    ];

    // Do not show add to cart form for category carousel.
    // Show add to cart form only if config says so.
    // Show add to cart form if product is buyable.
    $class = $product_settings->get('show_cart_form_in_related') == 0 ? 'no-cart-form' : '';
    $build['show_cart_form']['#markup'] = $class;

    $build['sku_id'] = [
      '#markup' => $sku_entity->id(),
    ];

    $build['mobile_add_to_cart_form'] = [];
  }

}
