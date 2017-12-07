<?php

namespace Drupal\alshaya_addressbook;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\profile\Entity\Profile;
use Drupal\user\Entity\User;

/**
 * Class AlshayaAddressBookManager.
 *
 * @package Drupal\alshaya_addressbook
 */
class AlshayaAddressBookManager {

  const AREA_VOCAB = 'area_list';

  /**
   * Entity Repository object.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

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
   * Lanaguage Manager object.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaAddressBookManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository object.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   ApiWrapper object.
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   The MobileNumber util service object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshayaApiWrapper
   *   Alshaya API Wrapper object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language manager object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              EntityRepositoryInterface $entity_repository,
                              APIWrapper $api_wrapper,
                              MobileNumberUtilInterface $mobile_util,
                              LoggerChannelFactoryInterface $logger_factory,
                              AlshayaApiWrapper $alshayaApiWrapper,
                              LanguageManagerInterface $languageManager) {
    $this->entityRepository = $entity_repository;
    $this->profileStorage = $entity_type_manager->getStorage('profile');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->apiWrapper = $api_wrapper;
    $this->mobileUtil = $mobile_util;
    $this->alshayaApiWrapper = $alshayaApiWrapper;
    $this->languageManager = $languageManager;
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
    $dm_version = \Drupal::config('alshaya_addressbook.settings')->get('dm_version');

    $address = [];

    if ($dm_version == DM_VERSION_2) {
      $mapping = $this->getMagentoFieldMappings();

      // Flip the mapping to make it easy below.
      $mapping = array_flip($mapping);

      // Initialise with NULL for all fields to avoid notices.
      foreach ($mapping as $field_code) {
        $address[$field_code] = NULL;
      }

      foreach ($magento_address as $attribute_code => $value) {
        if (is_array($value)) {
          continue;
        }

        if (!isset($mapping[$attribute_code])) {
          continue;
        }

        switch ($mapping[$attribute_code]) {
          case 'mobile_number':
            $address[$mapping[$attribute_code]] = ['value' => $value];
            break;

          default:
            if (isset($mapping[$attribute_code])) {
              $address[$mapping[$attribute_code]] = $value;
            }
            break;
        }
      }

      if (isset($magento_address['extension']) && is_array($magento_address['extension'])) {
        foreach ($magento_address['extension'] as $attribute_code => $value) {
          if (!isset($mapping[$attribute_code])) {
            continue;
          }

          switch ($mapping[$attribute_code]) {
            case 'administrative_area':
            case 'area_parent':
              $term = $this->getLocationTermFromLocationId($value);

              if ($term) {
                $term = $this->entityRepository->getTranslationFromContext($term);

                $address[$mapping[$attribute_code]] = $term->id();
                $address[$mapping[$attribute_code] . '_display'] = $term->label();
              }
              else {
                $address[$mapping[$attribute_code]] = $value;
              }

              break;

            default:
              if (isset($mapping[$attribute_code])) {
                $address[$mapping[$attribute_code]] = $value;
              }
              break;
          }
        }
      }
    }
    else {
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
      }

      $address['country_code'] = $magento_address['country_id'];

      if (isset($magento_address['region'])) {
        $address['region'] = $magento_address['region'];
      }
    }

    return $address;
  }

  /**
   * Get parent location from location id.
   *
   * @param int $area_term_id
   *   Area term id.
   * @param bool $return_translated
   *   Flag to specify if return value is required in current language or not.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   Term if found or null.
   */
  public function getLocationParentTerm($area_term_id, $return_translated = TRUE) {
    if (empty($area_term_id)) {
      return NULL;
    }

    $parents = $this->termStorage->loadParents($area_term_id);

    if (!empty($parents)) {
      $parent = reset($parents);

      if ($return_translated) {
        $parent = $this->entityRepository->getTranslationFromContext($parent);
      }

      return $parent;
    }

    return NULL;
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
    if (empty($location_id)) {
      return NULL;
    }

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
      $terms = $this->termStorage->loadMultiple($tids);
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

    // City is core attribute in Magento and hard to remove validation.
    $magento_address['city'] = '&#8203;';

    $dm_version = \Drupal::config('alshaya_addressbook.settings')->get('dm_version');

    if ($dm_version == DM_VERSION_2) {
      $mapping = $this->getMagentoFieldMappings();
      $custom_fields = $this->getMagentoCustomFields();

      foreach ($mapping as $field_code => $attribute_code) {
        switch ($field_code) {
          case 'mobile_number':
            $magento_address[$attribute_code] = _alshaya_acm_checkout_clean_address_phone($address[$field_code]);
            break;

          case 'administrative_area':
          case 'area_parent':
            $term = $this->termStorage->load($address[$field_code]);

            if (empty($term)) {
              $magento_address['extension'][$attribute_code] = $address[$field_code];
            }
            else {
              $magento_address['extension'][$attribute_code] = $term->get('field_location_id')->getString();
            }
            break;

          default:
            if (isset($custom_fields[$attribute_code])) {
              $magento_address['extension'][$attribute_code] = $address[$field_code];
            }
            else {
              $magento_address[$attribute_code] = $address[$field_code];
            }
        }
      }
    }
    else {
      $magento_address['firstname'] = (string) $address['given_name'];
      $magento_address['lastname'] = (string) $address['family_name'];
      $magento_address['telephone'] = _alshaya_acm_checkout_clean_address_phone($address['mobile_number']);
      $magento_address['street'] = (string) $address['address_line1'];
      $magento_address['extension']['address_apartment_segment'] = (string) $address['address_line2'];
      $magento_address['extension']['address_area_segment'] = (string) $address['administrative_area'];
      $magento_address['extension']['address_building_segment'] = (string) $address['dependent_locality'];
      $magento_address['extension']['address_block_segment'] = (string) $address['locality'];
      $magento_address['country_id'] = $address['country_code'];
    }

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
   * @param string $langcode
   *   Language code.
   *
   * @return \Drupal\taxonomy\Entity\Term|null
   *   Term object if created, or empty.
   */
  public function updateLocation(array $termData, $langcode) {
    if (empty($termData)) {
      return NULL;
    }

    $term = $this->getLocationTermFromLocationId($termData['field_location_id']);

    if ($term && $term->hasTranslation($langcode)) {
      $term = $term->getTranslation($langcode);
    }
    elseif ($term) {
      $term = $term->addTranslation($langcode);
    }

    if (!empty($term)) {
      foreach ($termData as $termKey => $termValue) {
        $term->set($termKey, $termValue);
      }
    }
    else {
      $termData['vid'] = self::AREA_VOCAB;
      $termData['langcode'] = $langcode;
      $term = $this->termStorage->create($termData);
    }

    try {
      $term->save();
    }
    catch (\Exception $e) {
      // Log error if any.
      $this->logger->error($e->getMessage());
    }

    return $term;
  }

  /**
   * Function to sync areas for delivery matrix.
   */
  public function syncAreas() {
    $termsProcessed = [];

    $languages = $this->languageManager->getLanguages();

    foreach ($languages as $langcode => $language) {
      $this->alshayaApiWrapper->updateStoreContext($langcode);

      $governates = $this->alshayaApiWrapper->getLocations('attribute_id', 'governate');

      if (!empty($governates['items'])) {
        foreach ($governates['items'] as $governate) {
          // Assuming Parent ID is 0, for Governates.
          $governateData = [
            'name' => $governate['label'],
            'field_location_id' => $governate['location_id'],
            'parent' => 0,
          ];

          $governateTerm = $this->updateLocation($governateData, $langcode);
          $termsProcessed[$governateTerm->id()] = $governateTerm->id();

          // Fetch area's under this governate.
          $areas = $this->alshayaApiWrapper->getLocations('parent_id', $governate['location_id']);

          if (!empty($areas['items'])) {
            foreach ($areas['items'] as $area) {
              $areaData = [
                'name' => $area['label'],
                'field_location_id' => $area['location_id'],
                'parent' => $governateTerm->id(),
              ];

              $areaTerm = $this->updateLocation($areaData, $langcode);
              $termsProcessed[$areaTerm->id()] = $areaTerm->id();
            }
          }
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

  /**
   * Get address form fields from Magento.
   *
   * @return array
   *   Address form fields.
   */
  public function getMagentoFormFields() {
    // Cache the form per language.
    $cid = 'magento_customer_address_form_' . $this->languageManager->getCurrentLanguage()->getId();

    $cache = \Drupal::cache()->get($cid);

    if ($cache) {
      return $cache->data;
    }

    try {
      $magento_form = $this->alshayaApiWrapper->getCustomerAddressForm();

      if (empty($magento_form)) {
        return [];
      }

      $magento_form = array_filter($magento_form, function ($form_item) {
        return (bool) $form_item['visible'];
      });

      \Drupal::cache()->set($cid, $magento_form);
    }
    catch (\Exception $e) {
      $magento_form = [];
    }

    return $magento_form;
  }

  /**
   * Function to get field mapping betting magento form and address fields.
   *
   * @return array
   *   Mapping Address field <-> Magento form field.
   */
  public function getMagentoFieldMappings() {
    // Fields that can be used in area_parent: governate, city, emirates.
    $mapping = \Drupal::config('alshaya_addressbook.settings')->get('mapping');

    return $mapping;
  }

  /**
   * Helper function to get all custom fields from magento form.
   *
   * @return array
   *   Custom fields array.
   */
  public function getMagentoCustomFields() {
    $custom_fields = [];

    $magento_form = $this->getMagentoFormFields();

    $magento_form = array_filter($magento_form, function ($form_item) {
      return (bool) $form_item['user_defined'];
    });

    foreach ($magento_form as $field) {
      if ($field['user_defined']) {
        $custom_fields[$field['attribute_code']] = 1;
      }
    }

    return $custom_fields;
  }

}
