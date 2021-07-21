<?php

namespace Drupal\rcs_placeholders\Service;

/**
 * Interface for RcsPhHelper service.
 */
interface RcsPhHelperInterface {

  /**
   * Return the placeholder term data from rcs_category.
   */
  public function getRcsPhCategoryTermData();

}
