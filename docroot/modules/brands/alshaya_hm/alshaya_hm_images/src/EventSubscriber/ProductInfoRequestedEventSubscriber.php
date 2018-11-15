<?php

namespace Drupal\alshaya_hm_images\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_hm_images\SkuAssetManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductInfoRequestedEventSubscriber.
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

    $media = [];
    $styled_images = [];

    switch ($context) {
      case 'pdp':
        $pdp_images = $this->skuAssetsManager->getImagesForSku(
          $sku,
          'pdp',
          ['pdp_fullscreen'],
          FALSE
        );

        foreach ($pdp_images ?? [] as $image) {
          $url = $image['raw_url']->toString();
          $media[$url] = [
            'url' => $url,
            'image_type' => $image['sortAssetType'],
          ];

          $styled_images[] = $image['url']->toString();
        }

        break;

      case 'search':
        // Lookup images on current SKU if its a simple SKU.
        $main_image_assets = $this->skuAssetsManager->getSkuAssets($sku, 'plp', ['plp']);
        $avoid_assets = !empty($main_image_assets) && !empty($main_image_assets[0]['Data']) ? [$main_image_assets[0]['Data']['AssetId']] : [];
        $hover_image_assets = $this->skuAssetsManager->getSkuAssets($sku, 'plp_hover', ['plp'], '', TRUE, $avoid_assets);

        $plp_main_image = !empty($main_image_assets) ? $main_image_assets[0] : NULL;
        $plp_hover_image = !empty($hover_image_assets) ? $hover_image_assets[0] : NULL;

        if ($plp_main_image) {
          $url = $plp_main_image['raw_url']->toString();
          $media[$url] = [
            'url' => $url,
            'image_type' => $plp_main_image['sortAssetType'],
          ];

          $styled_images[] = $plp_main_image['url']->toString();

          if ($plp_hover_image) {
            $url = $plp_hover_image['raw_url']->toString();
            $media[$url] = [
              'url' => $url,
              'image_type' => $plp_hover_image['sortAssetType'],
            ];
            $styled_images[] = $plp_hover_image['url']->toString();
          }
        }
        break;

      case 'teaser':
        $teaser_assets = $this->skuAssetsManager->getSkuAssets($sku, 'teaser', [$context]);

        // Try once with plp assets if nothing found for teaser.
        if (empty($teaser_assets)) {
          $teaser_assets = $this->skuAssetsManager->getSkuAssets($sku, 'plp', [$context]);
        }

        if (!empty($teaser_assets)) {
          $image = reset($teaser_assets);
          $url = $image['raw_url']->toString();
          $media[$url] = [
            'url' => $url,
            'image_type' => $image['sortAssetType'],
          ];
          $styled_images[] = $image['url']->toString();
        }
        break;
    }

    if (!empty($media)) {
      $event->setValue([
        'images' => array_keys($media),
        'videos' => [],
        'styled_images' => $styled_images,
        'images_with_type' => array_values($media),
      ]);
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
      $assets = $this->skuAssetsManager->getSkuAssets($sku, 'swatch', ['swatch'], $swatch_type, FALSE);
    }

    // If swatch type is not miniature_image or assets were missing from
    // sku, use rgb color code instead.
    $swatch = [
      'display_value' => empty($assets) ? $sku->get('attr_rgb_color')->getString() : $assets[0]['raw_url']->toString(),
      'display_label' => $sku->get('attr_color_label')->getString(),
      'swatch_type' => empty($assets) ? SkuAssetManager::LP_SWATCH_RGB : $swatch_type,
    ];

    $event->setValue($swatch);
  }

}
