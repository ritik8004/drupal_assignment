<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

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
   * The api wrapper.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcApiHelper
   */
  protected $apiWrapper;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaSpcCustomerHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcApiHelper $api_wrapper
   *   The api wrapper.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AlshayaSpcApiHelper $api_wrapper,
    ModuleHandlerInterface $module_handler
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->apiWrapper = $api_wrapper;
    $this->moduleHandler = $module_handler;
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

  /**
   * Helper function to authenticate user from Magento.
   *
   * @param string $mail
   *   Mail.
   * @param string $pass
   *   Password.
   *
   * @return int|mixed|string|null
   *   User id of user if successful or null.
   *
   * @throws \Exception
   */
  public function authenticateCustomer($mail, $pass) {
    global $_alshaya_acm_custom_cart_association_processed;

    try {
      $customer = $this->apiWrapper->authenticateCustomerOnMagento($mail, $pass);

      if (!empty($customer) && !empty($customer['customer_id'])) {
        $_alshaya_acm_custom_cart_association_processed = TRUE;
        $this->moduleHandler->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.utility');

        // Check if user exists in Drupal.
        if ($user = user_load_by_mail($mail)) {
          // Update the data in Drupal to match the values in Magento.
          alshaya_acm_customer_update_user_data($user, $customer);
        }
        // Create user.
        else {
          /** @var \Drupal\user\Entity\User $user */
          $user = alshaya_acm_customer_create_drupal_user($customer);
        }

        return $user->id();
      }
    }
    catch (\Exception $e) {
      throw $e;
    }

    return NULL;
  }

}
