<?php

namespace Drupal\alshaya_addressbook;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\profile\Entity\Profile;
use Drupal\user\Entity\User;

/**
 * Class AlshayaAddressBookManager.
 *
 * @package Drupal\alshaya_addressbook
 */
class AlshayaAddressBookManager {

  /**
   * Profile storage object.
   *
   * @var \Drupal\profile\ProfileStorageInterface
   */
  protected $profileStorage;

  /**
   * AlshayaAddressBookManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   ApiWrapper object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, APIWrapper $api_wrapper, LoggerChannelFactoryInterface $logger_factory) {
    $this->profileStorage = $entity_type_manager->getStorage('profile');
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('alshaya_addressbook');
  }

  /**
   * Function to load address in Drupal using Magento id.
   *
   * @param int $commerce_id
   *   Commerce address id.
   *
   * @return mixed|null
   *   Address entity if found or NULL.
   */
  public function getUserAddressByCommerceId($commerce_id) {
    if ($addresses = $this->profileStorage->loadByProperties(['field_address_id' => $commerce_id])) {
      return reset($addresses);
    }

    return NULL;
  }

  /**
   * Function to delete all addresses for a user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Account object.
   */
  public function deleteUserAddresses(AccountInterface $account) {
    if ($addresses = $this->profileStorage->loadByProperties(['uid' => $account->id()])) {
      $this->profileStorage->delete($addresses);
    }
  }

  /**
   * Save address from Magento to Drupal.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Account for which address is to be saved.
   * @param array $magento_address
   *   Address array.
   */
  public function saveUserAddressFromApi(AccountInterface $account, array $magento_address) {
    /** @var \Drupal\profile\Entity\Profile $address_entity */
    $address_entity = $this->getUserAddressByCommerceId($magento_address['address_id']);

    if (empty($address_entity)) {
      $address_entity = $this->profileStorage->create([
        'type' => 'address_book',
      ]);
    }

    $address_entity->setOwnerId($account->id());
    $address_entity->get('field_address_id')->setValue($magento_address['address_id']);
    $address_entity->get('field_mobile_number')->setValue($magento_address['phone']);
    $address_entity->setDefault((int) $magento_address['default_shipping']);

    $address = $this->getAddressArrayFromMagentoAddress($magento_address);
    $address_entity->get('field_address')->setValue($address);

    $address_entity->save();
  }

  /**
   * Push address changes to Magento.
   *
   * @param \Drupal\profile\Entity\Profile $entity
   *   Address Entity.
   */
  public function pushUserAddressToApi(Profile $entity) {
    /** @var \Drupal\acq_commerce\Conductor\APIWrapper $apiWrapper */
    $account = User::load($entity->getOwnerId());

    $customer = $this->apiWrapper->getCustomer($account->getEmail());
    unset($customer['extension']);

    foreach ($customer['addresses'] as $index => $address) {
      $customer['addresses'][$index] = $this->getCleanAddress($address);
      $customer['addresses'][$index]['customer_id'] = $customer['customer_id'];
      $customer['addresses'][$index]['customer_address_id'] = $address['address_id'];

      if ($entity->isDefault()) {
        $customer['addresses'][$index]['default_shipping'] = 0;
      }
    }

    $address_id = $entity->get('field_address_id')->getString();
    $new_address = $this->getAddressFromEntity($entity);
    $new_address['customer_id'] = $customer['customer_id'];

    if ($address_id) {
      $address_index = array_search($address_id, array_column($customer['addresses'], 'address_id'));
      if ($address_index !== FALSE) {
        $customer['addresses'][$address_index] = $new_address;
      }
      else {
        // We will treat this as new address.
        $address_id = '';
        unset($new_address['address_id']);
        $customer['addresses'][] = $new_address;
      }
    }
    else {
      $customer['addresses'][] = $new_address;
    }

    try {
      $updated_customer = $this->apiWrapper->updateCustomer($customer);

      \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.utility');

      // Update the data in Drupal to match the values in Magento.
      alshaya_acm_customer_update_user_data($account, $updated_customer);

      return TRUE;
    }
    catch (\Exception $e) {
      $this->logger->warning('Error while saving address: @message', ['@message' => $e->getMessage()]);
      drupal_set_message($e->getMessage(), 'error');
    }

    return FALSE;
  }

