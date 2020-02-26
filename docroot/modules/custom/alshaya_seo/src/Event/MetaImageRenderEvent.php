<?php

namespace Drupal\alshaya_seo\Event;

use Symfony\Component\EventDispatcher\Event;

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

}
