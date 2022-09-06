<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class Alshaya Spc Customer Helper.
 */
class AlshayaSpcCustomerHelper {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The spc cookies handler..
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcCookies
   */
  protected $spcCookies;

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\Session
   */
  protected $session;

  /**
   * Address book manager.
   *
   * @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager
   */
  protected $addressBookManager;

  /**
   * AlshayaSpcCustomerHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   The api wrapper.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcCookies $spc_cookies
   *   The spc cookies handler.
   * @param \Symfony\Component\HttpFoundation\Session\Session $session
   *   The session.
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager
   *   Address book manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AlshayaApiWrapper $api_wrapper,
    ModuleHandlerInterface $module_handler,
    AlshayaSpcCookies $spc_cookies,
    Session $session,
    AlshayaAddressBookManager $address_book_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->apiWrapper = $api_wrapper;
    $this->moduleHandler = $module_handler;
    $this->spcCookies = $spc_cookies;
    $this->session = $session;
    $this->addressBookManager = $address_book_manager;
  }

  /**
   * Get list of addresses for the customer.
   *
   * @param int $uid
   *   User id.
   * @param bool $default
   *   If we want only default address.
   *
   * @return array
   *   Address list.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCustomerAllAddresses(int $uid, bool $default = FALSE) {
    $user = $this->entityTypeManager->getStorage('user')->load($uid);
    if ($default) {
      $profiles = [];
      if ($default_profile = $this->entityTypeManager->getStorage('profile')
        ->loadDefaultByUser($user, 'address_book')) {
        $profiles[] = $default_profile;
      }
    }
    else {
      $profiles = $this->entityTypeManager->getStorage('profile')
        ->loadMultipleByUser($user, 'address_book');
    }

    $addressList = [];

    /** @var \Drupal\profile\Entity\Profile $profile */
    foreach ($profiles as $profile) {
      $address_data = [];
      $valid_address = TRUE;
      $address_data[$profile->id()] = array_filter($profile->get('field_address')->first()->getValue());
      $address_data[$profile->id()]['firstname'] = $address_data[$profile->id()]['given_name'];
      $address_data[$profile->id()]['lastname'] = $address_data[$profile->id()]['family_name'];
      $address_data[$profile->id()]['is_default'] = $profile->isDefault();
      $address_data[$profile->id()]['address_id'] = $profile->id();
      $address_data[$profile->id()]['email'] = $user->getEmail();
      $address_data[$profile->id()]['customer_id'] = $user->get('acq_customer_id')->getString();
      $address_data[$profile->id()]['address_mdc_id'] = $profile->get('field_address_id')->getString();

      $address_data[$profile->id()]['mobile'] = ($mobile = $profile->get('field_mobile_number')->first())
        ? $mobile->getValue()
        : '';

      if ($this->addressBookManager->isAreaParentUsed()) {
        $parent_term = $this->addressBookManager->getLocationParentTerm($address_data[$profile->id()]['administrative_area']);
        if ($parent_term) {
          $address_data[$profile->id()]['area_parent'] = $parent_term->get('field_location_id')->first()->getValue()['value'];
          $address_data[$profile->id()]['area_parent_label'] = $parent_term->label();
        }
        else {
          // Address contains city id for which term not available.
          $valid_address = FALSE;
        }
      }

      // We get the area as term id but we need the location id
      // of that term.
      if ($address_data[$profile->id()]['administrative_area']) {
        $term = $this->entityTypeManager->getStorage('taxonomy_term')
          ->load($address_data[$profile->id()]['administrative_area']);
        if ($term) {
          $address_data[$profile->id()]['administrative_area'] = $term->get('field_location_id')->first()->getValue()['value'];
          $address_data[$profile->id()]['area_label'] = $term->label();
        }
        else {
          // Address contains area id for which term not available.
          $valid_address = FALSE;
        }
      }

      // If address doesn't contain mobile number.
      if (empty($address_data[$profile->id()]['mobile'])) {
        $valid_address = FALSE;
      }

      // Flag determines whether address is valid or not on FE.
      $address_data[$profile->id()]['validAddress'] = $valid_address;
      $addressList[] = $address_data[$profile->id()];
    }

