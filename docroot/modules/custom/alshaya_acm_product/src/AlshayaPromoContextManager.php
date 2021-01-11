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
   * Default Context.
   *
   * @var string
   */
  protected static $context = 'web';

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
   * Set Default context.
   *
   * @param string $context
   *   Default context.
   */
  public static function updateDefaultContext(string $context) {
    self::$context = $context;
  }

  /**
   * Get Default context.
   *
   * @return string
   *   Context - web/app.
   */
  public function getDefaultContext() {
    return self::$context;
  }

  /**
   * Validates & fetches promotion context from the request.
   *
   * @return string
   *   Context - web/app.
   */
  public function getPromotionContext() {
    $context = $this->currentRequest->query->get('context');
    if ($context == 'mapp' || $context == 'app') {
      return 'app';
    }
    if ($context == 'web') {
      return 'web';
    }
    return self::$context;
  }

}
