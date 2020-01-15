<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\alshaya_addressbook\AddressBookAreasTermsHelper;

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
   * AlshayaSpcHelper constructor.
   *
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager
   *   Address book manager.
   * @param \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper $terms_helper
   *   Address terms helper.
   */
  public function __construct(AlshayaAddressBookManager $address_book_manager,
                              AddressBookAreasTermsHelper $terms_helper) {
    $this->addressBookManager = $address_book_manager;
    $this->areaTermsHelper = $terms_helper;
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

}
