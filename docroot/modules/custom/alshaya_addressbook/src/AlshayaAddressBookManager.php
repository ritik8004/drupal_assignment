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
    $address_entity->get('field_mobile_number')->setValue($magento_address['telephone']);
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
   *
   * @return bool|string
   *   Commerce Address ID or false.
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
        $customer['addresses'][$index]['default_billing'] = 0;
      }
    }

    $address_id = $entity->get('field_address_id')->getString();
    $new_address = $this->getAddressFromEntity($entity);
    $new_address['customer_id'] = $customer['customer_id'];

    $address_index = FALSE;
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

      // This is not reliable but only solution available.
      // @TODO: Try and find a better solution for this.
      if ($address_index !== FALSE) {
        $updated_address = $updated_customer['addresses'][$address_index];
      }
      else {
        $updated_address = array_pop($updated_customer['addresses']);
      }

      return $updated_address['address_id'];
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
        $customer['addresses'][$index]['default_billing'] = 0;
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

    $address['default_shipping'] = (int) $entity->isDefault();
    $address['default_billing'] = (int) $entity->isDefault();

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
    // Fix for current invalid data.
    if (empty($magento_address['extension'])) {
      $magento_address['extension'] = [
        'address_apartment_segment' => '',
        'address_area_segment' => '',
        'address_block_segment' => '',
        'address_building_segment' => '',
      ];
    }

    $address = [];

    $address['given_name'] = $magento_address['firstname'];
    $address['family_name'] = $magento_address['lastname'];
    $address['mobile_number'] = [
      'value' => $magento_address['telephone'],
    ];
    $address['address_line1'] = $magento_address['street'];
    $address['address_line2'] = $magento_address['extension']['address_apartment_segment'];
    $address['administrative_area'] = $magento_address['extension']['address_area_segment'];
    $address['locality'] = $magento_address['extension']['address_block_segment'];
    $address['dependent_locality'] = $magento_address['extension']['address_building_segment'];

    $address['country_code'] = $magento_address['country_id'];

    if (isset($magento_address['region'])) {
      $address['region'] = $magento_address['region'];
    }

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
    $magento_address['telephone'] = _alshaya_acm_checkout_clean_address_phone($address['mobile_number']);
    $magento_address['street'] = $address['address_line1'];
    $magento_address['extension']['address_apartment_segment'] = $address['address_line2'];
    $magento_address['extension']['address_area_segment'] = $address['administrative_area'];
    $magento_address['extension']['address_building_segment'] = $address['dependent_locality'];
    $magento_address['extension']['address_block_segment'] = $address['locality'];
    $magento_address['country_id'] = $address['country_code'];

    // @TODO: Remove this after Magento makes it optional.
    // This will stay here for long - check MMCSP-916.
    // City is core attribute in Magento and hard to remove validation.
    $magento_address['city'] = '&#8203;';

    return $magento_address;
  }

}
