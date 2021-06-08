<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class Alshaya Spc Customer Controller.
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
    if ($address_id = $this->spcCustomerHelper->addEditCustomerAddress($data['address'], $uid, $data['isDefault'])) {
      $response['error'] = FALSE;
      $response['data'] = $this->spcCustomerHelper->getCustomerAllAddresses($uid);
      foreach ($response['data'] as $address) {
        if ($address['address_mdc_id'] == $address_id) {
          $response['data'] = [$address];
          break;
        }
      }
    }
    else {
      $response = [
        'error' => TRUE,
        'error_message' => _alshaya_spc_global_error_message(),
      ];
    }

    return new JsonResponse($response);
  }

  /**
   * Get user customer id.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getUserCustomerId(Request $request) {
    $response = [
      'customer_id' => 0,
      'uid' => 0,
    ];

    if ($this->currentUser()->isAuthenticated()) {
      $user = $this->entityTypeManager()->getStorage('user')->load($this->currentUser()->id());
      $customer_id = $user->get('acq_customer_id')->getString();

      if ($customer_id) {
        $response['customer_id'] = (int) $customer_id;

        // Drupal CORE uses numeric 0 for anonymous but string for logged in.
        // We follow the same.
        $response['uid'] = $user->id();
      }
    }

    return new JsonResponse($response);
  }

  /**
   * Gets the bearer token from session.
   *
   * If its not in the session, it retrieves from Magento.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getCustomerToken() {
    $token = '';
    if ($this->currentUser()->isAuthenticated()) {
      $token = $this->session->get('magento_customer_token');
      if (empty($token) || !is_string($token)) {
        $mail = $this->currentUser()->getEmail();
        $token = $this->spcCustomerHelper->getCustomerTokenBySocialDetail($mail);
      }
    }

    if (empty($token)) {
      return new JsonResponse($token, 404);
    }

    return new JsonResponse($token);
  }

}
