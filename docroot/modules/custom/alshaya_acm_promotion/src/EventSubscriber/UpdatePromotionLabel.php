<?php

namespace Drupal\alshaya_acm_promotion\EventSubscriber;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\alshaya_acm_product\Event\AddToCartSubmitEvent;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UpdatePromotionLabel.
 *
 * @package Drupal\alshaya_acm_promotion\EventSubscriber
 */
class UpdatePromotionLabel implements EventSubscriberInterface {

  /**
   * Cart Storage.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  private $cartStorage;

  /**
   * Alshaya Promotions Label Manager.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager
   */
  private $labelManager;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * UpdatePromotionLabel constructor.
   *
   * @param \Drupal\acq_cart\CartStorageInterface $cartStorage
   *   Cart Storage.
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager $labelManager
   *   Alshaya Promotions Label Manager.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   SKU Manager.
   */
  public function __construct(CartStorageInterface $cartStorage, AlshayaPromoLabelManager $labelManager, SkuManager $skuManager) {
    $this->cartStorage = $cartStorage;
    $this->labelManager = $labelManager;
    $this->skuManager = $skuManager;
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
    $label = $this->labelManager->getCurrentSkuPromoLabel($sku, $this->cartStorage, $this->skuManager);
    $this->labelManager->prepareResponse($label, $response);
  }

}
