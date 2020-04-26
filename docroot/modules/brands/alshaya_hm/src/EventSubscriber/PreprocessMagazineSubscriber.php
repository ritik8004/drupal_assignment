<?php

namespace Drupal\alshaya_hm\EventSubscriber;

use Drupal\alshaya_pdp_layouts\Event\PreprocessMagazineEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\acq_sku\ProductInfoHelper;

/**
 * Class PreprocessMagazineSubscriber.
 *
 * @package Drupal\alshaya_hm\EventSubscriber
 */
class PreprocessMagazineSubscriber implements EventSubscriberInterface {

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * Product Info Helper.
   *
   * @var \Drupal\acq_sku\ProductInfoHelper
   */
  private $productInfoHelper;

  /**
   * PreprocessMagazineSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\acq_sku\ProductInfoHelper $product_info_helper
   *   Product Info Helper.
   */
  public function __construct(SkuManager $sku_manager, ProductInfoHelper $product_info_helper) {
    $this->skuManager = $sku_manager;
    $this->productInfoHelper = $product_info_helper;
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
    $vars = $event->variables;
    if ($vars['sku'] instanceof SKUInterface) {
      // Get parent sku.
      $parent_sku = $this->skuManager->getParentSkuBySku($vars['sku']);
      $labels = $this->productInfoHelper->getValue($parent_sku, 'collection_labels', 'pdp');
      $vars['product_attribute_labels'] = [
        '#markup' => $labels,
      ];
      $event->setVariables($vars);
    }
  }

}
