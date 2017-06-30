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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

    $address = $this->addressBookManager->getAddressFromEntity($profile, FALSE);

    $update = [];
    $update['customer_address_id'] = $address['customer_address_id'];
    $update['country'] = $address['country'];
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
    $magento_address = $this->addressBookManager->getAddressFromEntity($profile, FALSE);
    $address = $this->addressBookManager->getAddressArrayFromMagentoAddress($magento_address);

    $address['id'] = $magento_address['address_id'];
    $address['mobile'] = $this->mobileUtil->getMobileNumberAsString($address['mobile_number']['value']);

    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'editDeliveryAddress', [$address]));
    return $response;
  }

  /**
   * Function to get the cart stores.
   *
   * @param int $cart_id
   *   The cart id.
   * @param float $lat
   *   The latitude.
   * @param float $lon
   *   The longitude.
   *
   * @return array
   *   Return the array of all available stores.
   */
  public function getCartStores($cart_id, $lat = NULL, $lon = NULL) {

    // Get the stores from Magento.
    $api_wrapper = \Drupal::service('alshaya_api.api');
    $stores = $api_wrapper->getCartStores($cart_id, $lat, $lon);

    // Add missing information to store data.
    array_walk($stores, function (&$store) {
      $store_utility = \Drupal::service('alshaya_stores_finder.utility');
      $extra_data = $store_utility->getStoreExtraData($store);
      if (!empty($extra_data)) {
        $store = array_merge($extra_data, $extra_data);
      }
      else {
        $store['name'] = $store['code'];
        $store['address'] = $store['code'];
        $store['opening_hours'] = $store['code'];
      }
    });
    return $stores;
  }

  /**
   * Function to get the cart stores.
   *
   * @param int $cart_id
   *   The cart id.
   * @param float $lat
   *   The latitude.
   * @param float $lon
   *   The longitude.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return json response to use in jquery ajax.
   */
  public function getCartStoresJson($cart_id, $lat = NULL, $lon = NULL) {
    $stores = $this->getCartStores($cart_id, $lat, $lon);
    $output = t('Sorry, No Store found for selected location.');
    if (count($stores) > 0) {
      $build = [
        '#theme' => 'click_collect_stores_list',
        '#title' => t('Available at @count stores', ['@count' => count($stores)]),
        '#stores' => $stores,
      ];
      $output = render($build);
    }

    return new JsonResponse(['output' => $output, 'raw' => $stores]);
  }

  /**
   * Render selected store html.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Output the rendered html with selected store information.
   */
  public function selectedStore() {
    // Get all the post data, which contains store information passed in ajax.
    $store = \Drupal::request()->request->all();
    $output = t("There's no store selected.");
    if (!empty($store)) {
      $build = [
        '#theme' => 'click_collect_selected_store',
        '#store' => $store,
      ];
      $output = render($build);
    }

    return new JsonResponse(['output' => render($build), 'raw' => $store]);
  }

  /**
   * Map view of the selected store.
   */
  public function storeMapView() {
    return new JsonResponse(['output' => '']);
  }

}
