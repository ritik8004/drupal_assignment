<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductInfoRequestedEventSubscriber.
 *
 * @package Drupal\alshaya_acm_product\EventSubscriber
 */
class ProductInfoRequestedEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  private $skuImagesManager;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * ProductInfoRequestedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   SKU Manager.
   */
  public function __construct(SkuImagesManager $sku_images_manager, SkuManager $skuManager) {
    $this->skuImagesManager = $sku_images_manager;
    $this->skuManager = $skuManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[ProductInfoRequestedEvent::EVENT_NAME][] = [
      'onProductInfoRequested',
      500,
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
      case 'media':
        $this->processMedia($event);
        break;

      case 'swatch':
        $this->processSwatch($event);
        break;

      case 'title':
        $this->processTitle($event);
        break;

      case 'product_tree':
        $this->getProductTree($event);
        break;
    }
  }

  /**
   * Process media for SKU based on brand specific rules.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processMedia(ProductInfoRequestedEvent $event): void {
    // Don't modify again here.
    if ($event->isValueModified()) {
      return;
    }

    $event->setValue($this->skuImagesManager->getSkuMediaItems(
      $event->getSku(),
      $event->getContext()
    ));
  }

  /**
   * Process swatch for SKU based on brand specific rules.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processSwatch(ProductInfoRequestedEvent $event): void {
    $sku = $event->getSku();

    $image = $this->skuImagesManager->getPdpSwatchImageUrl($sku);
    if (empty($image)) {
      return;
    }

    $swatch = [
      'image_url' => $image,
      'display_label' => $sku->get('attr_' . $event->getContext())->getString(),
      'swatch_type' => 'image',
    ];

    $event->setValue($swatch);
  }

  /**
   * Process description for SKU based on brand specific rules.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processDescription(ProductInfoRequestedEvent $event): void {
    // Don't modify again here.
    if ($event->isValueModified()) {
      return;
    }

    $event->setValue($this->skuManager->getDescription(
      $event->getSku(),
      $event->getContext()
    ));
  }

  /**
   * Process title for SKU based on brand specific rules.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processTitle(ProductInfoRequestedEvent $event) {
    // Don't modify again here.
    if ($event->isValueModified()) {
      return;
    }

    $sku = $event->getSku();
    $title = $event->getValue();

    if ($sku->bundle() == 'simple') {
      if ($parentSku = $this->skuManager->getParentSkuBySku($sku)) {
        $title = $parentSku->label();
      }
    }

    $event->setValue($title);
  }

  /**
   * Get Product Tree.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function getProductTree(ProductInfoRequestedEvent $event) {
    $sku = $event->getSku();

    if ($event->isValueModified()) {
      $tree = $event->getValue();
    }
    else {
      $tree = [
        'parent' => $sku,
        'products' => Configurable::getChildren($sku),
        'combinations' => [],
        'configurables' => [],
      ];
    }

    $combinations =& $tree['combinations'];

    $configurables = Configurable::getSortedConfigurableAttributes($sku);

    foreach ($configurables ?? [] as $configurable) {
      $tree['configurables'][$configurable['code']] = $configurable;
    }

    $configurable_codes = array_keys($tree['configurables']);

    foreach ($tree['products'] ?? [] as $sku_code => $sku_entity) {
      // Dot not display free gifts.
      if ($this->skuManager->isSkuFreeGift($sku_entity)) {
        continue;
      }

      // Do not display OOS variants.
      // Bypass wrapper function in this class as it creates cyclic
      // dependency.
      /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $childPlugin */
      if (!$this->skuManager->isProductInStock($sku_entity)) {
        continue;
      }

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

}
