<?php

namespace Drupal\alshaya_acm_promotion\EventSubscriber;

use Drupal\alshaya_acm_product\Event\AddToCartFormSubmitEvent;
use Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UpdatePromotionLabel.
 *
 * @package Drupal\alshaya_acm_promotion\EventSubscriber
 */
class UpdatePromotionLabel implements EventSubscriberInterface {

  /**
   * Alshaya Promotions Label Manager.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager
   */
  private $labelManager;

  /**
   * UpdatePromotionLabel constructor.
   *
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager $labelManager
   *   Alshaya Promotions Label Manager.
   */
  public function __construct(AlshayaPromoLabelManager $labelManager) {
    $this->labelManager = $labelManager;
  }

  /**
   * Get subscribed events.
   */
  public static function getSubscribedEvents() {
    $events[AddToCartFormSubmitEvent::EVENT_NAME][] = 'postAddToCartFormSubmit';
    return $events;
  }

  /**
   * Add Promo Label Update Commands.
   *
   * @param \Drupal\alshaya_acm_product\Event\AddToCartFormSubmitEvent $addToCartFormSubmitEvent
   *   Add to Cart Submit Event.
   */
  public function postAddToCartFormSubmit(AddToCartFormSubmitEvent $addToCartFormSubmitEvent) {
    $sku = $addToCartFormSubmitEvent->getSku();
    $response = $addToCartFormSubmitEvent->getResponse();
    $label = $this->labelManager->getCurrentSkuPromoLabel($sku);

    // Prepare response if label is present.
    if (!empty($label)) {
      $this->labelManager->prepareResponse($label, $response);
    }
  }

}
