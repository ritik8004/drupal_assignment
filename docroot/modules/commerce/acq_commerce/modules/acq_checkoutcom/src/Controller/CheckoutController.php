<?php

namespace Drupal\acq_checkoutcom\Controller;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides additional urls for checkout pages.
 */
class CheckoutController implements ContainerInjectionInterface {

  /**
   * The cart session.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * CheckoutController constructor.
   *
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module services.
   */
  public function __construct(
    CartStorageInterface $cart_storage,
    ModuleHandlerInterface $module_handler
  ) {
    $this->cartStorage = $cart_storage;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_cart.cart_storage'),
      $container->get('module_handler')
    );
  }

  /**
   * AJAX callback to select payment method.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   AJAX Response.
   */
  public function submitMakePaymentForm(Request $request) {
    $request_params = $request->request->all();
    if (!is_array($request_params)) {
      throw new NotFoundHttpException();
    }

    $errors = [];
    $response = new AjaxResponse();

    $success_event = $request_params['acm_payment_methods']['payment_options'] === 'checkout_com'
      ? 'checkoutCardPaymentSuccess'
      : 'checkoutApplePaymentSuccess';

    // Allow other modules to validate the request data.
    $this->moduleHandler->alter('acq_checkoutcom_payment_form_validate', $errors, $request_params);
    $response->addCommand(
      new InvokeCommand(
        NULL,
        !empty($errors) ? 'checkoutPaymentError' : $success_event,
        !empty($errors) ? [$errors] : []
      )
    );

    // Initiate card token request if no error found.
    if (empty($errors)
      && isset($request_params['acm_payment_methods']['payment_options'])
      && $request_params['acm_payment_methods']['payment_options'] !== 'checkout_com_applepay') {
      $response->addCommand(new InvokeCommand(NULL, 'checkoutComCreateCardToken', []));
    }

    return $response;
  }

}
