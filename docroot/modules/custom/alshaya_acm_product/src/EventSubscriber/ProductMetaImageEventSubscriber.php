<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\alshaya_acm_product\SkuImagesHelper;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\alshaya_seo\Event\MetaImageRenderEvent;
use Drupal\node\NodeInterface;
use Drupal\acq_sku\Entity\SKU;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Product Meta Image Event Subscriber.
 *
 * @package Drupal\alshaya_acm_product\EventSubscriber
 */
class ProductMetaImageEventSubscriber implements EventSubscriberInterface {

  /**
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Sku Manager service.
   *
   * @var Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  private $skuImagesManager;

  /**
   * Sku images helper.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesHelper
   */
  private $skuImagesHelper;

  /**
   * ProductDyPageTypeEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route Match Object.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesHelper $images_helper
   *   Sku images helper.
   */
  public function __construct(RouteMatchInterface $route_match,
                              SkuManager $skuManager,
                              SkuImagesManager $sku_images_manager,
                              SkuImagesHelper $images_helper) {
    $this->routeMatch = $route_match;
    $this->skuManager = $skuManager;
    $this->skuImagesManager = $sku_images_manager;
    $this->skuImagesHelper = $images_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[MetaImageRenderEvent::EVENT_NAME][] = ['setProductMetaImage', 200];
    return $events;
  }

  /**
   * Set PRODUCT first image in meta image tag.
   *
   * @param \Drupal\alshaya_seo\MetaImageRenderEvent $event
   *   Dispatched Event.
   */
  public function setProductMetaImage(MetaImageRenderEvent $event) {
    if (($node = $event->getContext()) && $node instanceof NodeInterface && $node->bundle() == 'acq_product') {
      $sku = $this->skuManager->getSkuForNode($node);
      $sku_entity = SKU::loadFromSku($sku);
      if (!($sku_entity instanceof SKU)) {
        return;
      }

      $sku_media = $this->skuImagesManager->getFirstImage($sku_entity);
      if (!empty($sku_media)) {
        $teaser_image = $this->skuImagesHelper->getSkuImage($sku_media, SkuImagesHelper::STYLE_PRODUCT_TEASER);
        if (!empty($teaser_image['#uri'])) {
          $event->setMetaImage(file_create_url($teaser_image['#uri']));
          // We have three event subscriber to handle meta images.
          // Execution order for them are -
          // 1-ProductMetaImageEventSubscriber
          // 2-SuperCategoryMetaImageEventSubscriber
          // 3-DefaultMetaImageEventSubscriber
          // So we if we are on product detail page and if get image
          // then we are avoiding to execute other event subcribers.
          $event->stopPropagation();
        }
      }
    }
  }

}
