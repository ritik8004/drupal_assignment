<?php

namespace Drupal\alshaya_algolia_react\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ToggleAlgoliaProductListEvent.
 *
 * Perform toggle event on product list block.
 *
 * @package Drupal\alshaya_algolia_react
 */
class ToggleAlgoliaProductListEvent extends Event {

  public const EVENT_NAME = 'toggle_algolia_product_list';

  /**
   * Operation performed - enable, disable.
   *
   * @var string
   */
  private $operation;

  /**
   * ToggleAlgoliaProductListEvent constructor.
   *
   * @param string $operation
   *   Operation performed - enable, disable.
   */
  public function __construct(string $operation) {
    $this->operation = $operation;
  }

  /**
   * Get performed operation.
   *
   * @return string
   *   Operation performed - enable, disable.
   */
  public function getOperation() {
    return $this->operation;
  }

}
