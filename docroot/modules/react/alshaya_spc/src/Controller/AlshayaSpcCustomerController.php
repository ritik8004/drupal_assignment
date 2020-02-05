<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

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
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * AlshayaSpcCustomerController constructor.
   *
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper $spc_customer_helper
   *   SPC customer helper.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   */
  public function __construct(AlshayaSpcCustomerHelper $spc_customer_helper,
                              AccountInterface $current_user) {
    $this->spcCustomerHelper = $spc_customer_helper;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_spc.customer_helper'),
      $container->get('current_user')
    );
  }

  /**
   * Get all address list of the current customer.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getCustomerAddressList() {
    $uid = $this->currentUser->getAccount()->id();
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
      $uid = $this->currentUser->getAccount()->id();
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
    $uid = $this->currentUser->getAccount()->id();
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
   * Get user customer id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getUserCustomerId() {
    $response = [
      'customer_id' => NULL,
      'uid' => $this->currentUser->getAccount()->id(),
    ];

    if (!$this->currentUser->isAnonymous()) {
      $response['customer_id'] = $this->currentUser->getAccount()->acq_customer_id;
    }
    return new JsonResponse($response);
  }

}