    return $addressList;
  }

  /**
   * Changes the default address of the customer.
   *
   * @param int $profile
   *   Profile id.
   * @param int $uid
   *   User id.
   *
   * @return bool
   *   True if profile is saved as default.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateCustomerDefaultAddress(int $profile, int $uid) {
    $return = FALSE;
    try {
      /** @var \Drupal\profile\Entity\Profile $profile */
      $profile = $this->entityTypeManager->getStorage('profile')->load($profile);
      // If profile is valid and belongs to the user.
      if ($profile && $profile->getOwnerId() == $uid) {
        $profile->setDefault(TRUE);
        if ($this->addressBookManager->pushUserAddressToApi($profile)) {
          $return = TRUE;
        }
      }
    }
    catch (\Exception) {
      $return = FALSE;
    }

    return $return;
  }

  /**
   * Deletes the address of the user.
   *
   * @param int $profile
   *   Profile id.
   * @param int $uid
   *   User id.
   *
   * @return bool
   *   True if deleted successfully.
   */
  public function deleteCustomerAddress(int $profile, int $uid) {
    $return = FALSE;
    try {
      /** @var \Drupal\profile\Entity\Profile $profile */
      $profile = $this->entityTypeManager->getStorage('profile')->load($profile);
      // If address belongs to the current user.
      if ($profile && $profile->getOwnerId() == $uid) {
        // If user tyring to delete default address.
        if ($profile->isDefault()) {
          $return = FALSE;
        }
        else {
          // Delete the address.
          if ($this->addressBookManager->deleteUserAddressFromApi($profile)) {
            $profile->delete();
            $return = TRUE;
          }
        }
      }
      else {
        $return = FALSE;
      }
    }
    catch (\Exception) {
      $return = FALSE;
    }

    return $return;
  }

  /**
   * Adds customer address.
   *
   * @param array $address
   *   Address array.
   * @param int $uid
   *   User id.
   * @param bool $isDefault
   *   Whether address needs to be default or not.
   *
   * @return bool|string
   *   Response.
   */
  public function addEditCustomerAddress(array $address, int $uid, bool $isDefault = TRUE) {
    try {
      $address_data = $address['address'];
      // If address already exists.
      if (!empty($address_data['address_id'])) {
        $profile = $this->entityTypeManager->getStorage('profile')->load($address_data['address_id']);
      }
      else {
        $profile = $this->entityTypeManager->getStorage('profile')->create([
          'type' => 'address_book',
          'uid' => $uid,
        ]);
        $profile->setOwnerId($uid);
      }

      // Prepare mobile info.
      $mobile_info = [
        'country' => _alshaya_custom_get_site_level_country_code(),
        'local_number' => $address['mobile'],
        'value' => '+' . _alshaya_spc_get_country_mobile_code() . $address['mobile'],
      ];

      // Get and use location term based on location id.
      if (!empty($address_data['administrative_area'])) {
        if ($location_term = _alshaya_spc_get_location_term_by_location_id($address_data['administrative_area'])) {
          $address_data['administrative_area'] = $location_term->id();
        }
      }

      // Get and use location term based on location id.
      if (!empty($address_data['area_parent'])) {
        if ($location_term = _alshaya_spc_get_location_term_by_location_id($address_data['area_parent'])) {
          $address_data['area_parent'] = $location_term->id();
        }
      }

      $address_data['country_code'] = _alshaya_custom_get_site_level_country_code();
      $profile->get('field_address')->setValue($address_data);
      $profile->get('field_mobile_number')->setValue($mobile_info);

      return $this->addressBookManager->pushUserAddressToApi($profile, $isDefault);
    }
    catch (\Exception) {
      return FALSE;
    }
  }

  /**
   * Helper function to authenticate user from Magento.
   *
   * @param string $mail
   *   Mail.
   * @param string $pass
   *   Password.
   *
   * @return int|mixed|string|null
   *   User id of user if successful or null.
   *
   * @throws \Exception
   */
  public function authenticateCustomer($mail, $pass) {
    global $_alshaya_acm_customer_addressbook_processed;

    try {
      $customer = $this->apiWrapper->getCustomerUsingAuthDetails($mail, $pass);

      if (!empty($customer['customer_id'])) {
        // Set the token in session.
        $this->setCustomerTokenInSession($customer['token']);
        $this->moduleHandler->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.utility');

        // @todo Remove this condition when we uninstall alshaya_acm module.
        $cart_id = $this->spcCookies->getSessionCartId();
        if (empty($cart_id) && !empty($customer['extension']['cart_id'])) {
          // @phpcs:ignore
          \Drupal::service('acq_cart.cart_storage')->clearCart();
          user_cookie_delete('acq_cart_id');
        }

        // Check if user exists in Drupal.
        if ($user = user_load_by_mail($mail)) {
          // Update the data in Drupal to match the values in Magento.
          alshaya_acm_customer_update_user_data($user, $customer);
        }
        // Create user.
        else {
          /** @var \Drupal\user\Entity\User $user */
          $user = alshaya_acm_customer_create_drupal_user($customer);
        }
        $_alshaya_acm_customer_addressbook_processed = TRUE;

        return array_merge($customer, ['user' => $user]);
      }
    }
    catch (\Exception $e) {
      throw $e;
    }

    return NULL;
  }

  /**
   * Helper function to get a token from Magento using Social Details.
   *
   * @param string $mail
   *   The email address.
   *
   * @return string|null
   *   The token or null.
   */
  public function loadCustomerTokenForSocialAccount($mail) {
    $token = $this->getCustomerToken();
    if (empty($token) || !is_string($token)) {
      $token = json_decode($this->apiWrapper->getCustomerTokenBySocialDetail($mail), NULL);
      if ($token === FALSE) {
        $token = NULL;
      }
      $this->setCustomerTokenInSession($token);
    }

    return $token;
  }

  /**
   * Helper function to set the customer token for API calls.
   *
   * @param string|null $token
   *   The token.
   */
  private function setCustomerTokenInSession($token) {
    $this->session->set('magento_customer_token', $token);
  }

  /**
   * Helper function to get the customer token for API calls.
   *
   * @return string
   *   The token.
   */
  public function getCustomerToken() {
    return $this->session->get('magento_customer_token', NULL);
  }

}
