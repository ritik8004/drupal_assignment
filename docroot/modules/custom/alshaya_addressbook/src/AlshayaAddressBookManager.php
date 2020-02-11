<?php

namespace Drupal\alshaya_addressbook;

use Drupal\acq_cart\CartInterface;
use Drupal\alshaya_acm\CartHelper;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\profile\Entity\Profile;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class AlshayaAddressBookManager.
 *
 * @package Drupal\alshaya_addressbook
 */
class AlshayaAddressBookManager implements AlshayaAddressBookManagerInterface {

  use StringTranslationTrait;

  const INVISIBLE_CHARACTER = '&#8203;';

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
   * User storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

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
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Cache Backend object for "cache.addressbook".
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AddressBook Areas Terms helper service.
   *
   * @var \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper
   */
  protected $areasTermsHelper;

  /**
   * Cart Helper service object.
   *
   * @var \Drupal\alshaya_acm\CartHelper
   */
  protected $cartHelper;

  /**
   * AlshayaAddressBookManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository object.
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   The MobileNumber util service object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshayaApiWrapper
   *   Alshaya API Wrapper object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language manager object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend object for "cache.addressbook".
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper $areas_terms_helper
   *   AddressBook Areas Terms helper service.
   * @param \Drupal\alshaya_acm\CartHelper $cart_helper
   *   Cart Helper service object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              EntityRepositoryInterface $entity_repository,
                              MobileNumberUtilInterface $mobile_util,
                              LoggerChannelFactoryInterface $logger_factory,
                              AlshayaApiWrapper $alshayaApiWrapper,
                              LanguageManagerInterface $languageManager,
                              ConfigFactoryInterface $config_factory,
                              CacheBackendInterface $cache,
                              ModuleHandlerInterface $module_handler,
                              AddressBookAreasTermsHelper $areas_terms_helper,
                              CartHelper $cart_helper) {
    $this->entityRepository = $entity_repository;
    $this->profileStorage = $entity_type_manager->getStorage('profile');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->userStorage = $entity_type_manager->getStorage('user');
    $this->mobileUtil = $mobile_util;
    $this->alshayaApiWrapper = $alshayaApiWrapper;
    $this->languageManager = $languageManager;
    $this->logger = $logger_factory->get('alshaya_addressbook');
    $this->configFactory = $config_factory;
    $this->cache = $cache;
    $this->moduleHandler = $module_handler;
    $this->areasTermsHelper = $areas_terms_helper;
    $this->cartHelper = $cart_helper;
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
   * Function to get addresses from profile.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Account object.
   *
   * @return mixed
   *   An array of address profile objects.
   */
  public function getUserAddressesFromProfile(AccountInterface $account) {
    $address_profiles = $this->profileStorage->loadByProperties([
      'uid' => $account->id(),
      'type' => 'address_book',
    ]);

    return $address_profiles;
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

    $account = $this->userStorage->load($entity->getOwnerId());

    try {
      $customer = $this->alshayaApiWrapper->getCustomer($account->getEmail());
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
      $updated_customer = $this->alshayaApiWrapper->updateCustomer($customer);

      $this->moduleHandler->loadInclude('alshaya_api', 'inc', 'alshaya_api.utility');

      // Update the data in Drupal to match the values in Magento.
      alshaya_api_update_user_data($account, $updated_customer);

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
    $account = $this->userStorage->load($entity->getOwnerId());

    $customer = $this->alshayaApiWrapper->getCustomer($account->getEmail());
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
      $updated_customer = $this->alshayaApiWrapper->updateCustomer($customer);

      $this->moduleHandler->loadInclude('alshaya_api', 'inc', 'alshaya_api.utility');

      // Update the data in Drupal to match the values in Magento.
      alshaya_api_update_user_data($account, $updated_customer);

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
    // Get the value from mobile number field only when the field has value.
    if (($mobile_number = $entity->get('field_mobile_number')) && !$mobile_number->isEmpty()) {
      $entity_address['mobile_number'] = $mobile_number->first()->getValue();
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
   * Helper function to convert Magento raw address to Drupal format.
   *
   * @param array $magento_address
   *   Magento address returned by direct Magento API call.
   *
   * @return array
   *   Address array that Drupal understands.
   */
  public function getAddressArrayFromRawMagentoAddress(array $magento_address) {
    $address = [];

    $custom_fields = $this->getMagentoCustomFields();

    foreach ($magento_address as $line_item) {
      if (isset($custom_fields[$line_item['code']])) {
        $address['extension'][$line_item['code']] = $line_item['value'];
      }
      else {
        $address[$line_item['code']] = $line_item['value'];
      }
    }

    // @TODO: Get this corrected in Magento if possible.
    if (empty($address['country_id'])) {
      $address['country_id'] = _alshaya_custom_get_site_level_country_code();
    }

    return $this->getAddressArrayFromMagentoAddress($address);
  }

  /**
   * Convert drupal address into magento address.
   *
   * @param array $magento_address
   *   Magento address.
   * @param bool $default_lang
   *   If true, return address in english.
   *
   * @return array
   *   Drupal address.
   */
  public function getAddressArrayFromMagentoAddress(array $magento_address, $default_lang = FALSE) {
    $address = [];

    if ($this->getDmVersion() == AlshayaAddressBookManagerInterface::DM_VERSION_2) {
      $mapping = $this->getMagentoFieldMappings();

      // Flip the mapping to make it easy below.
      $mapping = array_flip($mapping);

      $magento_form = $this->getMagentoFormFields();

      // Initialise with NULL for all fields to avoid notices.
      foreach ($mapping as $field_code) {
        $address[$field_code] = NULL;
      }

      foreach ($magento_address as $attribute_code => $value) {
        if (is_array($value)) {
          continue;
        }

        if (!isset($mapping[$attribute_code]) || !isset($magento_form[$attribute_code])) {
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
          if (!isset($mapping[$attribute_code]) || !isset($magento_form[$attribute_code])) {
            continue;
          }

          switch ($mapping[$attribute_code]) {
            case 'administrative_area':
            case 'area_parent':
              $term = $this->areasTermsHelper->getLocationTermFromLocationId($value);

              if ($term) {
                if (!$default_lang) {
                  $term = $this->entityRepository->getTranslationFromContext($term);
                }
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
      $address['sorting_code'] = '';
      $address['additional_name'] = '';
      $address['postal_code'] = '';

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

        if (isset($magento_address['extension']['landmark'])) {
          $address['sorting_code'] = $magento_address['extension']['landmark'];
        }

        if (isset($magento_address['extension']['apartment'])) {
          $address['additional_name'] = $magento_address['extension']['apartment'];
        }

        if (isset($magento_address['extension']['postcode'])) {
          $address['postal_code'] = $magento_address['extension']['postcode'];
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
    $magento_address['city'] = self::INVISIBLE_CHARACTER;

    if ($this->getDmVersion() == AlshayaAddressBookManagerInterface::DM_VERSION_2) {
      $mapping = $this->getMagentoFieldMappings();
      $custom_fields = $this->getMagentoCustomFields();

      foreach ($mapping as $field_code => $attribute_code) {
        switch ($field_code) {
          case 'mobile_number':
            $magento_address[$attribute_code] = isset($address[$field_code])
              ? _alshaya_acm_checkout_clean_address_phone($address[$field_code])
              : '';
            break;

          case 'area_parent':
            if (empty($address['area_parent']) && !empty($address['administrative_area'])) {
              $parent = $this->getLocationParentTerm($address['administrative_area']);
              $address[$field_code] = $parent ? $parent->id() : NULL;
            }
          case 'administrative_area':
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
              $magento_address['extension'][$attribute_code] = isset($address[$field_code])
                ? $address[$field_code]
                : '';
            }
            elseif (isset($address[$field_code])) {
              $magento_address[$attribute_code] = $address[$field_code];
            }
        }
      }

      // Add parent id for area if blank and we are using DMV2.
      // This is exceptional case for Kuwait where we do not show the
      // governate but Magento needs it.
      if (isset($mapping, $mapping['area_parent'])
        && (empty($address['area_parent']) || $address['area_parent'] === self::INVISIBLE_CHARACTER)) {
        $parent = $this->getLocationParentTerm($address['administrative_area']);
        if ($parent) {
          $magento_address['extension'][$mapping['area_parent']] = $parent->get('field_location_id')->getString();
        }
      }

      // @TODO: CORE-2332 - Fix this properly, possibly in Magento.
      // City is Magento core field but we don't use it at all.
      // But this is required by Cybersource so we need proper value.
      // For now, we copy value of Area to City.
      /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
      $magento_address['city'] = $this->getAddressShippingAreaValue($magento_address);
    }
    else {
      $magento_address['firstname'] = (string) $address['given_name'];
      $magento_address['lastname'] = (string) $address['family_name'];
      $magento_address['telephone'] = _alshaya_acm_checkout_clean_address_phone($address['mobile_number']);
      $magento_address['street'] = (string) $address['address_line1'];
      $magento_address['extension']['address_apartment_segment'] = (string) $address['address_line2'];
      $magento_address['extension']['address_area_segment'] = (string) $address['administrative_area'];
      $magento_address['extension']['address_building_segment'] = (string) $address['dependent_locality'];
      $magento_address['extension']['landmark'] = (string) $address['sorting_code'];
      $magento_address['extension']['apartment'] = (string) $address['additional_name'];
      $magento_address['extension']['postcode'] = (string) $address['postal_code'];
      $magento_address['extension']['address_block_segment'] = (string) $address['locality'];
      $magento_address['country_id'] = $address['country_code'];
    }

    return $magento_address;
  }

  /**
   * Helper function to get magento address structure.
   *
   * @param string|null $empty_value
   *   Empty value to set in address fields. Leave this empty to use invisible
   *   character.
   *
   * @return array
   *   Magento address array with empty values.
   */
  public function getAddressStructureWithEmptyValues($empty_value = NULL) {
    $empty_value = isset($empty_value) ? $empty_value : self::INVISIBLE_CHARACTER;

    $magento_address = [];

    $magento_address['firstname'] = $empty_value;
    $magento_address['lastname'] = $empty_value;
    $magento_address['telephone'] = $empty_value;
    $magento_address['street'] = $empty_value;
    $magento_address['extension']['address_apartment_segment'] = $empty_value;
    $magento_address['extension']['address_area_segment'] = $empty_value;
    $magento_address['extension']['address_building_segment'] = $empty_value;
    $magento_address['extension']['address_block_segment'] = $empty_value;
    $magento_address['extension']['area'] = $empty_value;
    $magento_address['extension']['governate'] = $empty_value;
    $magento_address['extension']['address_city_segment'] = $empty_value;
    $magento_address['country_id'] = _alshaya_custom_get_site_level_country_code();
    $magento_address['city'] = $empty_value;

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
      $errors['mobile_number][mobile'] = $this->t('The phone number %value provided for %field is not a valid mobile number for country %country.', [
        '%value' => $mobile['mobile'],
        '%field' => $this->t('Mobile Number'),
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

    $term = $this->areasTermsHelper->getLocationTermFromLocationId($termData['field_location_id']);

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
      $termData['vid'] = AlshayaAddressBookManagerInterface::AREA_VOCAB;
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
    $mapping = $this->getMagentoFieldMappings();

    $termsProcessed = [];

    $languages = $this->languageManager->getLanguages();

    foreach ($languages as $langcode => $language) {
      $this->alshayaApiWrapper->updateStoreContext($langcode);

      if (isset($mapping['area_parent'])) {
        // Use the magento field name from mapping.
        $governates = $this->alshayaApiWrapper->getLocations('attribute_id', $mapping['area_parent']);

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
      else {
        // Sync directly the areas.
        // Use the magento field name from mapping.
        $areas = $this->alshayaApiWrapper->getLocations('attribute_id', $mapping['administrative_area']);

        if (!empty($areas['items'])) {
          foreach ($areas['items'] as $area) {
            $data = [
              'name' => $area['label'],
              'field_location_id' => $area['location_id'],
              'parent' => 0,
            ];

            $term = $this->updateLocation($data, $langcode);
            $termsProcessed[$term->id()] = $term->id();
          }
        }
      }
    }

    // Delete the excess terms that exist.
    if (!empty($termsProcessed)) {
      $result = $this->termStorage->getQuery()
        ->condition('vid', AlshayaAddressBookManagerInterface::AREA_VOCAB)
        ->condition('tid', $termsProcessed, 'NOT IN')
        ->execute();

      if ($result) {
        $terms = $this->termStorage->loadMultiple($result);
        $this->termStorage->delete($terms);
      }
    }

    $this->logger->notice('Area sync completed.');
  }

  /**
   * Helper function to reset Magento Form fields cache.
   */
  public function resetMagentoFormFields() {
    foreach ($this->languageManager->getLanguages() as $language) {
      $this->getMagentoFormFields($language->getId(), TRUE);
    }
  }

  /**
   * Get address form fields from Magento.
   *
   * @param string $langcode
   *   Language code.
   * @param bool $reset
   *   Reset cached data and fetch again.
   *
   * @return array
   *   Address form fields.
   */
  public function getMagentoFormFields($langcode = NULL, $reset = FALSE) {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    // Cache the form per language.
    $cid = 'magento_customer_address_form_' . $langcode;

    $cache = $reset ? NULL : $this->cache->get($cid);

    if ($cache) {
      return $cache->data;
    }

    try {
      $this->alshayaApiWrapper->updateStoreContext($langcode);
      $magento_form = $this->alshayaApiWrapper->getCustomerAddressForm();
      $this->alshayaApiWrapper->resetStoreContext();

      if (empty($magento_form)) {
        return [];
      }

      $magento_form = array_filter($magento_form, function ($form_item) {
        return (bool) $form_item['visible'] && $form_item['status'];
      });

      foreach ($magento_form as $index => $form_item) {
        if (isset($form_item['attribute'])) {
          // Copy values from attribute to main array.
          $form_item = array_merge($form_item['attribute'], $form_item);
          unset($form_item['attribute']);
        }

        $magento_form[$form_item['attribute_code']] = $form_item;
        unset($magento_form[$index]);
      }

      // Array keys based on sort_order.
      uasort($magento_form, function ($a, $b) {
        if ($a['sort_order'] == $b['sort_order']) {
          return 0;
        }
        return ($a['sort_order'] < $b['sort_order']) ? -1 : 1;
      });
      $this->cache->set($cid, $magento_form);
    }
    catch (\Exception $e) {
      $magento_form = [];
    }

    return $magento_form;
  }

  /**
   * Function to get field mapping between magento form and address fields.
   *
   * @return array
   *   Mapping Address field <-> Magento form field.
   */
  public function getMagentoFieldMappings() {
    static $mapping;

    if (empty($mapping)) {
      // Fields that can be used in area_parent: governate, city, emirates.
      $mapping = $this->configFactory->get('alshaya_addressbook.settings')->get('mapping');
      $mapping = array_filter($mapping);
    }

    return $mapping;
  }

  /**
   * Function to get field mapping between magento form and address fields.
   *
   * @return array
   *   Mapping Address field <-> Magento form field.
   */
  public function getMagentoUnmappedFields() {
    static $field_unmapped = NULL;

    if (!is_array($field_unmapped)) {
      $mapping = array_flip($this->getMagentoFieldMappings());
      $form_fields = $this->getMagentoFormFields();

      foreach ($form_fields as $form_item) {
        if (empty($form_item['attribute_code'])) {
          continue;
        }

        if (isset($mapping[$form_item['attribute_code']])) {
          $field_code = $mapping[$form_item['attribute_code']];
          $fields_mapped[$form_item['attribute_code']] = $field_code;
        }
      }

      $field_unmapped = array_diff($mapping, $fields_mapped);
    }

    return $field_unmapped;
  }

  /**
   * Function to get fields disabled in magento form and available in address.
   *
   * @return array
   *   Mapped but disabled Address field <-> Magento form field.
   */
  public function getMagentoDisabledFields() {
    static $disabled;

    if (empty($disabled)) {
      // Fields that can be used in area_parent: governate, city, emirates.
      $mapping = $this->configFactory->get('alshaya_addressbook.settings')->get('mapping');
      $enabled = $this->getMagentoFieldMappings();
      $disabled = array_diff($mapping, $enabled);
    }

    return $disabled;
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

  /**
   * Get Area label for Shipping Address from Cart.
   *
   * This function takes care of DM V1/V2.
   *
   * @param \Drupal\acq_cart\CartInterface $cart
   *   Cart object.
   *
   * @return string|null
   *   String value for the area or NULL if shipping value not available.
   */
  public function getCartShippingAreaValue(CartInterface $cart) {
    if (!$cart->getShippingMethodAsString()) {
      return '';
    }

    $shipping = $this->cartHelper->getShipping($cart);
    return $this->getAddressShippingAreaValue($shipping);
  }

  /**
   * Get City label for Shipping Address from Cart.
   *
   * This function takes care of DM V1/V2.
   *
   * @param \Drupal\acq_cart\CartInterface $cart
   *   Cart object.
   *
   * @return string|null
   *   String value for the area or NULL if shipping value not available.
   */
  public function getCartShippingAreaParentValue(CartInterface $cart) {
    if (!$cart->getShippingMethodAsString()) {
      return '';
    }

    $shipping = $this->cartHelper->getShipping($cart);
    $address = $this->getAddressArrayFromMagentoAddress($shipping);
    return $this->getAddressShippingAreaParentValue($address, $shipping);
  }

  /**
   * Get Area label from Magento Address.
   *
   * This function takes care of DM V1/V2.
   *
   * @param array $magento_address
   *   Magento address to extract info from.
   * @param string $langcode
   *   Language code in which we want the value to be returned.
   *
   * @return string|null
   *   String value for the area or NULL.
   */
  public function getAddressShippingAreaValue(array $magento_address, $langcode = 'en') {
    if (isset($magento_address['extension']) && is_object($magento_address['extension'])) {
      $magento_address['extension'] = (array) $magento_address['extension'];
    }

    $field = 'address_area_segment';

    if ($this->getDmVersion() == AlshayaAddressBookManagerInterface::DM_VERSION_2) {
      $mappings = $this->getMagentoFieldMappings();
      $field = $mappings['administrative_area'];
    }

    $value = isset($magento_address['extension'], $magento_address['extension'][$field])
      ? $magento_address['extension'][$field]
      : '';

    return $this->areasTermsHelper->getShippingAreaLabel($value, $langcode);
  }

  /**
   * Get City/Governate label from Magento Address.
   *
   * This function takes care of DM V1/V2.
   *
   * @param array $address
   *   Drupal address to extract info from.
   * @param array $magento_address
   *   Magento address to extract info from.
   * @param string $langcode
   *   Language code in which we want the value to be returned.
   *
   * @return string|null
   *   String value for the area or NULL.
   */
  public function getAddressShippingAreaParentValue(array $address, array $magento_address, $langcode = 'en') {
    if ($this->getDmVersion() != AlshayaAddressBookManagerInterface::DM_VERSION_2) {
      return $address['locality'];
    }

    $mappings = $this->getMagentoFieldMappings();
    $field = $mappings['area_parent'];

    $value = isset($magento_address['extension'], $magento_address['extension'][$field])
      ? $magento_address['extension'][$field]
      : '';

    return $this->areasTermsHelper->getShippingAreaLabel($value, $langcode);
  }

  /**
   * Wrapper function to get current DM Version from config.
   *
   * @return mixed
   *   Current DM Version.
   */
  public function getDmVersion() {
    static $dm_version;

    if (!isset($dm_version)) {
      $dm_version = $this->configFactory->get('alshaya_addressbook.settings')->get('dm_version');
    }

    return $dm_version;
  }

  /**
   * Add available prefix for each components of given address.
   *
   * @param array $address
   *   Address array.
   */
  public function decorateAddressDispaly(array $address) {
    $address = array_filter($address);

    if (!isset($address['country'])) {
      $country_list = \Drupal::service('address.country_repository')->getList();
      $address['country'] = $country_list[$address['country_code']];
    }

    $prefix_settings = $this->configFactory->get('alshaya_addressbook.settings')->get('prefix_settings');
    $mapping = $this->getMagentoFieldMappings();
    $form_fields = $this->getMagentoFormFields();

    // Rearrange mapping array according to $form_fields.
    $mapping = array_merge(array_flip(array_keys($form_fields)), array_flip($mapping));
    // Remove any additional keys, which are associated as index id.
    $mapping = array_filter($mapping, function ($value) use ($address) {
      return (!is_numeric($value) && in_array($value, array_keys($address))) || strpos($value, 'country') !== FALSE;
    });

    // Reverse the string for rtl language.
    $language = $this->languageManager->getCurrentLanguage();
    $component_string = $language->getDirection() == LanguageInterface::DIRECTION_RTL ? ":value :prefix" : ":prefix :value";

    $prefix_field = $prefix_settings['key'];
    $remove_keys = [];
    foreach ($address as $key => &$value) {
      // Check if the _display keys are present with values.
      if (strpos($key, '_display') !== TRUE && isset($address[$key . '_display'])) {
        $value = $address[$key . '_display'];
        $remove_keys[] = $key . '_display';
      }

      if (!isset($mapping[$key]) || in_array($key, $prefix_settings['except'])) {
        continue;
      }

      $magento_key = $mapping[$key];
      if (!empty($value) && !empty($form_fields[$magento_key][$prefix_field])) {
        $value = strtr($component_string, [
          ':prefix' => $form_fields[$magento_key][$prefix_field],
          ':value' => ($key == 'mobile_number' && isset($value['value'])) ? $value['value'] : $value,
        ]);
      }
    }

    // Remove keys from array.
    $address = array_diff_key($address, array_flip($remove_keys));
    // Rearrange address fields based on mapping array.
    $address = array_merge(array_flip($mapping), $address);
    return $address;
  }

}
