<?php

namespace Drupal\acq_checkoutcom\Controller;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Renderer;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CustomerController.
 *
 * @package Drupal\acq_checkoutcom\Controller
 */
class CustomerController extends ControllerBase {

  /**
   * Renderer service object.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * CustomerController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   Renderer service object.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   */
  public function __construct(
    Request $current_request,
    Renderer $renderer,
    AlshayaApiWrapper $api_wrapper
  ) {
    $this->currentRequest = $current_request;
    $this->renderer = $renderer;
    $this->apiWrapper = $api_wrapper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('renderer'),
      $container->get('alshaya_api.api')
    );
  }

  /**
   * Helper method to check access.
   *
   * @return bool
   *   Return TRUE to allow access, false otherwise.
   */
  public function checkAccess() {
    return TRUE;
  }

  /**
   * Returns the list of saved cards.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders list page is being viewed.
   */
  public function listCards(UserInterface $user) {

  }

  /**
   * Returns the list of saved cards.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders list page is being viewed.
   *
   * @return array
   *   Build array.
   */
  public function addCard(UserInterface $user) {
    return $this->formBuilder()->getForm('\Drupal\acq_checkoutcom\Form\CustomerCardForm');
  }

}
