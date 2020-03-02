<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\alshaya_seo\Event\MetaImageRenderEvent;
use Drupal\node\NodeInterface;
use Drupal\acq_sku\Entity\SKU;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductMetaImageEventSubscriber.
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
   * ProductDyPageTypeEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route Match Object.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager.
   */
  public function __construct(RouteMatchInterface $route_match, SkuManager $skuManager, SkuImagesManager $sku_images_manager) {
    $this->routeMatch = $route_match;
    $this->skuManager = $skuManager;
    $this->skuImagesManager = $sku_images_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
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
    if (($node = $event->getContext()) && $node instanceof NodeInterface) {
      if ($node->bundle() == 'acq_product') {
        $sku = $this->skuManager->getSkuForNode($node);
        $sku_entity = SKU::loadFromSku($sku);
        $sku_media = $this->skuImagesManager->getFirstImage($sku_entity);
        $teaser_image = $this->skuManager->getSkuImage($sku_media['drupal_uri'], $sku_entity->label(), 'product_teaser');
        if (!empty($teaser_image['#uri'])) {
          $event->setMetaImage(file_create_url($teaser_image['#uri']));
          $event->stopPropagation();
        }
      }
    }
  }

}
