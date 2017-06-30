<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProductStockController.
 *
 * @package Drupal\alshaya_acm_product\Controller
 */
class ProductStockController extends ControllerBase {

  /**
   * Controller to check for Product's availability.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Product node for which we checking the stock.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response object returning availability.
   *
   * @throws \InvalidArgumentException
   * @throws \UnexpectedValueException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function checkStock(Node $node) {
    $sku = $node->get('field_skus')->first()->getString();
    $sku_entity = SKU::loadFromSku($sku);
    if (alshaya_acm_is_product_in_stock($sku_entity)) {
      $build['#markup'] = '';
    }
    else {
      $build['#markup'] = '<span>' . $this->t('out of stock') . '</span>';
    }

    $response = new Response();
    $response->setContent(render($build));
    return $response;
  }

}
