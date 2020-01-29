<?php

namespace Drupal\dynamic_yield\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class DyPageType.
 *
 * @package Drupal\dynamic_yield\Event
 */
class DyPageType extends Event {
  const DY_SET_CONTEXT = 'dy.set.context';

  /**
   * Dynamic yield Context.
   *
   * @var string
   */
  protected $dycontext;

  /**
   * Dynamic yield Context Data.
   *
   * @var array
   */
  protected $dyContextData;

  /**
   * Set Dynamic yield page context.
   *
   * @param string $context
   *   Page context as required by DY.
   */
  public function setDyContext($context) {
    $this->dycontext = $context;
  }

  /**
   * Get Dynamic yield page context.
   *
   * @return string
   *   Page context as required by DY.
   */
  public function getDyContext() {
    return $this->dycontext;
  }

  /**
   * Get Dynamic Yield Context data.
   *
   * @return array
   *   Dynamic yield Context data.
   */
  public function getDyContextData() {
    return $this->dyContextData;
  }

  /**
   * Set Dynamic Yield Context data.
   *
   * @param array $data
   *   Dynamic yield Context data.
   */
  public function setDyContextData(array $data) {
    $this->dyContextData = $data;
    $this->stopPropagation();
  }

}
