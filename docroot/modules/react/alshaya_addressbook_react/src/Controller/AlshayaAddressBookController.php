<?php

namespace Drupal\alshaya_addressbook_react\Controller;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\profile\Controller\UserController;
use Drupal\user\UserInterface;
use Drupal\profile\Entity\ProfileTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alshaya Address Book Controller class.
 */
class AlshayaAddressBookController extends UserController {

  /**
   * Mobile utility.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * AlshayaAddressBookController constructor.
   *
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   */
  public function __construct(MobileNumberUtilInterface $mobile_util) {
    $this->mobileUtil = $mobile_util;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mobile_number.util')
    );
  }

  /**
   * Provides profile create form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param \Drupal\profile\Entity\ProfileTypeInterface $profile_type
   *   The profile type entity for the profile.
   *
   * @return array
   *   Returns form array.
   */
  public function userProfileForm(RouteMatchInterface $route_match, UserInterface $user, ProfileTypeInterface $profile_type) {
    // Get country code.
    $country_code = _alshaya_custom_get_site_level_country_code();

    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-user-address-page" class="block-system"></div>',
      '#attached' => [
        'library' => [
          'alshaya_addressbook_react/alshaya_addressbook_react_user_profile',
          'alshaya_addressbook_react/alshaya_addressbook_react_util',
        ],
        'drupalSettings' => [
          'addressbook' => [
            'country_mobile_code' => $this->mobileUtil->getCountryCode($country_code),
            'mobile_max_limit' => $this->config('alshaya_master.mobile_number_settings')->get('maxlength'),
          ],
        ],
      ],
    ];
  }

}
