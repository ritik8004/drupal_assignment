<?php

namespace Drupal\alshaya_rcs_listing\Service;

use Drupal\alshaya_search_algolia\Service\AlshayaAlgoliaIndexHelper;

/**
 * Class Alshaya Rcs Algolia Index Helper.
 *
 * @package Drupal\alshaya_rcs_listing\Service
 */
class AlshayaRcsAlgoliaIndexHelper extends AlshayaAlgoliaIndexHelper {

  /**
   * {@inheritDoc}
   */
  public function addCustomFacetToIndex($attributes, $index_name = '') {
    // As part of the RCS migration, we do not anymore create/configure Algolia
    // index from Drupal. So we do not do anything from here.
  }

}
