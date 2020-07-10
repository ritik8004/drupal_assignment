<?php

namespace Drupal\alshaya_appointment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AlshayaAppointmentController.
 *
 * @package Drupal\alshaya_appointments\Controller
 */
class AlshayaAppointmentController extends ControllerBase {
  /**
   * Mobile utility.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * AlshayaAppointmentController constructor.
   *
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   Current user.
   */
  public function __construct(MobileNumberUtilInterface $mobile_util,
                              AccountProxy $current_user) {
    $this->mobileUtil = $mobile_util;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mobile_number.util'),
      $container->get('current_user')
    );
  }

  /**
   * Appointment multi step form page.
   *
   * @return array
   *   Return array of markup with react lib attached.
   */
  public function appointment() {
    $cache_tags = [];

    $alshaya_appointment_config = $this->config('alshaya_appointment.settings');
    $store_finder_config = $this->config('alshaya_stores_finder.settings');
    $geolocation_config = $this->config('geolocation.settings');
    $alshaya_master_config = $this->config('alshaya_master.mobile_number_settings');
    $social_login_enabled = $this->config('alshaya_social.settings');

    $cache_tags = Cache::mergeTags($cache_tags, array_merge(
      $alshaya_appointment_config->getCacheTags(),
      $store_finder_config->getCacheTags(),
      $geolocation_config->getCacheTags(),
      $alshaya_master_config->getCacheTags(),
      $social_login_enabled->getCacheTags()
    ));

    // Get country code.
    $country_code = _alshaya_custom_get_site_level_country_code();

    $settings['alshaya_appointment'] = [
      'middleware_url' => _alshaya_appointment_get_middleware_url(),
      'step_labels' => $this->getAppointmentSteps(),
      'appointment_companion_limit' => $alshaya_appointment_config->get('appointment_companion_limit'),
      'local_storage_expire' => $alshaya_appointment_config->get('local_storage_expire'),
      'store_finder' => array_merge(
        $alshaya_appointment_config->get('store_finder'),
        $store_finder_config->get('country_center'),
        ['radius' => $store_finder_config->get('search_proximity_radius')]
      ),
      'google_map_api_key' => $geolocation_config->get('google_map_api_key'),
      'country_mobile_code' => $this->mobileUtil->getCountryCode($country_code),
      'mobile_maxlength' => $alshaya_master_config->get('maxlength'),
      'customer_details_disclaimer_text' => $alshaya_appointment_config->get('customer_details_disclaimer_text'),
      'user_details' => $this->getUserDetails(),
      'socialLoginEnabled' => $social_login_enabled->get('social_login'),
    ];

    return [
      '#type' => 'markup',
      '#markup' => '<div id="appointment-booking"></div>',
      '#attached' => [
        'library' => [
          'alshaya_appointment/alshaya_appointment',
          'alshaya_white_label/appointment-booking',
          'alshaya_social/alshaya_social_popup',
        ],
        'drupalSettings' => $settings,
      ],
      '#cache' => [
        'tags' => $cache_tags,
      ],
    ];
  }

  /**
   * Get appointment steps.
   *
   * @return array
   *   Array of appointment steps.
   */
  private function getAppointmentSteps() {
    $steps = [
      [
        'step' => 1,
        'stepTitle' => $this->t('appointment type'),
        'stepValue' => 'appointment-type',
      ],
      [
        'step' => 2,
        'stepTitle' => $this->t('select store'),
        'stepValue' => 'select-store',
      ],
      [
        'step' => 3,
        'stepTitle' => $this->t('select time slot'),
        'stepValue' => 'select-time-slot',
      ],
      [
        'step' => 4,
        'stepTitle' => $this->t('login / guest'),
        'stepValue' => 'login-guest',
      ],
      [
        'step' => 5,
        'stepTitle' => $this->t('customer details'),
        'stepValue' => 'customer-details',
      ],
      [
        'step' => 6,
        'stepTitle' => $this->t('confirmation'),
        'stepValue' => 'appointment-confirmation',
      ],
    ];

    return $steps;
  }

  /**
   * Get user details.
   *
   * @return array
   *   Array of user details.
   */
  private function getUserDetails() {
    $userDetails = [];
    $uid = $this->currentUser()->id();

    if (!$this->currentUser()->isAuthenticated()) {
      $userDetails = ['id' => $uid];
      return $userDetails;
    }

    $user = $this->entityTypeManager()->getStorage('user')->load($uid);
    $user_mobile_number = $user->get('field_mobile_number')->first();
    $userDetails = [
      'id' => $uid,
      'email' => $this->currentUser()->getEmail(),
      'fname' => $user->get('field_first_name')->getString(),
      'lname' => $user->get('field_last_name')->getString(),
      'mobile' => !empty($user_mobile_number) ? $user_mobile_number->getValue()['local_number'] : '',
    ];

    return $userDetails;
  }

  /**
   * View appointment list for logged in user.
   *
   * @return array
   *   Return array of markup with react lib attached.
   */
  public function viewAppointments() {
    $cache_tags = [];

    $settings['alshaya_appointment'] = [
      'middleware_url' => _alshaya_appointment_get_middleware_url(),
      'user_details' => $this->getUserDetails(),
    ];

    return [
      '#type' => 'markup',
      '#markup' => '<div id="customer-appointments"></div>',
      '#attached' => [
        'library' => [
          'alshaya_appointment/alshaya_appointment_view',
        ],
        'drupalSettings' => $settings,
      ],
      '#cache' => [
        'tags' => $cache_tags,
      ],
    ];
  }

  /**
   * Get user customer id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getUserInfo() {
    $response = [
      'email' => '',
    ];

    if ($this->currentUser()->isAuthenticated()) {
      $response['email'] = $this->currentUser()->getEmail();
    }

    return new JsonResponse($response);
  }

}
