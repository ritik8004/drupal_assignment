<?php

namespace Drupal\alshaya_acm_checkout\Controller;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\profile\Entity\Profile;
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
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Address Book Manager object.
   *
   * @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager
   */
  protected $addressBookManager;

  /**
   * The mobile utility.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * Constructs a new CheckoutController object.
   *
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity type manager.
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager
   *   Address Book Manager object.
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   The MobileNumber util service object.
   */
  public function __construct(CartStorageInterface $cart_storage, EntityTypeManagerInterface $entity_manager, AlshayaAddressBookManager $address_book_manager, MobileNumberUtilInterface $mobile_util) {
    $this->cartStorage = $cart_storage;
    $this->entityManager = $entity_manager;
    $this->addressBookManager = $address_book_manager;
    $this->mobileUtil = $mobile_util;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_cart.cart_storage'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_addressbook.manager'),
      $container->get('mobile_number.util')
    );
  }

  /**
   * Function to update cart with selected address.
   *
   * @param \Drupal\profile\Entity\Profile $profile
   *   Profile object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   AjaxResponse object.
   */
  public function useAddress(Profile $profile) {
    $cart = $this->cartStorage->getCart();

    $address = $this->addressBookManager->getAddressFromEntity($profile);

    $update = [];
    $update['customer_address_id'] = $address['customer_address_id'];
    $update['country_id'] = $address['country_id'];
    $update['customer_id'] = $cart->customerId();

    $cart->setShipping($update);

    // Clear the shipping method info now to ensure we set it properly again.
    $this->cartStorage->clearShippingMethodSession();

    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'showCheckoutLoader', []));
    $response->addCommand(new RedirectCommand(Url::fromRoute('acq_checkout.form', ['step' => 'delivery'], ['query' => ['method' => 'hd']])->toString()));
    return $response;
  }

  /**
   * Function to return commands to display edit address form in checkout.
   *
   * @param \Drupal\profile\Entity\Profile $profile
   *   Profile object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   AjaxResponse object.
   */
  public function editAddress(Profile $profile) {
    $magento_address = $this->addressBookManager->getAddressFromEntity($profile);
    $address = $this->addressBookManager->getAddressArrayFromMagentoAddress($magento_address);

    $address['id'] = $magento_address['address_id'];
    $address['mobile'] = $this->mobileUtil->getMobileNumberAsString($address['mobile_number']['value']);

    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'editDeliveryAddress', [$address]));
    $response->addCommand(new InvokeCommand('#edit-member-delivery-home-addresses', 'hide', []));
    $response->addCommand(new InvokeCommand('#addresses-header', 'hide', []));
    $response->addCommand(new InvokeCommand(NULL, 'correctFloorFieldLabel', []));
    return $response;
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
  public function selectPaymentMethod(Request $request) {
    $element = $request->request->get('_triggering_element_name');

    // Confirm it is a POST request and contains form data.
    if (empty($element)) {
      throw new NotFoundHttpException();
    }

    // Get governate value dynamically to ensure it doesn't depend on form
    // structure.
    $selected_payment_method = NestedArray::getValue($request->request->all(), explode('[', str_replace(']', '', $element)));

    // Check if we have value available for payment method.
    if (empty($selected_payment_method)) {
      throw new NotFoundHttpException();
    }

    $cart = $this->cartStorage->getCart(FALSE);

    if ($cart instanceof CartInterface) {
      $cart->setPaymentMethod($selected_payment_method);
    }

    $response = new AjaxResponse();

    $url = Url::fromRoute('acq_checkout.form', ['step' => 'payment']);
    $response->addCommand(new InvokeCommand(NULL, 'showCheckoutLoader', []));
    $response->addCommand(new RedirectCommand($url->toString()));

    return $response;
  }

}
