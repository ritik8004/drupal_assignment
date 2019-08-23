<?php

namespace Drupal\alshaya_acm_promotion\EventSubscriber;

use Drupal\alshaya_acm_product\Event\AddToCartSubmitEvent;
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
    $events[AddToCartSubmitEvent::EVENT_NAME][] = 'postAddToCartSubmit';
    return $events;
  }

  /**
   * Add Promo Label Update Commands.
   *
   * @param \Drupal\alshaya_acm_product\Event\AddToCartSubmitEvent $addToCartSubmitEvent
   *   Add to Cart Submit Event.
   */
  public function postAddToCartSubmit(AddToCartSubmitEvent $addToCartSubmitEvent) {
    $sku = $addToCartSubmitEvent->getSku();
    $response = $addToCartSubmitEvent->getResponse();
    $label = $this->labelManager->getCurrentSkuPromoLabel($sku);
    $this->labelManager->prepareResponse($label, $response);
  }

}
