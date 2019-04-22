<?php

namespace Drupal\acq_checkoutcom\Controller;

use Drupal\acq_cart\CartInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides the checkout form page.
 */
class CheckoutComController {

  use StringTranslationTrait;

//  /**
//   * Constructs a new CheckoutComController object.
//   */
//  public function __construct() {
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public static function create() {
//    return new static();
//  }

  /**
   * Page callback to process checkoutcom response.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   */
  public function status(Request $request) {
    $post_data = $request->query->get('cko-payment-token');

    $url = "https://sandbox.checkout.com/api2/v2/charges/$post_data";
    $header = [
      'Content-Type: application/json;charset=UTF-8',
      'Authorization: sk_test_863d1545-5253-4387-b86b-df6a86797baa',
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $output = curl_exec($ch);

    curl_close($ch);
    $decoded = json_decode($output, TRUE);
    echo '<pre>';
    print_r($decoded);
    echo '</pre>';
    die();
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
  public function selectCardType(Request $request) {
    $element = $request->request->get('_triggering_element_name');

    // Confirm it is a POST request and contains form data.
    if (empty($element)) {
      throw new NotFoundHttpException();
    }

    $request_params = $request->request->all();
    if (!is_array($request_params)) {
      throw new NotFoundHttpException();
    }

    // Get payment method value dynamically to ensure it doesn't depend on form
    // structure.
    $selected_card_type = NestedArray::getValue($request_params, explode('[', str_replace(']', '', $element)));

    // Check if we have value available for payment method.
    if (empty($selected_card_type)) {
      throw new NotFoundHttpException();
    }

    $response = new AjaxResponse();

    $url = Url::fromRoute('acq_checkout.form', ['step' => 'payment'], ['query' => ['type' => $selected_card_type]]);
    $response->addCommand(new InvokeCommand(NULL, 'showCheckoutLoader', []));
    $response->addCommand(new RedirectCommand($url->toString()));

    return $response;
  }

}