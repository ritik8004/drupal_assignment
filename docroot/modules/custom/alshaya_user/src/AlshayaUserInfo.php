<?php

namespace Drupal\alshaya_user;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Entity\EntityRepository;

/**
 * Class Alshaya User Info.
 *
 * @package Drupal\alshaya_user
 */
class AlshayaUserInfo {

  public const INVISIBLE_CHARACTER = '&#8203;';

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
    return self::getUserNameField($this->userObject, 'field_first_name');
  }

  /**
   * Get last name of user.
   *
   * @return string
   *   The Last name of the user.
   */
  public function getLastName() {
    return self::getUserNameField($this->userObject, 'field_last_name');
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
    return self::getFullName($this->userObject, $glue);
  }

  /**
   * Return full name for given user object.
   *
   * @param object $user
   *   The user object.
   * @param string $glue
   *   The glue to use to attach first and last name.
   *
   * @return string
   *   Combined first and last name with provided glue or just empty string.
   */
  public static function getFullName($user, $glue = ' ') {
    $firstName = self::getUserNameField($user, 'field_first_name');
    $lastName = self::getUserNameField($user, 'field_last_name');
    return !empty($firstName) || !empty($lastName) ? implode($glue, array_filter([
      $firstName,
      $lastName,
    ])) : '';
  }

  /**
   * Return the given name field value.
   *
   * @param object $user
   *   The user object.
   * @param string $field
   *   The field name.
   *
   * @return mixed|null
   *   Return null if it contains invisible character or the value as is.
   */
  public static function getUserNameField($user, $field) {
    $fieldValue = $user->get($field)->getString();
    return ($fieldValue == self::INVISIBLE_CHARACTER) ? '' : $fieldValue;
  }

}
