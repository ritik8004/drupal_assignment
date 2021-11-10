<?php

namespace Drupal\alshaya_hm_images\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_hm_images\SkuAssetManager;
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
   * @var \Drupal\alshaya_hm_images\SkuAssetManager
   */
  private $skuAssetsManager;

  /**
   * ProductInfoRequestedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_hm_images\SkuAssetManager $sku_assets_manager
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

      case 'swatch':
        $this->processSwatch($event);
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

    $swatch_type = $this->skuAssetsManager->getSkuSwatchType($parent);

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
