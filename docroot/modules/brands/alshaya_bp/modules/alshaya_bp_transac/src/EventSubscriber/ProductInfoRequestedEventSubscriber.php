<?php

namespace Drupal\alshaya_bp_transac\EventSubscriber;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_acm_product\ProductHelper;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Contains Product Info Requested Event Subscriber methods.
 *
 * @package Drupal\alshaya_bp_transac\EventSubscriber
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
  public function __construct(
    ProductHelper $product_helper
  ) {
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
    $prod_description = $this->getDescription($sku_entity);
    $event->setValue($prod_description['description']);
  }

  /**
   * Process short descriptions for SKU.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processShortDescription(ProductInfoRequestedEvent $event) {
    $sku_entity = $event->getSku();
    $prod_description = $this->getDescription($sku_entity);
    $event->setValue($prod_description['short_desc']);
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
  protected function getDescription(SKU $sku_entity) {
    $static = &drupal_static(__METHOD__, []);

    if (!empty($static[$sku_entity->language()->getId()][$sku_entity->getSku()])) {
      return $static[$sku_entity->language()->getId()][$sku_entity->getSku()];
    }

    $return = [];
    $body = $sku_entity->get('attr_description')->getValue();
    if ($body) {
      $description = [];
      $description['value'] = [
        '#markup' => $body[0]['value'],
      ];
      $return['description'][] = $description;
      $this->productHelper->updateShortDescription($return, $body[0]['value']);
      if (!empty($return['short_desc'])) {
        $return['short_desc']['value']['#markup'] = $this->productHelper->processShortDescEllipsis($return['short_desc']['value']['#markup']);
      }
    }

    if ($suitable_for = $sku_entity->get('attr_suitable_for')->getString()) {
      $suitable_for = [
        'label' => ['#markup' => $this->t('Suitable for')],
        'value' => ['#markup' => $suitable_for],
      ];
      $return['description'][] = $suitable_for;
    }

    if ($how_to_use = $sku_entity->get('attr_how_to_use')->getString()) {
      $how_to_use = [
        'label' => ['#markup' => $this->t('How to use')],
        'value' => ['#markup' => $how_to_use],
      ];
      $return['description'][] = $how_to_use;
    }

    if ($hazards_and_cautions = $sku_entity->get('attr_hazards_and_cautions')->getString()) {
      $hazards_and_cautions = [
        'label' => ['#markup' => $this->t('Hazards and cautions')],
        'value' => ['#markup' => $hazards_and_cautions],
      ];
      $return['description'][] = $hazards_and_cautions;
    }

    if ($important_info = $sku_entity->get('attr_important_info')->getString()) {
      $important_info = [
        'label' => ['#markup' => $this->t('Important info')],
        'value' => ['#markup' => $important_info],
      ];
      $return['description'][] = $important_info;
    }

    if ($ingredients = $sku_entity->get('attr_ingredients')->getString()) {
      $ingredients = [
        'label' => ['#markup' => $this->t('Ingredients')],
        'value' => ['#markup' => $ingredients],
      ];
      $return['description'][] = $ingredients;
    }

    if ($active_ingredients = $sku_entity->get('attr_active_ingredients')->getString()) {
      $active_ingredients = [
        'label' => ['#markup' => $this->t('Active ingredients')],
        'value' => ['#markup' => $active_ingredients],
      ];
      $return['description'][] = $active_ingredients;
    }

    if ($indications_age_restrictions = $sku_entity->get('attr_indications_age_restrictions')->getString()) {
      $indications_age_restrictions = [
        'label' => ['#markup' => $this->t('Indications age restrictions')],
        'value' => ['#markup' => $indications_age_restrictions],
      ];
      $return['description'][] = $indications_age_restrictions;
    }

    if ($pil = $sku_entity->get('attr_pil')->getString()) {
      $pil = [
        'label' => ['#markup' => $this->t('PIL')],
        'value' => ['#markup' => $pil],
      ];
      $return['description'][] = $pil;
    }

    if ($body_area = $sku_entity->get('attr_body_area')->getString()) {
      $body_area = [
        'label' => ['#markup' => $this->t('Body area')],
        'value' => ['#markup' => $body_area],
      ];
      $return['description'][] = $body_area;
    }

    $static[$sku_entity->language()->getId()][$sku_entity->getSku()] = $return;
    return $return;
  }

}
