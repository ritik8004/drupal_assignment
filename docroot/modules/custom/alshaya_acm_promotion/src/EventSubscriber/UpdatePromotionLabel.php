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
   * @param \Drupal\alshaya_acm_product\Event\AddToCartFormSubmitEvent $event
   *   Add to Cart Submit Event.
   */
  public function postAddToCartFormSubmit(AddToCartFormSubmitEvent $event) {
    if ($this->labelManager->isDynamicLabelsEnabled()) {
      $sku = $event->getSku();
      $variant = $event->getVariant();

      if ($sku->bundle() === 'configurable') {
        // Load the parent again to ensure we keep adding the product for
        // first parent when multiple parents are available and also to
        // ensure we select proper parent when using alshaya_color_split.
        $sku = $variant->getPluginInstance()->getParentSku($variant);
      }

      $response = $event->getResponse();
      $label = $this->labelManager->getSkuPromoDynamicLabel($variant ?? $sku);

      // Prepare response if label is present.
      if (!empty($label)) {
        $this->labelManager->prepareResponse($label, $sku->id(), $response);
      }
    }
  }

}
