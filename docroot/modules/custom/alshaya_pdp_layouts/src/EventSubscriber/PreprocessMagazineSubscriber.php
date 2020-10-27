<?php

namespace Drupal\alshaya_pdp_layouts\EventSubscriber;

use Drupal\alshaya_pdp_layouts\Event\PreprocessMagazineEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_acm_product\SkuManager;

/**
 * Class Preprocess Magazine Subscriber.
 *
 * @package Drupal\alshaya_pdp_layouts\EventSubscriber
 */
class PreprocessMagazineSubscriber implements EventSubscriberInterface {

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * PreprocessMagazineSubscriber setManager method.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   */
  public function setManager(SkuManager $sku_manager) {
    $this->skuManager = $sku_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PreprocessMagazineEvent::EVENT_NAME => 'onPreprocessMagazine',
    ];
  }

  /**
   * Subscribe to the preprocess magazine event dispatched.
   *
   * @param \Drupal\alshaya_pdp_layouts\Event\PreprocessMagazineEvent $event
   *   Preprocess magazine event.
   */
  public function onPreprocessMagazine(PreprocessMagazineEvent $event) {
    $vars = $event->getVariables();
    if ($vars['sku'] instanceof SKUInterface) {
      // Get parent sku.
      $parent_sku = $this->skuManager->getParentSkuBySku($vars['sku']);
      $sku_for_description = $parent_sku instanceof SKUInterface ? $parent_sku : $vars['sku'];
      $vars['description'] = $this->skuManager->getDescription($sku_for_description, SkuManager::PDP_LAYOUT_MAGAZINE);
      $event->setVariables($vars);
    }
  }

}
