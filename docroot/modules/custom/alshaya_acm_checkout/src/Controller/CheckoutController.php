<?php

namespace Drupal\alshaya_acm_checkout\Controller;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\alshaya_acm_checkout\CheckoutHelper;
use Drupal\alshaya_addressbook\AddressBookAreasTermsHelper;
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
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides additional urls for checkout pages.
 */
class CheckoutController implements ContainerInjectionInterface {
  use StringTranslationTrait;

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
   * AddressBook Areas Terms helper service.
   *
   * @var \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper
   */
  protected $areasTermsHelper;

  /**
   * Checkout Helper service object.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutHelper
   */
  protected $checkoutHelper;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

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
   * @param \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper $areas_terms_helper
   *   AddressBook Areas Terms helper service.
   * @param \Drupal\alshaya_acm_checkout\CheckoutHelper $checkout_helper
   *   Checkout Helper service object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   */
  public function __construct(CartStorageInterface $cart_storage,
                              EntityTypeManagerInterface $entity_manager,
                              AlshayaAddressBookManager $address_book_manager,
                              MobileNumberUtilInterface $mobile_util,
                              AddressBookAreasTermsHelper $areas_terms_helper,
                              CheckoutHelper $checkout_helper,
                              LoggerChannelFactoryInterface $logger_factory,
                              MessengerInterface $messenger) {
    $this->cartStorage = $cart_storage;
    $this->entityManager = $entity_manager;
    $this->addressBookManager = $address_book_manager;
    $this->mobileUtil = $mobile_util;
    $this->areasTermsHelper = $areas_terms_helper;
    $this->checkoutHelper = $checkout_helper;
    $this->logger = $logger_factory->get('alshaya_acm_checkout');
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_cart.cart_storage'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_addressbook.manager'),
      $container->get('mobile_number.util'),
      $container->get('alshaya_addressbook.area_terms_helper'),
      $container->get('alshaya_acm_checkout.checkout_helper'),
      $container->get('logger.factory'),
      $container->get('messenger')
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
    $cart = $this->cartStorage->getCart(FALSE);

    // If cart no longer available, redirect the user to basket page.
    if (!($cart instanceof CartInterface)) {
      $response = new AjaxResponse();
      $response->addCommand(new RedirectCommand(Url::fromRoute('acq_cart.cart')->toString()));
      return $response;
    }

    $selected_address = $profile->get('field_address')->first()->getValue();
    // If address doesn't have area or area term not exists in drupal.
    if (!$selected_address
      || empty($selected_address['administrative_area'])
      || !$this->entityManager->getStorage('taxonomy_term')->load($selected_address['administrative_area'])) {
      $this->logger->error('User @uid tried to select the address @profile_id which does not have area/city.', [
        '@uid' => $profile->getOwner()->id(),
        '@profile_id' => $profile->id(),
      ]);
      $this->messenger->addError($this->t('Address you selected does not contain required information. Please update address.'));
      $response = new AjaxResponse();
      $response->addCommand(new RedirectCommand(Url::fromRoute('acq_checkout.form', ['step' => 'delivery'], ['query' => ['method' => 'hd']])->toString()));
      return $response;
    }

    $update = $this->checkoutHelper->getFullAddressFromEntity($profile);
    $this->checkoutHelper->setCartShippingHistory(
      'hd',
      $update,
      ['customer_address_id' => $profile->get('field_address_id')->getString()]
    );

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
    $response = new AjaxResponse();

    $magento_address = $this->addressBookManager->getAddressFromEntity($profile);
    $address = $this->addressBookManager->getAddressArrayFromMagentoAddress($magento_address);

    if (isset($address['area_parent'])) {
      $areas = $this->areasTermsHelper->getAllAreasWithParent($address['area_parent']);
      $response->addCommand(new InvokeCommand(NULL, 'updateAreaList', [$areas]));
    }

    $address['id'] = $magento_address['address_id'];
    $address['mobile'] = $this->mobileUtil->getMobileNumberAsString($address['mobile_number']['value']);

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

    $request_params = $request->request->all();
    if (!is_array($request_params)) {
      throw new NotFoundHttpException();
    }

    // Get payment method value dynamically to ensure it doesn't depend on form
    // structure.
    $selected_payment_method = NestedArray::getValue($request_params, explode('[', str_replace(']', '', $element)));

    // Check if we have value available for payment method.
    if (empty($selected_payment_method)) {
      throw new NotFoundHttpException();
    }

    $cart = $this->cartStorage->getCart(FALSE);

    if ($cart instanceof CartInterface) {
      $this->checkoutHelper->setSelectedPayment(
        $selected_payment_method,
        [],
        $this->checkoutHelper->isSurchargeEnabled()
      );
    }

    $response = new AjaxResponse();

    $url = Url::fromRoute('acq_checkout.form', ['step' => 'payment']);
    $response->addCommand(new InvokeCommand(NULL, 'showCheckoutLoader', []));
    $response->addCommand(new RedirectCommand($url->toString()));

    return $response;
  }

}
