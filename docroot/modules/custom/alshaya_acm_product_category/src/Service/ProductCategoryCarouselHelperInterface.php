<?php

namespace Drupal\alshaya_acm_product_category\Service;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Product category carousel helper service.
 */
interface ProductCategoryCarouselHelperInterface {

  /**
   * Reuturns the carousel render array.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The source entity containing the data for the carousel.
   *
   * @return array
   *   The render array for the carousel.
   */
  public function getCarousel(ContentEntityInterface $entity);

}
