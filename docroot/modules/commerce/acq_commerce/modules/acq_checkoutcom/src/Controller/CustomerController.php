<?php

namespace Drupal\acq_checkoutcom\Controller;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Renderer;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\block\Entity\Block;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Customer controller to add/override pages for customer.
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
   * Current time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $currentTime;

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('renderer'),
      $container->get('alshaya_api.api'),
      $container->get('datetime.time'),
      $container->get('date.formatter')
    );
  }

  public function checkAccess() {
    return TRUE;
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
