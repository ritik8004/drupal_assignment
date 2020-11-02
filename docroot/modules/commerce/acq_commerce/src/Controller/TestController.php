<?php

namespace Drupal\acq_commerce\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Test Controller class.
 */
class TestController extends ControllerBase {

  /**
   * The api wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * TestController constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   The api wrapper.
   */
  public function __construct(APIWrapper $api_wrapper) {
    $this->apiWrapper = $api_wrapper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_commerce.api'),
    );
  }

  /**
   * Callback for acq_commerce.test route.
   */
  public function testConnection() {
    return [
      '#markup' => print_r($this->apiWrapper->systemWatchdog(), TRUE),
    ];
  }

}
