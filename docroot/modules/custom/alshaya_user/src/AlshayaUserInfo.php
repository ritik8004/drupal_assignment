<?php

namespace Drupal\alshaya_user;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Entity\EntityRepository;

/**
 * Class AlshayaUserInfo.
 *
 * @package Drupal\alshaya_user
 */
class AlshayaUserInfo {

  /**
   * The current user service object.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  public $currentUser;

  /**
   * The translated current user object.
   *
   * @var \Drupal\user\Entity\User
   */
  public $userObject;

  /**
   * AlshayaUserInfo constructor.
   *
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current account object.
   * @param \Drupal\Core\Entity\EntityRepository $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service object.
   */
  public function __construct(AccountProxy $current_user,
                              EntityRepository $entity_repository,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $current_user;
    $account = $entity_type_manager->getStorage('user')->load($this->currentUser->id());
    $this->userObject = $entity_repository->getTranslationFromContext($account);
  }

  /**
   * Get first name of user.
   *
   * @return string
   *   The First name of the user.
   */
  public function getFirstName() {
    return $this->userObject->get('field_first_name')->getString();
  }

  /**
   * Get last name of user.
   *
   * @return string
   *   The Last name of the user.
   */
  public function getLastName() {
    return $this->userObject->get('field_last_name')->getString();
  }

  /**
   * Get full name of user.
   *
   * @param string $glue
   *   The glue to use to attach first and last name.
   *
   * @return string
   *   Combined first and last name with provided glue.
   */
  public function getName($glue = ' ') {
    if (!empty($this->getFirstName())) {
      return implode($glue, [$this->getFirstName(), $this->getLastName()]);
    }
    return '';
  }

  /**
   * Get the route names related to user account.
   *
   * @return array
   *   The array of route names for user account related information.
   */
  public function getMyAccountRoutes() {
    return [
      'entity.user.canonical',
      'acq_customer.orders',
      'entity.user.edit_form',
      'entity.profile.type.user_profile_form',
      'alshaya_user.user_communication_preference',
      'change_pwd_page.change_password_form',
      'acq_checkoutcom.payment_cards',
    ];
  }

}
