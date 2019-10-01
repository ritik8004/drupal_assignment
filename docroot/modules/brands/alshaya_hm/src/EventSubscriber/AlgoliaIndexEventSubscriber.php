<?php

namespace Drupal\alshaya_hm\EventSubscriber;

use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_product_options\SwatchesHelper;
use Drupal\alshaya_search_algolia\Event\AlshayaAlgoliaProductIndexEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AlgoliaIndexEventSubscriber.
 *
 * @package Drupal\alshaya_hm\EventSubscriber
 */
class AlgoliaIndexEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * Swatches helper service.
   *
   * @var \Drupal\alshaya_product_options\SwatchesHelper
   */
  private $swatchesHelper;

  /**
   * AlgoliaIndexEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\alshaya_product_options\SwatchesHelper $swatches_helper
   *   Swatches helper service.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   Images manager.
   */
  public function __construct(
    SkuManager $sku_manager,
    SwatchesHelper $swatches_helper,
    SkuImagesManager $sku_images_manager
  ) {
    $this->skuManager = $sku_manager;
    $this->swatchesHelper = $swatches_helper;
    $this->skuImagesManager = $sku_images_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      AlshayaAlgoliaProductIndexEvent::PRODUCT_INDEX => 'onProductIndex',
    ];
  }

  /**
   * Index brand specific attributes.
   *
   * @param \Drupal\alshaya_search_algolia\Event\AlshayaAlgoliaProductIndexEvent $event
   *   The event object.
   */
  public function onProductIndex(AlshayaAlgoliaProductIndexEvent $event) {
    $item = $event->getItem();

    if (!empty($item['attr_color_family'])) {
      foreach ($item['attr_color_family'] as $key => $value) {
        $swatch = $this->swatchesHelper->getSwatch('color_family', $value, $item['search_api_language']);
        $swatch_data = ['value' => $swatch['name'], 'label' => $swatch['name']];
        if ($swatch) {
          switch ($swatch['type']) {
            case SwatchesHelper::SWATCH_TYPE_TEXTUAL:
              $swatch_data['swatch_text'] = $swatch['swatch'];
              $swatch_data['label'] .= ', swatch_text:' . $swatch['swatch'];
              break;

            case SwatchesHelper::SWATCH_TYPE_VISUAL_COLOR:
              $swatch_data['swatch_color'] = $swatch['swatch'];
              $swatch_data['label'] .= ', swatch_color:' . $swatch['swatch'];
              break;

            case SwatchesHelper::SWATCH_TYPE_VISUAL_IMAGE:
              $swatch_data['swatch_image'] = $swatch['swatch'];
              $swatch_data['label'] .= ', swatch_image:' . $swatch['swatch'];
              break;

            default:
              continue;
          }
          $item['attr_color_family'][$key] = $swatch_data;
        }
      }
      $event->setItem($item);
    }
  }

}