  /**
   * Push address changes to Magento.
   *
   * @param \Drupal\profile\Entity\Profile $entity
   *   Address Entity.
   */
  public function deleteUserAddressFromApi(Profile $entity) {
    /** @var \Drupal\acq_commerce\Conductor\APIWrapper $apiWrapper */
    $account = User::load($entity->getOwnerId());

    $customer = $this->apiWrapper->getCustomer($account->getEmail());
    unset($customer['extension']);

    $address_id = $entity->get('field_address_id')->getString();

    foreach ($customer['addresses'] as $index => $address) {
      if ($address['address_id'] == $address_id) {
        unset($customer['addresses'][$index]);
        continue;
      }

      $customer['addresses'][$index] = $this->getCleanAddress($address);
      $customer['addresses'][$index]['customer_id'] = $customer['customer_id'];
      $customer['addresses'][$index]['customer_address_id'] = $address['address_id'];

      if ($entity->isDefault()) {
        $customer['addresses'][$index]['default_shipping'] = 0;
      }
    }

    try {
      $updated_customer = $this->apiWrapper->updateCustomer($customer);

      \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.utility');

      // Update the data in Drupal to match the values in Magento.
      alshaya_acm_customer_update_user_data($account, $updated_customer);

      return TRUE;
    }
    catch (\Exception $e) {
      $this->logger->warning('Error while deleting address: @message', ['@message' => $e->getMessage()]);
      drupal_set_message($e->getMessage(), 'error');
    }

    return FALSE;
  }

  /**
   * Hack to get sync working.
   *
   * @param array $address
   *   Address array.
   *
   * @return array
   *   Cleaned/Hacked address array.
   */
  protected function getCleanAddress(array $address) {
    $address['street'] = [
      $address['street'],
      $address['street2'],
    ];

    $address['country_id'] = $address['country'];
    $address['telephone'] = $address['phone'];

    unset($address['street2']);
    unset($address['phone']);

    // This is very annoying, when we save in Magento it allows to save region
    // but when we pass from here it gives error because Kuwait doesn't have
    // region.
    unset($address['region']);

    return $address;
  }

  /**
   * Function to get address array to send to Magento from Entity.
   *
   * @param \Drupal\profile\Entity\Profile $entity
   *   Address entity.
   * @param bool $return_clean
   *   Flag to specify if cleaned address is required in response or not.
   *
   * @return array
   *   Address array.
   */
  public function getAddressFromEntity(Profile $entity, $return_clean = TRUE) {
    $address_id = $entity->get('field_address_id')->getString();
    $entity_address = $entity->get('field_address')->first()->getValue();

    $entity_address['mobile_number'] = '';
    if ($phone = $entity->get('field_mobile_number')->first()->getValue()) {
      $entity_address['mobile_number'] = $phone['value'];
    }

    $address = $this->getMagentoAddressFromAddressArray($entity_address);

    if ($address_id) {
      $address['address_id'] = (int) $address_id;
      $address['customer_address_id'] = $address['address_id'];
    }

    // @TODO: Check why this is different, it is phone in checkout.
    $address['telephone'] = $address['phone'];

    $address['default_shipping'] = (int) $entity->isDefault();

    return $return_clean ? $this->getCleanAddress($address) : $address;
  }

  /**
   * Convert drupal address into magento address.
   *
   * @param array $magento_address
   *   Magento address.
   *
   * @return array
   *   Drupal address.
   */
  public function getAddressArrayFromMagentoAddress(array $magento_address) {
    $address = [];

    $address['given_name'] = $magento_address['firstname'];
    $address['family_name'] = $magento_address['lastname'];
    $address['organization'] = $magento_address['email'];
    $address['mobile_number'] = [
      'value' => $magento_address['phone'],
    ];
    $address['address_line1'] = $magento_address['street'];
    $address['address_line2'] = $magento_address['street2'];
    $address['administrative_area'] = '';
    $address['locality'] = '';
    $address['dependent_locality'] = $magento_address['region'];
    $address['locality'] = $magento_address['city'];
    $address['country_code'] = $magento_address['country'];

    return $address;
  }

  /**
   * Convert magento address into drupal address.
   *
   * @param array $address
   *   Drupal address.
   *
   * @return array
   *   Magento address.
   */
  public function getMagentoAddressFromAddressArray(array $address) {
    $magento_address = [];

    $magento_address['firstname'] = $address['given_name'];
    $magento_address['lastname'] = $address['family_name'];
    $magento_address['email'] = $address['organization'];
    $magento_address['phone'] = _alshaya_acm_checkout_clean_address_phone($address['mobile_number']);
    $magento_address['street'] = $address['address_line1'];
    $magento_address['street2'] = $address['address_line2'];
    $magento_address['region'] = $address['dependent_locality'];
    $magento_address['city'] = $address['locality'];
    $magento_address['country'] = $address['country_code'];

    // @TODO: Remove this after Magento gets the address form fields proper.
    $magento_address['postcode'] = '30000';

    return $magento_address;
  }

}
