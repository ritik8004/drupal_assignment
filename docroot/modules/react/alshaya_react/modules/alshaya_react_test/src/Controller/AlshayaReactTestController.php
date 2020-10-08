<?php

namespace Drupal\alshaya_react_test\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class Alshaya React Test Controller.
 *
 * @package Drupal\alshaya_react_test\Controller
 */
class AlshayaReactTestController extends ControllerBase {

  /**
   * Test page to check react is working.
   *
   * @return array
   *   Return array of markup with react lib attached.
   */
  public function testPage() {
    return [
      '#type' => 'markup',
      '#markup' => '<div id="simple-bundle"></div><div id="custom-bundle"></div>',
      '#attached' => [
        'library' => [
          'alshaya_react_test/test',
        ],
      ],
    ];
  }

}
