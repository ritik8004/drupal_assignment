<?php

namespace Drupal\alshaya_algolia_react\Services;

/**
 * Interface for Alshaya Algolia React Config service.
 */
interface AlshayaAlgoliaReactConfigInterface {

  /**
   * Public function of the service.
   *
   * @param string $page_type
   *   Page Type.
   */
  public function getAlgoliaReactCommonConfig(string $page_type);

}
