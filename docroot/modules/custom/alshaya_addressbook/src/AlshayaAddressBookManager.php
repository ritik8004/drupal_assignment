<?php

namespace Drupal\alshaya_addressbook;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\profile\Entity\Profile;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;

/**
 * Class AlshayaAddressBookManager.
 *
 * @package Drupal\alshaya_addressbook
 */
class AlshayaAddressBookManager {

  const AREA_VOCAB = 'area_list';

  /**
   * Profile storage object.
   *
   * @var \Drupal\profile\ProfileStorageInterface
   */
  protected $profileStorage;

  /**
   * The mobile utility.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * Term storage object.
   *
   * @var \Drupal\taxonomy\TermStorage
   */
  protected $termStorage;

  /**
   * Alshaya API Wrapper object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $alshayaApiWrapper;

  /**
   * AlshayaAddressBookManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   ApiWrapper object.
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   The MobileNumber util service object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshayaApiWrapper
   *   Alshaya API Wrapper object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              APIWrapper $api_wrapper,
                              MobileNumberUtilInterface $mobile_util,
                              LoggerChannelFactoryInterface $logger_factory,
                              AlshayaApiWrapper $alshayaApiWrapper) {
    $this->profileStorage = $entity_type_manager->getStorage('profile');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->apiWrapper = $api_wrapper;
    $this->mobileUtil = $mobile_util;
    $this->alshayaApiWrapper = $alshayaApiWrapper;
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
    // Last discussion in MMCPA-2042.
    // We always set the address last added or edited as primary.
    $entity->setDefault(TRUE);

    /** @var \Drupal\acq_commerce\Conductor\APIWrapper $apiWrapper */
    $account = User::load($entity->getOwnerId());

    try {
      $customer = $this->apiWrapper->getCustomer($account->getEmail());
    }
    catch (\Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
      return FALSE;
    }

    unset($customer['extension']);

    foreach ($customer['addresses'] as $index => $address) {
      $customer['addresses'][$index] = $address;
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

      $customer['addresses'][$index] = $address;
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
   * Function to get address array to send to Magento from Entity.
   *
   * @param \Drupal\profile\Entity\Profile $entity
   *   Address entity.
   *
   * @return array
   *   Address array.
   */
  public function getAddressFromEntity(Profile $entity) {
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

    return $address;
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
        'area' => '',
        'governate' => '',
      ];
    }

    $address = [];

    $address['given_name'] = $magento_address['firstname'];
    $address['family_name'] = $magento_address['lastname'];
    $address['mobile_number'] = [
      'value' => $magento_address['telephone'],
    ];
    $address['address_line1'] = $magento_address['street'];

    $address['address_line2'] = '';
    $address['administrative_area'] = '';
    $address['locality'] = '';
    $address['dependent_locality'] = '';

    if (isset($magento_address['extension']) && is_array($magento_address['extension'])) {
      if (isset($magento_address['extension']['address_apartment_segment'])) {
        $address['address_line2'] = $magento_address['extension']['address_apartment_segment'];
      }

      if (isset($magento_address['extension']['address_area_segment'])) {
        $address['administrative_area'] = $magento_address['extension']['address_area_segment'];
      }

      if (isset($magento_address['extension']['address_block_segment'])) {
        $address['locality'] = $magento_address['extension']['address_block_segment'];
      }

      if (isset($magento_address['extension']['address_building_segment'])) {
        $address['dependent_locality'] = $magento_address['extension']['address_building_segment'];
      }

      if (isset($magento_address['extension']['area'])) {
        $term = $this->getLocationTermFromLocationId($magento_address['extension']['area']);
        $address['administrative_area'] = $term->id();
        $address['administrative_area_display'] = $term->label();
      }

      if (isset($magento_address['extension']['governate'])) {
        $term = $this->getLocationTermFromLocationId($magento_address['extension']['governate']);
        $address['locality'] = $term->id();
        $address['locality_display'] = $term->label();
      }
    }

    $address['country_code'] = $magento_address['country_id'];

    if (isset($magento_address['region'])) {
      $address['region'] = $magento_address['region'];
    }

