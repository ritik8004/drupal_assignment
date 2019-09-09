<?php

namespace Drupal\alshaya_vs_transac\EventSubscriber;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_acm_product\ProductHelper;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductInfoRequestedEventSubscriber.
 *
 * @package Drupal\alshaya_vs_transac\EventSubscriber
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
    $description = $this->getDescription($sku_entity);
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
    $description = $this->getDescription($sku_entity);

    if (!empty($description['description'][0]['value']['#markup'])) {
      // Get short description of given html.
      $desc = $this->productHelper->createShortDescription($description['description'][0]['value']['#markup']);
      $short_desc = [
        'label' => [
          '#markup' => $this->t('Short Description'),
        ],
        'value' => [
          '#markup' => $desc['html'],
        ],
      ];
      $event->setValue($short_desc);
    }
  }

  /**
   * Prepare description and short description array for given sku.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   The sku entity.
   *
   * @return array
   *   Return array of description and short description.
   */
  private function getDescription(SKU $sku_entity) {
    $static = drupal_static(__METHOD__, []);

    if (!empty($static[$sku_entity->language()->getId()][$sku_entity->getSku()])) {
      return $static[$sku_entity->language()->getId()][$sku_entity->getSku()];
    }

    $return['description'] = [];
    $body = $sku_entity->get('attr_description')->getValue();
    if (is_array($body) && !empty($body[0]['value'])) {
      $return['description'][] = [
        'value' => [
          '#markup' => $body[0]['value'],
        ],
      ];
    }

    $static[$sku_entity->language()->getId()][$sku_entity->getSku()] = $return;
    return $return;
  }

}
