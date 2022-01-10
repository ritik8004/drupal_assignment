<?php

namespace Drupal\alshaya_acm_product_category\Service;

/**
 * Product category carousel helper service.
 */
interface ProductCategoryCarouselHelperInterface {

  /**
   * Creates and returns the carousel render array.
   *
   * @param int $category_id
   *   Category id value.
   * @param int $carousel_limit
   *   Limit of items to display in the carousel.
   * @param string $carousel_title
   *   Title for the carousel.
   * @param string $view_all_text
   *   The view all button text for the carousel.
   *
   * @return array
   *   The render array for the carousel.
   */
  public function getCarousel($category_id, int $carousel_limit, $carousel_title, $view_all_text);

}