    return $address;
  }

  /**
   * Get location term from location id.
   *
   * @param mixed $location_id
   *   Location id.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   Term if found or null.
   */
  public function getLocationTermFromLocationId($location_id) {
    $terms = $this->getLocationTerms([
      [
        'field' => 'field_location_id',
        'value' => $location_id,
      ],
    ]);

    if (!empty($terms)) {
      return reset($terms);
    }

    return NULL;
  }

  /**
   * Helper method to fetch TID from area vocab, from the param provided.
   *
   * @param array $conditions
   *   An array of associative array containing conditions, to be used in query,
   *   with following elements:
   *   - 'field': Name of the field being queried.
   *   - 'value': The value for field.
   *   - 'operator': Possible values like '=', '<>', '>', '>=', '<', '<='.
   *
   * @return array
   *   Array of term objects.
   */
  private function getLocationTerms(array $conditions = []) {
    $terms = [];
    $query = \Drupal::entityQuery('taxonomy_term')->condition('vid', self::AREA_VOCAB);
    foreach ($conditions as $condition) {
      if (!empty($condition['field']) && !empty($condition['value'])) {
        $condition['operator'] = empty($condition['operator']) ? '=' : $condition['operator'];
        $query->condition($condition['field'], $condition['value'], $condition['operator']);
      }
    }
    $tids = $query->execute();
    if (!empty($tids)) {
      $terms = Term::loadMultiple($tids);
    }

    return $terms;
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

    $magento_address['firstname'] = (string) $address['given_name'];
    $magento_address['lastname'] = (string) $address['family_name'];
    $magento_address['telephone'] = _alshaya_acm_checkout_clean_address_phone($address['mobile_number']);
    $magento_address['street'] = (string) $address['address_line1'];
    $magento_address['extension']['address_apartment_segment'] = (string) $address['address_line2'];
    $magento_address['extension']['address_area_segment'] = (string) $address['administrative_area'];
    $magento_address['extension']['address_building_segment'] = (string) $address['dependent_locality'];
    $magento_address['country_id'] = $address['country_code'];

    $dm_version = \Drupal::config('alshaya_addressbook.settings')->get('dm_version');

    if ($dm_version == DM_VERSION_2) {
      $area = $this->termStorage->load($address['administrative_area']);

      if (!empty($area)) {
        $magento_address['extension']['address_area_segment'] = $area->label();
        $magento_address['extension']['area'] = $area->get('field_location_id')->getString();

        // This will be available only in R2.
        $governate = current($this->termStorage->loadParents($area->id()));
        if (!empty($governate)) {
          $magento_address['extension']['governate'] = $governate->get('field_location_id')->getString();
        }
      }
    }
    else {
      // This is used in V2 as governate.
      $magento_address['extension']['address_block_segment'] = (string) $address['locality'];
    }

    // City is core attribute in Magento and hard to remove validation.
    $magento_address['city'] = '&#8203;';
    return $magento_address;
  }

  /**
   * Helper function to get magento address structure.
   *
   * @return array
   *   Magento address array with empty values.
   */
  public function getAddressStructureWithEmptyValues() {
    $magento_address = [];

    $magento_address['firstname'] = '&#8203;';
    $magento_address['lastname'] = '&#8203;';
    $magento_address['telephone'] = '&#8203;';
    $magento_address['street'] = '&#8203;';
    $magento_address['extension']['address_apartment_segment'] = '&#8203;';
    $magento_address['extension']['address_area_segment'] = '&#8203;';
    $magento_address['extension']['address_building_segment'] = '&#8203;';
    $magento_address['extension']['address_block_segment'] = '&#8203;';
    $magento_address['extension']['area'] = '&#8203;';
    $magento_address['extension']['governate'] = '&#8203;';
    $magento_address['country_id'] = _alshaya_custom_get_site_level_country_code();
    $magento_address['city'] = '&#8203;';

    return $magento_address;
  }

  /**
   * Utility function to validate profile address.
   *
   * @param array $address_values
   *   Address values.
   *
   * @return array
   *   Errors if found else empty array.
   */
  public function validateAddress(array $address_values) {
    $errors = [];

    /** @var \Drupal\profile\Entity\Profile $profile */
    $profile = $this->profileStorage->create([
      'type' => 'address_book',
      'uid' => 0,
      'field_address' => $address_values,
      'field_mobile_number' => $address_values['mobile_number'],
    ]);

    /* @var \Drupal\Core\Entity\EntityConstraintViolationListInterface $violations */
    if ($violations = $profile->validate()) {
      foreach ($violations->getByFields(['field_address']) as $violation) {
        $error_field = explode('.', $violation->getPropertyPath());
        $errors[$error_field[2]] = $violation->getMessage();
      }

      foreach ($violations->getByFields(['field_mobile_number']) as $violation) {
        $errors['organization'] = $violation->getMessage();
      }
    }

    $mobile = $address_values['mobile_number'];

    $mobile_country_code = '+' . $this->mobileUtil->getCountryCode($mobile['country-code']);

    // Remove the country code prefix added in mobile field.
    $mobile['mobile'] = str_replace($mobile_country_code, '', $mobile['mobile']);

    $mobile_number = $this->mobileUtil->getMobileNumber($mobile['mobile'], $mobile['country-code']);

    if (empty($mobile_number)) {
      $errors['mobile_number][mobile'] = t('The phone number %value provided for %field is not a valid mobile number for country %country.', [
        '%value' => $mobile['mobile'],
        '%field' => t('Mobile Number'),
        '%country' => $this->mobileUtil->getCountryName($mobile['country-code']),
      ]);
    }

    return $errors;
  }

  /**
   * Function to update location as term.
   *
   * @param array $termData
   *   Term data to save as term.
   *
   * @return mixed
   *   Term object if created, or empty.
   */
  public function updateLocation(array $termData = []) {
    if (empty($termData)) {
      return;
    }

    // @todo: Translations missing.
    $term = $this->getLocationTermFromLocationId($termData['field_location_id']);

    if (!empty($term)) {
      foreach ($termData as $termKey => $termValue) {
        $term->set($termKey, $termValue);
      }
    }
    else {
      $termData['vid'] = self::AREA_VOCAB;
      $term = Term::create($termData);
    }

    try {
      $term->save();
    }
    catch (\Exception $e) {
      // Log error if any.
      \Drupal::logger('alshaya_addressbook')->error($e->getMessage());
    }

    return $term;
  }

  /**
   * Function to sync areas for delivery matrix.
   */
  public function syncAreas() {
    $governates = $this->alshayaApiWrapper->getLocations('attribute_id', 'governate');
    if (!empty($governates['items'])) {
      $termsProcessed = [];
      foreach ($governates['items'] as $governate) {
        // Assuming Parent ID is 0, for Governates.
        $governateData = [
          'name' => $governate['labels'][0],
          'field_location_id' => $governate['location_id'],
          'parent' => 0,
        ];
        /** @var \Drupal\taxonomy\Entity\Term $governateTerm */
        $governateTerm = $this->updateLocation($governateData);
        $termsProcessed[] = $governateTerm->id();

        // Fetch area's under this governate.
        $areas = $this->alshayaApiWrapper->getLocations('parent_id', $governate['location_id']);
        if (!empty($areas['items'])) {
          foreach ($areas['items'] as $area) {
            $areaData = [
              'name' => $area['labels'][0],
              'field_location_id' => $area['location_id'],
              'parent' => $governateTerm->id(),
            ];
            $areaTerm = $this->updateLocation($areaData);
            $termsProcessed[] = $areaTerm->id();
          }
        }
      }

      // Delete the excess terms that exist.
      if (!empty($termsProcessed)) {
        $result = \Drupal::entityQuery('taxonomy_term')
          ->condition('vid', self::AREA_VOCAB)
          ->condition('tid', $termsProcessed, 'NOT IN')
          ->execute();

        if ($result) {
          $terms = $this->termStorage->loadMultiple($result);
          $this->termStorage->delete($terms);
        }
      }

    }
  }

}
