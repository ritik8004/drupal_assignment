<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Product Info Requested Event Subscriber.
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
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * ProductInfoRequestedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   SKU Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(
    SkuImagesManager $sku_images_manager,
    SkuManager $skuManager,
    ConfigFactoryInterface $config_factory
  ) {
    $this->skuImagesManager = $sku_images_manager;
    $this->skuManager = $skuManager;
    $this->configFactory = $config_factory;
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
    }
  }

  /**
   * Process media for SKU based on brand specific rules.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  private function processMedia(ProductInfoRequestedEvent $event): void {
    // Don't modify again here.
    if ($event->isValueModified()) {
      return;
    }

    $media = $this->skuImagesManager->getGalleryMedia($event->getSku());
    $event->setValue($media);
  }

  /**
   * Process swatch for SKU based on brand specific rules.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processSwatch(ProductInfoRequestedEvent $event): void {
    $sku = $event->getSku();
    // Fetch image data with relevant attribute media role.
    $media_role = $this->skuImagesManager->getSwatchAttributRole($event->getContext());
    $image = $this->skuImagesManager->getPdpSwatchImageUrl($sku, $media_role);

    $swatch = [
      'image_url' => $image ?? NULL,
      'display_label' => $sku->get('attr_' . $event->getContext())->getString(),
      'swatch_type' => !empty($image) ? 'image' : 'text',
    ];

    // If image is not there, fallback to RGB color.
    if (empty($swatch['image_url'])) {
      $color_swatch = $this->getSolidColorLabel($sku);
      if (!empty($color_swatch)) {
        $swatch = $color_swatch;
      }
    }

    $event->setValue($swatch);
  }

  /**
   * Get RGB color label and value.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   *
   * @return array
   *   Value and label for RGB color.
   */
  private function getSolidColorLabel(SKUInterface $sku): array {
    $multiple_attributes_for_color = $this->configFactory->get('alshaya_acm_product.display_settings')->get('color_attribute_config');
    // If site uses multiple attributes for color.
    if ($multiple_attributes_for_color && $multiple_attributes_for_color['support_multiple_attributes']) {
      // Attribute key which will be used for label.
      $config_color_label_attribute = $multiple_attributes_for_color['configurable_color_label_attribute'];
      // Attribute key which will be used for the swatch code.
      $color_code_attribute = $multiple_attributes_for_color['configurable_color_code_attribute'];
      // Get the configurables for the product.
      $variant_sku = SKU::loadFromSku($sku->getSku());

      if (!($variant_sku instanceof SKU)) {
        return [];
      }

      return [
        'display_label' => $variant_sku->get($config_color_label_attribute)->getString(),
        'swatch_type' => 'color',
        'display_value' => $variant_sku->get($color_code_attribute)->getString(),
      ];
    }

    return [];
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

}
