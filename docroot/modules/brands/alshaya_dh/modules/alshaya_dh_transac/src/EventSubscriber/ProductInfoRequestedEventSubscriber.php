<?php

namespace Drupal\alshaya_dh_transac\EventSubscriber;

use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_acm_product\ProductHelper;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Contains Product Info Requested Event Subscriber methods.
 *
 * @package Drupal\alshaya_cos_transac\EventSubscriber
 */
class ProductInfoRequestedEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Product helper service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $productHelper;

  /**
   * ProductInfoRequestedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\ProductHelper $product_helper
   *   Product helper service object.
   */
  public function __construct(ProductHelper $product_helper) {
    $this->productHelper = $product_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[ProductInfoRequestedEvent::EVENT_NAME][] = [
      'onProductInfoRequested',
      800,
    ];

    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function onProductInfoRequested(ProductInfoRequestedEvent $event) {
    switch ($event->getFieldCode()) {
      case 'description':
        $this->processDescription($event);
        break;

      case 'short_description':
        $this->processShortDescription($event);
        break;
    }
  }

  /**
   * Process description for SKU.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processDescription(ProductInfoRequestedEvent $event) {
    $sku_entity = $event->getSku();
    $description = _alshaya_dh_transac_get_product_description($sku_entity);
    $event->setValue($description['description']);
  }

  /**
   * Process short descriptions for SKU.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processShortDescription(ProductInfoRequestedEvent $event) {
    $sku_entity = $event->getSku();
    $description = _alshaya_dh_transac_get_product_description($sku_entity);

    if (!empty($description['description'][0]['value']['#markup'])) {
      // Get short description of given html.
      $desc = $this->productHelper->createShortDescription($description['description'][0]['value']['#markup']);
      $short_desc = [
        'label' => [
          '#markup' => $this->t('Short Description', [], ['langcode' => $sku_entity->language()->getId()]),
        ],
        'value' => [
          '#markup' => $desc['html'],
        ],
      ];
      $event->setValue($short_desc);
    }
  }

}
