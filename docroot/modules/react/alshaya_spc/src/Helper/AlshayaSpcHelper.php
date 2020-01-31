<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class AlshayaSpcHelper.
 */
class AlshayaSpcHelper {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AlshayaSpcHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
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
