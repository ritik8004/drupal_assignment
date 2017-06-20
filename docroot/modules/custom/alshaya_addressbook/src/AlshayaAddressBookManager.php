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
   * @param array $address
   *   Address array.
   */
  public function saveUserAddressFromApi(AccountInterface $account, array $address) {
    /** @var \Drupal\profile\Entity\Profile $address_entity */
    $address_entity = $this->getUserAddressByCommerceId($address['address_id']);

    if (empty($address_entity)) {
      $address_entity = $this->profileStorage->create([
        'type' => 'address_book',
      ]);
    }

    $address_entity->setOwnerId($account->id());
    $address_entity->get('field_address_id')->setValue($address['address_id']);
    $address_entity->get('field_mobile_number')->setValue($address['phone']);

    $address_entity->get('field_address')->setValue([
      'given_name' => $address['firstname'],
      'family_name' => $address['lastname'],
      'address_line1' => $address['street'],
      'address_line2' => $address['street2'],
      'dependent_locality' => $address['region'],
      'locality' => $address['city'],
      'country_code' => $address['country'],
    ]);

    $address_entity->setDefault((int) $address['default_shipping']);

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

    $current_ids = [];

    foreach ($customer['addresses'] as $index => $address) {
      $customer['addresses'][$index] = $this->getCleanAddress($address);
      $customer['addresses'][$index]['customer_id'] = $customer['customer_id'];
      $customer['addresses'][$index]['customer_address_id'] = $address['address_id'];

      if ($entity->isDefault()) {
        $customer['addresses'][$index]['default_shipping'] = 0;
      }

      $current_ids[] = $address['address_id'];
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

    $address = [];

    if ($address_id) {
      $address['address_id'] = (int) $address_id;
      $address['customer_address_id'] = $address['address_id'];
    }

    $address['firstname'] = $entity_address['given_name'];
    $address['lastname'] = $entity_address['family_name'];
    $address['street'] = $entity_address['address_line1'];
    $address['street2'] = $entity_address['address_line2'];
    $address['city'] = $entity_address['locality'];
    $address['country'] = $entity_address['country_code'];

    $address['default_shipping'] = (int) $entity->isDefault();

    $address['phone'] = '';
    if ($phone = $entity->get('field_mobile_number')->first()->getValue()) {
      $address['phone'] = $phone['value'];
    }

    // @TODO: Remove this after Magento gets the address form fields proper.
    $address['postcode'] = '30000';

    return $return_clean ? $this->getCleanAddress($address) : $address;
  }

}
