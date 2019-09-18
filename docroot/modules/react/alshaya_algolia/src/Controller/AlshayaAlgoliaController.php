<?php

namespace Drupal\alshaya_algolia\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class AlshayaReactTestController.
 *
 * @package Drupal\alshaya_react_test\Controller
 */
class AlshayaAlgoliaController extends ControllerBase {

  /**
   * Test page to check react is working.
   *
   * @return array
   *   Return array of markup with react lib attached.
   */
  public function testPage() {
    $config = \Drupal::configFactory()->get('search_api.server.algolia')->get('backend_config');
    $index = \Drupal::configFactory()->get('search_api.index.acquia_search_index')->get('options');
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-algolia-search"></div>',
      '#attached' => [
        'library' => [
          'alshaya_algolia/test',
        ],
        'drupalSettings' => [
          'algoliaSearch' => [
            'application_id' => $config['application_id'],
            'api_key' => $config['api_key'],
            'indexName' => $index['algolia_index_name'] . "_{$lang}",
          ],
        ],
      ],
      '#cache' => [
        'contexts' => [ 'languages' ],
      ],
    ];
  }

}
