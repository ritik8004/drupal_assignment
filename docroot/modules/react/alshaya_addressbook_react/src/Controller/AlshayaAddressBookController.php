<?php

namespace Drupal\alshaya_addressbook_react\Controller;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\profile\Controller\UserController;
use Drupal\user\UserInterface;
use Drupal\token\TokenInterface;
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
   * Token service.
   *
   * @var \Drupal\token\TokenInterface
   */
  protected $token;

  /**
   * AlshayaAddressBookController constructor.
   *
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   * @param \Drupal\token\TokenInterface $token
   *   Token service.
   */
  public function __construct(MobileNumberUtilInterface $mobile_util, TokenInterface $token) {
    $this->mobileUtil = $mobile_util;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mobile_number.util'),
      $container->get('token')
    );
  }

  /**
   * Provides profile create form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   *
   * @return array
   *   Returns form array.
   */
  public function userProfileForm(RouteMatchInterface $route_match, UserInterface $user) {

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
            'country_mobile_code' => $this->mobileUtil->getCountryCode(_alshaya_custom_get_site_level_country_code()),
            'country_name' => $this->token->replace('[alshaya_seo:country]'),
            'mobile_max_limit' => $this->config('alshaya_master.mobile_number_settings')->get('maxlength'),
          ],
        ],
      ],
      '#cache' => [
        'tags' => $this->config('alshaya_master.mobile_number_settings')->getCacheTags() ?? [],
      ],
    ];
  }

  /**
   * Page title for address_book page.
   */
  public function addressBookPageTitle() {
    return $this->t('Address book');
  }

}
