<?php

namespace Drupal\alshaya_algolia_react\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Customer controller to add front page.
 */
class AlgoliaController extends ControllerBase {

  /**
   * Returns the build for home page.
   *
   * @return array
   *   Build array.
   */
  public function search() {
    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-algolia-search-page"></div>',
      '#attached' => [
        'drupalSettings' => [
          'algoliaSearch' => [
            'showSearchResults' => TRUE,
          ],
        ],
      ],
    ];
  }

}
