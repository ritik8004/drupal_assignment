<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\NodeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaSpcController.
 */
class AlshayaSpcController extends ControllerBase {

  /**
   * Sku manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaSpcController constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   Sku manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(SkuManager $sku_manager,
                              ModuleHandlerInterface $module_handler) {
    $this->skuManager = $sku_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('module_handler')
    );
  }

  /**
   * Get the sku details.
   *
   * @param string $sku
   *   SKU string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function skuDetails(string $sku) {
    $data = [];
    $sku_entity = SKU::loadFromSku($sku);

    // If sku entity available in drupal and is of type simple.
    if ($sku_entity instanceof SKU
      && $sku_entity->bundle() == 'simple') {
      $data[$sku]['label'] = $sku_entity->label();
      $data[$sku]['promotions'] = '';
      $data[$sku]['price'] = $sku_entity->get('price')->first()->getValue()['value'];
      $data[$sku]['final_price'] = $sku_entity->get('final_price')->first()->getValue()['value'];
      $data[$sku]['stock'] = $this->skuManager->getStockQuantity($sku_entity);

      // Prepare attributes data to shown.
      $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
      $data[$sku]['attributes'] = alshaya_acm_product_get_sku_configurable_values($sku_entity);

      // Prepare image data for cart for sku.
      $image = alshaya_acm_get_product_display_image($sku_entity, 'cart_thumbnail', 'cart');
      $image_data = [];
      if (!empty($image)) {
        if (!empty($image['#uri'])) {
          $image_data['src'] = file_create_url(ImageStyle::load($image['#style_name'])->buildUri($image['#uri']));
        }

        $image_data['alt'] = $image['#alt'];
        $image_data['title'] = $image['#title'];
      }
      $data[$sku]['image'] = $image_data;

      /* @var \Drupal\node\NodeInterface $parent_node */
      $parent_node = $this->skuManager->getDisplayNode($sku_entity);
      if ($parent_node instanceof NodeInterface) {
        $parent_url = $parent_node->toUrl()->toString();
        $data[$sku]['pdp_link'] = $parent_url;
        $data[$sku]['label'] = $parent_node->label();
      }
    }

    return new JsonResponse($data);
  }

}
