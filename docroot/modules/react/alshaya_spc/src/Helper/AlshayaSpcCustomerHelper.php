<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class AlshayaSpcCustomerHelper.
 */
class AlshayaSpcCustomerHelper {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AlshayaSpcCustomerHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get list of addresses for the customer.
   *
   * @param int $uid
   *   User id.
   *
   * @return array
   *   Address list.
   */
  public function getCustomerAllAddresses(int $uid) {
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
   * Changes the default address of the customer.
   *
   * @param int $profile
   *   Profile id.
   * @param int $uid
   *   User id.
   *
   * @return bool
   *   True if profile is saved as default.
   */
  public function updateCustomerDefaultAddress(int $profile, int $uid) {
    /* @var \Drupal\profile\Entity\Profile $profile */
    $profile = $this->entityTypeManager->getStorage('profile')->load($profile);
    // If profile is valid and belongs to the user.
    if ($profile && $profile->getOwnerId() == $uid) {
      $profile->setDefault(TRUE);
      $profile->save();
      return TRUE;
    }

    return FALSE;
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
      /* @var \Drupal\profile\Entity\Profile $profile */
      $profile = $this->entityTypeManager->getStorage('profile')->load($profile);
      // If address belongs to the current user.
      if ($profile && $profile->getOwnerId() == $uid) {
        // If user tyring to delete default address.
        if ($profile->isDefault()) {
          $return = FALSE;
        }
        else {
          // Delete the address.
          $profile->delete();
          $return = TRUE;
        }
      }
      else {
        $return = FALSE;
      }
    }
    catch (\Exception $e) {
      $return = FALSE;
    }

    return $return;
  }

}
