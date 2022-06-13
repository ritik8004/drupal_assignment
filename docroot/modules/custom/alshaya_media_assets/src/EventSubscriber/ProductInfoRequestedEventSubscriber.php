<?php

namespace Drupal\alshaya_media_assets\EventSubscriber;

use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_media_assets\Services\SkuAssetManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Product Info Requested Event Subscriber.
 *
 * @package Drupal\alshaya_media_assets\EventSubscriber
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
   * ProductInfoRequestedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_media_assets\Services\SkuAssetManager $sku_assets_manager
   *   SKU Assets Manager.
   */
  public function __construct(SkuAssetManager $sku_assets_manager) {
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
      case 'media':
        $this->processMedia($event);
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
    $sku = $event->getSku();

    // We don't want to show images from parent in HnM.
    if ($sku->bundle() === 'configurable') {
      $event->setValue([]);
      return;
    }

    $context = $event->getContext();

    // We show same images for pdp, modal, modal-magazine.
    // To avoid adding extra configs for them (sorting assets) we use pdp
    // for all three cases.
    $context = (strpos($context, 'modal') > -1) ? 'pdp' : $context;

    switch ($context) {
      case 'cart':
      case 'pdp':
        $media = $this->skuAssetsManager->getAssetsForSku($sku, $context);

        $return = [];
        foreach ($media as $item) {
          $asset_type = $this->skuAssetsManager->getAssetType($item);

          switch ($asset_type) {
            case 'image':
              $item['label'] = $sku->label();
              $return['media_items']['images'][] = $item;
              break;

            case 'video':
              $item['label'] = $sku->label();
              $return['media_items']['videos'][] = $item;
              break;

            default:
              continue 2;
          }
        }

        $event->setValue($return);
        break;

      case 'search':
        // Lookup images on current SKU if its a simple SKU.
        $main_image_assets = $this->skuAssetsManager->getSkuAssets($sku, 'plp');
        $avoid_assets = !empty($main_image_assets) ? [$main_image_assets[0]['Data']['AssetId']] : [];
        $hover_image_assets = $this->skuAssetsManager->getSkuAssets($sku, 'plp_hover', $avoid_assets);

        $return = [];

        if (!empty($main_image_assets)) {
          $return['media_items']['images'][] = reset($main_image_assets);

          if (!empty($hover_image_assets)) {
            $return['media_items']['images'][] = reset($hover_image_assets);
          }
        }

        $event->setValue($return);
        break;

      case 'teaser':
        $teaser_assets = $this->skuAssetsManager->getSkuAssets($sku, 'teaser');

        // Try once with plp assets if nothing found for teaser.
        if (empty($teaser_assets)) {
          $teaser_assets = $this->skuAssetsManager->getSkuAssets($sku, 'plp');
        }

        $return = [];
        if (!empty($teaser_assets)) {
          $return['media_items']['images'][] = reset($teaser_assets);
        }
        $event->setValue($return);
        break;
    }
  }

}
