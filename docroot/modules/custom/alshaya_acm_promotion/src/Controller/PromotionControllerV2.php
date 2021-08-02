<?php

namespace Drupal\alshaya_acm_promotion\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class Promotion Controller V2.
 */
class PromotionControllerV2 extends PromotionController {

  /**
   * Get Promotions dynamic labels for both product and cart level.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   */
  public function getPromotionDynamicLabelForCartV2(Request $request) {
    $get = $request->query->all();
    $sku_encoded = $get['products'][0]['sku'];
    $sku = base64_decode($sku_encoded);
    $get['products'][0]['sku'] = $sku;
    return parent::getPromotionDynamicLabelForCartHelper($get);

  }

}
