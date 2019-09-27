<?php

namespace Drupal\alshaya_algolia_react\Controller;

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
    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-algolia-search"></div>',
      '#cache' => [
        'contexts' => ['languages'],
      ],
    ];
  }

}
