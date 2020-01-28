<?php

namespace Drupal\dynamic_yield\Event;

use Drupal\dynamic_yield\DynamicYieldService;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DyPageType.
 *
 * @package Drupal\dynamic_yield\Event
 */
class DyPageType extends Event {
  const DY_SET_CONTEXT = 'dy.set.context';

  /**
   * Request definition.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * DynamicYieldService definition.
   *
   * @var \Drupal\dynamic_yield\DynamicYieldService
   */
  protected $dyService;

  /**
   * DyPageType constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request service.
   * @param \Drupal\dynamic_yield\DynamicYieldService $dynamicYieldService
   *   Dynamic yield service.
   */
  public function __construct(Request $request, DynamicYieldService $dynamicYieldService) {
    $this->request = $request;
    $this->dyService = $dynamicYieldService;
  }

  /**
   * Get the current request.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   Request service.
   */
  public function getRequest() {
    return $this->request;
  }

  /**
   * Get the inserted dynamic yield service.
   *
   * @return \Drupal\dynamic_yield\DynamicYieldService
   *   Dynamic yield service.
   */
  public function getDyService() {
    return $this->dyService;
  }

}
