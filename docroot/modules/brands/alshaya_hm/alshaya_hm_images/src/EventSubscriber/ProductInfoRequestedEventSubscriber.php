<?php

namespace Drupal\alshaya_hm_images\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_hm_images\Services\HmImagesHelper;
use Drupal\alshaya_media_assets\Services\SkuAssetManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Product Info Requested Event Subscriber.
 *
 * @package Drupal\alshaya_hm_images\EventSubscriber
 */
class ProductInfoRequestedEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * SKU Assets Manager.
   *
   * @var \Drupal\alshaya_media_assets\Services\SkuAssetManager
   */
  private $skuAssetsManager;

  /**
   * Images Helper Service.
   *
   * @var \Drupal\alshaya_hm_images\Services\HmImagesHelper
   */
  private $imagesHelper;

  /**
   * ProductInfoRequestedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_hm_images\Services\HmImagesHelper $images_helper
   *   Images Helper Service.
   */
  public function __construct(HmImagesHelper $images_helper) {
    $this->imagesHelper = $images_helper;
  }

  /**
   * Setter function for Sku Asset Manager service.
   *
   * @param \Drupal\alshaya_media_assets\Services\SkuAssetManager $sku_assets_manager
   *   SKU Assets Manager.
   */
  public function setSkuAssetManager(SkuAssetManager $sku_assets_manager) {
    // @todo Move this back to normal/constructor once module enabled on prod.
    $this->skuAssetsManager = $sku_assets_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[ProductInfoRequestedEvent::EVENT_NAME][] = [
      'onProductInfoRequested',
      801,
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
      case 'swatch':
        $this->processSwatch($event);
        break;
    }
  }

  /**
   * Process swatch for SKU based on brand specific rules.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processSwatch(ProductInfoRequestedEvent $event): void {
    $sku = $event->getSku();
    $plugin = $sku->getPluginInstance();
    $parent = $plugin->getParentSku($sku);
    if (!($parent instanceof SKUInterface)) {
      return;
    }

    $swatch_type = $this->imagesHelper->getSkuSwatchType($parent);

    if (strtoupper($swatch_type) !== SkuAssetManager::LP_SWATCH_RGB) {
      $assets = $this->skuAssetsManager->getSkuAssets($sku, 'swatch');
    }

    // If swatch type is not miniature_image or assets were missing from
    // sku, use rgb color code instead.
    $swatch = [
      'display_label' => $sku->get('attr_color_label')->getString(),
      'swatch_type' => empty($assets) ? SkuAssetManager::LP_SWATCH_RGB : $swatch_type,
    ];

    $swatch['display_value'] = empty($assets)
      ? $sku->get('attr_rgb_color')->getString()
      : file_create_url($assets[0]['drupal_uri']);

    $event->setValue($swatch);

    // For HM brand we have custom requirements around swatch fields
    // so we do not want generic eventSubscriber to be executed further
    // so we stop the propogation.
    $event->stopPropagation();
  }

}
