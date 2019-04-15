<?php

namespace Drupal\acq_sku\EventSubscriber;

use Drupal\acq_sku\CartFormHelper;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\acq_sku\ProductInfoRequestedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductInfoRequestedEventSubscriber.
 *
 * @package Drupal\acq_sku\EventSubscriber
 */
class ProductInfoRequestedEventSubscriber implements EventSubscriberInterface {

  /**
   * Cart Form Helper.
   *
   * @var \Drupal\acq_sku\CartFormHelper
   */
  private $cartFormHelper;

  /**
   * ProductInfoRequestedEventSubscriber constructor.
   *
   * @param \Drupal\acq_sku\CartFormHelper $cart_form_helper
   *   Cart Form Helper.
   */
  public function __construct(CartFormHelper $cart_form_helper) {
    $this->cartFormHelper = $cart_form_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[ProductInfoRequestedEvent::EVENT_NAME][] = [
      'onProductInfoRequested',
      100,
    ];

    $events[ProductInfoRequestedEvent::EVENT_NAME][] = [
      'productInfoFinalProcess',
      1,
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
      case 'configurable_attributes':
        $this->getConfigurableAttributes($event);
        break;

      case 'product_tree':
        $this->getProductTree($event);
        break;
    }
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function productInfoFinalProcess(ProductInfoRequestedEvent $event) {
    switch ($event->getFieldCode()) {
      case 'configurable_attributes':
        $this->sortConfigurableAttributes($event);
        break;

      case 'product_tree':
        $this->sortProductTreeAndPrepareByAttribute($event);
        break;
    }
  }

  /**
   * Process and return configurable attributes.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function getConfigurableAttributes(ProductInfoRequestedEvent $event): void {
    // Don't modify again here.
    if ($event->isValueModified()) {
      return;
    }

    $sku = $event->getSku();
    $configurables = unserialize($sku->get('field_configurable_attributes')->getString());
    $event->setValue($configurables);
  }

  /**
   * Sort configurable attributes.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function sortConfigurableAttributes(ProductInfoRequestedEvent $event): void {
    $sku = $event->getSku();

    $configurables = $event->getValue();

    $configurable_weights = $this->cartFormHelper->getConfigurableAttributeWeights(
      $sku->get('attribute_set')->getString()
    );

    // Sort configurables based on the config.
    uasort($configurables, function ($a, $b) use ($configurable_weights) {
      // We may keep getting new configurable options not defined in config.
      // Use default values for them and keep their sequence as is.
      // Still move the ones defined in our config as per weight in config.
      $a = $configurable_weights[$a['code']] ?? -50;
      $b = $configurable_weights[$b['code']] ?? 50;
      return $a - $b;
    });

    $event->setValue($configurables);
  }

  /**
   * Process description for SKU based on brand specific rules.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function getProductTree(ProductInfoRequestedEvent $event) {
    if ($event->isValueModified()) {
      return;
    }

    $sku = $event->getSku();

    $tree = [
      'parent' => $sku,
      'products' => Configurable::getChildren($sku),
      'combinations' => [],
      'configurables' => [],
    ];

    $combinations =& $tree['combinations'];

    $configurables = Configurable::getSortedConfigurableAttributes($sku);

    foreach ($configurables ?? [] as $configurable) {
      $tree['configurables'][$configurable['code']] = $configurable;
    }

    $configurable_codes = array_keys($tree['configurables']);

    foreach ($tree['products'] ?? [] as $sku_code => $sku_entity) {
      $attributes = $sku_entity->get('attributes')->getValue();
      $attributes = array_column($attributes, 'value', 'key');
      foreach ($configurable_codes as $code) {
        $value = $attributes[$code] ?? '';

        if (empty($value)) {
          // Ignore variants with empty value in configurable options.
          unset($tree['products'][$sku_code]);
          continue;
        }

        $combinations['by_sku'][$sku_code][$code] = $value;
        $combinations['attribute_sku'][$code][$value][] = $sku_code;
      }
    }

    $event->setValue($tree);
  }

  /**
   * Sort the configurable attributes and prepare by_attribute combinations.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function sortProductTreeAndPrepareByAttribute(ProductInfoRequestedEvent $event) {
    $tree = $event->getValue();

    if (empty($tree['combinations'])) {
      return;
    }

    $combinations =& $tree['combinations'];

    // Sort the values in attribute_sku so we can use it later.
    foreach ($combinations['attribute_sku'] ?? [] as $code => $values) {
      if ($this->cartFormHelper->isAttributeSortable($code)) {
        $combinations['attribute_sku'][$code] = Configurable::sortConfigOptions($values, $code);
      }
      else {
        // Sort from field_configurable_attributes.
        $configurable_attribute = [];
        foreach ($tree['configurables'] as $configurable) {
          if ($configurable['code'] === $code) {
            $configurable_attribute = $configurable['values'];
            break;
          }
        }

        if ($configurable_attribute) {
          $configurable_attribute_weights = array_flip(array_column($configurable_attribute, 'value_id'));
          uksort($combinations['attribute_sku'][$code], function ($a, $b) use ($configurable_attribute_weights) {
            return $configurable_attribute_weights[$a] - $configurable_attribute_weights[$b];
          });
        }
      }
    }

    // Prepare combinations array grouped by attributes to check later which
    // combination is possible using isset().
    $combinations['by_attribute'] = [];

    foreach ($combinations['by_sku'] ?? [] as $sku_string => $combination) {
      $combination_string = Configurable::getSelectedCombination($combination);
      $combinations['by_attribute'][$combination_string] = $sku_string;
    }

    $event->setValue($tree);
  }

}
