<?php

namespace Drupal\alshaya_acm_checkout\Controller;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\profile\Entity\Profile;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    $response = new AjaxResponse();
    $response->addCommand(new RedirectCommand(Url::fromRoute('acq_checkout.form', ['step' => 'delivery'])->toString()));
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
    return $response;
  }

}
