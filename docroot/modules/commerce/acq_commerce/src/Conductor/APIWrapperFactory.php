<?php

namespace Drupal\acq_commerce\Conductor;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class API Wrapper Factory.
 */
class APIWrapperFactory {

  /**
   * Creates an APIWrapperInterface object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The current service container.
   *
   * @return \Drupal\acq_commerce\Conductor\APIWrapperInterface
   *   An api wrapper object.
   */
  public static function get(ContainerInterface $container) {
    $test_mode = $container
      ->get('config.factory')
      ->get('acq_commerce.conductor')
      ->get('test_mode');

    if ($test_mode) {
      return $container->get('acq_commerce.test_agent_api');
    }

    return $container->get('acq_commerce.agent_api');
  }

}
