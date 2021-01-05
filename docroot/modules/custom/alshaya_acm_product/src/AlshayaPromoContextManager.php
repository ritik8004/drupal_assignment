<?php

namespace Drupal\alshaya_acm_product;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Alshaya Promo Context Manager.
 *
 * @package Drupal\alshaya_acm_product
 */
class AlshayaPromoContextManager {

  /**
   * AlshayaPromoLabelManager constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * Validates & fetches promotion context from the request.
   *
   * @param string $default
   *   Default Context - app/web.
   *
   * @return string
   *   Context - web/app.
   */
  public function getPromotionContext($default = 'web') {
    $context = $this->currentRequest->query->get('context');
    if ($context == 'mapp' || $context == 'app') {
      return 'app';
    }
    if ($context == 'web') {
      return 'web';
    }
    return $default;
  }

}
