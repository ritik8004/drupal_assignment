<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AlshayaSpcExceptionController.
 *
 * @package Drupal\alshaya_spc\Controller
 */
class AlshayaSpcExceptionController extends ControllerBase {

  /**
   * API to get the message type based on message.
   *
   * API to determine the exception type we get on cart update
   * based on the exception message.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getExceptionType(Request $request) {
    $response = [
      'status' => FALSE,
    ];

    // Get message from request.
    $message = $request->query->get('message');

    // If message is for OOS.
    if (_alshaya_acm_is_out_of_stock_exception($message)) {
      $response = [
        'status' => TRUE,
        'type' => 'OOS',
      ];
    }
    // If message is for quantity limit.
    elseif (_alshaya_acm_is_order_limit_exceeded_exception($message)) {
      $response = [
        'status' => TRUE,
        'type' => 'quantity_limit',
      ];
    }

    // @TODO: Check cacheability.
    $json_response = new JsonResponse($response);
    $json_response->setMaxAge(0);
    return $json_response;
  }

}
