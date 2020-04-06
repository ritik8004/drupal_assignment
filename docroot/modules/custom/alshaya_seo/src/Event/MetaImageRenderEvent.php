<?php

namespace Drupal\alshaya_seo\Event;

use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Event that is fired when meta image token processed.
 */
class MetaImageRenderEvent extends Event {

  const EVENT_NAME = 'meta_image_render';

  /**
   * Meta image path.
   *
   * @var string
   */
  protected $metaImage;

  /**
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * MetaImageRenderEvent constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route Match Object.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * Set meta image.
   *
   * @param string $metaImage
   *   Meta image.
   */
  public function setMetaImage($metaImage) {
    $this->metaImage = $metaImage;
  }

  /**
   * Get meta image.
   *
   * @return string
   *   Meta image.
   */
  public function getMetaImage() {
    return $this->metaImage;
  }

  /**
   * Get page context for node.
   */
  public function getContext() {
    if ($this->routeMatch->getRouteName() !== 'entity.node.canonical') {
      return;
    }
    else {
      return $this->routeMatch->getParameter('node');
    }
  }

}
