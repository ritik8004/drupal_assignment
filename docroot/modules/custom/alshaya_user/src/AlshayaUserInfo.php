<?php

namespace Drupal\alshaya_user;

use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Entity\EntityRepository;
use Drupal\user\Entity\User;
use Drupal\mobile_number\MobileNumberUtil;
use libphonenumber\PhoneNumberFormat;

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
   * The Entity Repository service object.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   */
  public $entityRepository;

  /**
   * The translated current user object.
   *
   * @var \Drupal\user\Entity\User
   */
  public $userObject;

  /**
   * The Mobile number util service object.
   *
   * @var \Drupal\mobile_number\MobileNumberUtil
   */
  public $mobileUtil;

  /**
   * AlshayaUserInfo constructor.
   *
   * @param \Drupal\Core\Session\AccountProxy $currentUser
   *   The current account object.
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   *   The entity repository service.
   * @param \Drupal\mobile_number\MobileNumberUtil $mobileNumberUtil
   *   The Mobile number util service object.
   */
  public function __construct(AccountProxy $currentUser, EntityRepository $entityRepository, MobileNumberUtil $mobileNumberUtil) {
    $this->currentUser = $currentUser;
    $this->entityRepository = $entityRepository;
    $this->userObject = $this->entityRepository->getTranslationFromContext(User::load($this->currentUser->id()));
    $this->mobileUtil = $mobileNumberUtil;
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
   * Get formatter mobile number.
   *
   * @return mixed|string
   *   Formatted mobile number string or empty if it does not exist.
   */
  public function getFormattedMobileNumber() {
    $mobileNumber = explode(', ', $this->userObject->get('field_mobile_number')->getString())[0];
    if (!empty($mobileNumber)) {
      $mobile_value = $this->mobileUtil->getMobileNumber($mobileNumber);
      $mobile_number = $this->mobileUtil->libUtil()->format($mobile_value, PhoneNumberFormat::INTERNATIONAL);
      return str_replace(' ', ' - ', $mobile_number);
    }
    return '';
  }

}
