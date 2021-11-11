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
   * @param string $sub_page_type
   *   Sub Page Type.
   */
  public function getAlgoliaReactCommonConfig(string $page_type, string $sub_page_type = '');

}
