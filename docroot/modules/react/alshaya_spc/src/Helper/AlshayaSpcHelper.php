<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\alshaya_addressbook\AddressBookAreasTermsHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class AlshayaSpcHelper.
 */
class AlshayaSpcHelper {

  /**
   * Address book manager.
   *
   * @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager
   */
  protected $addressBookManager;

  /**
   * Address terms helper.
   *
   * @var \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper
   */
  protected $areaTermsHelper;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AlshayaSpcHelper constructor.
   *
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager
   *   Address book manager.
   * @param \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper $terms_helper
   *   Address terms helper.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(AlshayaAddressBookManager $address_book_manager,
                              AddressBookAreasTermsHelper $terms_helper,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->addressBookManager = $address_book_manager;
    $this->areaTermsHelper = $terms_helper;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get the address form fields with the render order.
   *
   * @return array
   *   Address form fields.
   */
  public function getAddressFields() {
    $magento_form_fields = $this->addressBookManager->getMagentoFormFields();
    $field_mapping = array_flip($this->addressBookManager->getMagentoFieldMappings());
    $fields = [];
    $all_fields = [
      'address_line1',
      'address_line2',
      'postal_code',
      'sorting_code',
      'locality',
      'dependent_locality',
      'administrative_area',
      'area_parent',
    ];

    foreach ($magento_form_fields as $field) {
      if (!isset($field_mapping[$field['attribute_code']]) || !in_array($field_mapping[$field['attribute_code']], $all_fields)) {
        continue;
      }

      $fields[$field_mapping[$field['attribute_code']]] = [
        'key' => $field['attribute_code'],
        'label' => $field['store_label'],
        'required' => $field['required'],
      ];
    }

    return $fields;
  }

  /**
   * Get area terms.
   *
   * @return array
   *   Area terms.
   */
  public function getAreaList() {
    $area_list = [];
    $field_list = $this->getAddressFields();
    // If `area_parent` is not available in the fields.
    if (!isset($field_list['area_parent'])) {
      $area_list = $this->areaTermsHelper->getAllAreas(TRUE);
    }

    return $area_list;
  }

  /**
   * Get all area terms of a given parent.
   *
   * @param int $parent_id
   *   Parent term id.
   *
   * @return array
   *   Area term array.
   */
  public function getAllAreasOfParent(int $parent_id) {
    return $this->areaTermsHelper->getAllAreasWithParent($parent_id, TRUE);
  }

  /**
   * Get parent area terms.
   *
   * @return array
   *   Parent area terms.
   */
  public function getAreaParentList() {
    $list = [];
    $field_list = $this->getAddressFields();
    // If `area_parent` is available in the fields.
    if (isset($field_list['area_parent'])) {
      return $this->areaTermsHelper->getAllGovernates(TRUE);
    }

    return $list;
  }

  /**
   * Get list of address for a user.
   *
   * @param int $uid
   *   User id.
   *
   * @return array
   *   Address list.
   */
  public function getAddressListByUid(int $uid) {
    $user = $this->entityTypeManager->getStorage('user')->load($uid);
    $profiles = $this->entityTypeManager->getStorage('profile')
      ->loadMultipleByUser($user, 'address_book');

    $addressList = [];
    foreach ($profiles as $profile) {
      $addressList[$profile->id()] = array_filter($profile->get('field_address')->first()->getValue());
      $addressList[$profile->id()]['mobile'] = $profile->get('field_mobile_number')->first()->getValue();
      $addressList[$profile->id()]['is_default'] = $profile->isDefault();
      $addressList[$profile->id()]['address_id'] = $profile->id();
      // We get the area as term id but we need the location id
      // of that term.
      if ($addressList[$profile->id()]['administrative_area']) {
        $term = $this->entityTypeManager->getStorage('taxonomy_term')
          ->load($addressList[$profile->id()]['administrative_area']);
        if ($term) {
          $addressList[$profile->id()]['administrative_area'] = $term->get('field_location_id')->first()->getValue()['value'];
        }
      }
    }

    return $addressList;
  }

  /**
   * Sets give address as default address for the user.
   *
   * @param int $profile
   *   Address id.
   * @param int $uid
   *   User id.
   *
   * @return bool
   *   True if profile is saved as default.
   */
  public function setDefaultAddress(int $profile, int $uid) {
    $profile = $this->entityTypeManager->getStorage('profile')->load($profile);
    // If profile is valid and belongs to the user.
    if ($profile && $profile->getOwnerId() == $uid) {
      $profile->setDefault(TRUE);
      $profile->save();
      return TRUE;
    }

    return FALSE;
  }

}
