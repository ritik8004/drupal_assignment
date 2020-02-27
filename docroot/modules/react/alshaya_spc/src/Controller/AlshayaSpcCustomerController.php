<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class AlshayaSpcCustomerController.
 */
class AlshayaSpcCustomerController extends ControllerBase {

  /**
   * SPC customer helper.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper
   */
  protected $spcCustomerHelper;

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\Session
   */
  protected $session;

  /**
   * AlshayaSpcCustomerController constructor.
   *
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper $spc_customer_helper
   *   SPC customer helper.
   * @param \Symfony\Component\HttpFoundation\Session\Session $session
   *   The session.
   */
  public function __construct(
    AlshayaSpcCustomerHelper $spc_customer_helper,
    Session $session
  ) {
    $this->spcCustomerHelper = $spc_customer_helper;
    $this->session = $session;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_spc.customer_helper'),
      $container->get('session')
    );
  }

  /**
   * Get all address list of the current customer.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getCustomerAddressList() {
    $uid = $this->currentUser()->getAccount()->id();
    $addressList = $this->spcCustomerHelper->getCustomerAllAddresses($uid);

    return new JsonResponse($addressList);
  }

  /**
   * Set address as default address for the customer.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function setCustomerDefaultAddress(Request $request) {
    $data = json_decode($request->getContent(), TRUE);
    $request->request->replace(is_array($data) ? $data : []);
    $response = [];
    try {
      $uid = $this->currentUser()->getAccount()->id();
      if ($this->spcCustomerHelper->updateCustomerDefaultAddress($data['address_id'], $uid)) {
        $response['data'] = $this->spcCustomerHelper->getCustomerAllAddresses($uid);
        $response['status'] = TRUE;
      }
      else {
        $response['status'] = FALSE;
      }
    }
    catch (\Exception $e) {
      $response['status'] = FALSE;
    }

    return new JsonResponse($response);
  }

  /**
   * Delete the customer address.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function deleteCustomerAddress(Request $request) {
    $response = [];
    $data = json_decode($request->getContent(), TRUE);
    $request->request->replace(is_array($data) ? $data : []);
    $uid = $this->currentUser()->getAccount()->id();
    if ($this->spcCustomerHelper->deleteCustomerAddress($data['address_id'], $uid)) {
      $response['status'] = TRUE;
      $response['data'] = $this->spcCustomerHelper->getCustomerAllAddresses($uid);
    }
    else {
      $response['status'] = FALSE;
    }

    return new JsonResponse($response);
  }

  /**
   * Adds/Edit customer address.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function addEditCustomerAddress(Request $request) {
    $response = [];
    $data = json_decode($request->getContent(), TRUE);
    $request->request->replace(is_array($data) ? $data : []);
    $uid = $this->currentUser()->getAccount()->id();
    $this->spcCustomerHelper->addEditCustomerAddress($data['address'], $uid);
    $response['status'] = TRUE;
    $response['data'] = $this->spcCustomerHelper->getCustomerAllAddresses($uid, TRUE);
    return new JsonResponse($response);
  }

  /**
   * Get user customer id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getUserCustomerId() {
    $currentUser = $this->currentUser()->getAccount();
    $response = [
      'customer_id' => $currentUser->acq_customer_id,
      'uid' => $currentUser->id(),
    ];

    return new JsonResponse($response);
  }

  /**
   * Get customer cart for logged in user.
   */
  public function getUserCustomerCart() {
    $currentUser = $this->currentUser()->getAccount();
    return new JsonResponse([
      'cart_id' => $this->session->get('customer_cart_id'),
      'customer_id' => $currentUser->acq_customer_id,
      'uid' => $currentUser->id(),
    ]);
  }

}
