<?php

namespace Drupal\alshaya_appointment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class Alshaya Appointment Controller.
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
   * Request Service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaAppointmentController constructor.
   *
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   Current user.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(MobileNumberUtilInterface $mobile_util,
                              AccountProxy $current_user,
                              RequestStack $request_stack,
                              LanguageManagerInterface $language_manager,
                              ModuleHandlerInterface $module_handler) {
    $this->mobileUtil = $mobile_util;
    $this->currentUser = $current_user;
    $this->request = $request_stack->getCurrentRequest();
    $this->languageManager = $language_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mobile_number.util'),
      $container->get('current_user'),
      $container->get('request_stack'),
      $container->get('language_manager'),
      $container->get('module_handler')
    );
  }

  /**
   * Appointment multi step form page.
   *
   * @return array
   *   Return array of markup with react lib attached.
   */
  public function appointment() {

    // If query parameter has appointment ID then check user is logged in.
    $appointment = $this->request->get('appointment');
    if ($appointment) {
      if (!$this->currentUser()->isAuthenticated()) {
        throw new AccessDeniedHttpException('Please login to edit appointments.');
      }
    }

    $cache_tags = [];

    $alshaya_appointment_config = $this->config('alshaya_appointment.settings');
    $store_finder_config = $this->config('alshaya_stores_finder.settings');
    $geolocation_config = $this->config('geolocation_google_maps.settings');
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
    // Get Lang code.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $settings = [
      'map' => [
        'google_api_key' => $geolocation_config->get('google_map_api_key'),
      ],
      'alshaya_appointment' => [
        'logo' => alshaya_master_get_email_logo(NULL, $langcode),
        'middleware_url' => _alshaya_appointment_get_middleware_url(),
        'step_labels' => $this->getAppointmentSteps(),
        'appointment_companion_limit' => $alshaya_appointment_config->get('appointment_companion_limit'),
        // Convert the expiry time into the seconds.
        'local_storage_expire' => ($alshaya_appointment_config->get('local_storage_expire') / 1000),
        'country_code' => $country_code,
        'store_finder' => array_merge(
          $alshaya_appointment_config->get('store_finder'),
          [
            'map_marker' => $store_finder_config->get('map_marker'),
          ]
        ),
        'country_mobile_code' => $this->mobileUtil->getCountryCode($country_code),
        'mobile_maxlength' => $alshaya_master_config->get('maxlength'),
        'customer_details_disclaimer_text' => $alshaya_appointment_config->get('customer_details_disclaimer_text.value'),
        'user_details' => $this->getUserDetails(),
        'socialLoginEnabled' => $social_login_enabled->get('social_login'),
      ],
    ];

    $this->moduleHandler->loadInclude('alshaya_appointment', 'inc', 'alshaya_appointment.static_strings');

    return [
      '#theme' => 'appointment_booking',
      '#strings' => _alshaya_appointment_static_strings(),
      '#attached' => [
        'library' => [
          'alshaya_appointment/alshaya_appointment_localstore',
          'alshaya_appointment/alshaya_appointment',
          'alshaya_white_label/appointment-booking',
          'alshaya_spc/googlemapapi',
          'alshaya_appointment/alshaya_appointment_socialauth',
          'alshaya_appointment/gtm_appointment_booking',
        ],
        'drupalSettings' => $settings,
      ],
      '#cache' => [
        'tags' => $cache_tags,
        'contexts' => ['user'],
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
        'stepTitle' => $this->t('Appointment type'),
        'stepValue' => 'appointment-type',
      ],
      [
        'step' => 2,
        'stepTitle' => $this->t('Select store'),
        'stepValue' => 'select-store',
      ],
      [
        'step' => 3,
        'stepTitle' => $this->t('Select time slot'),
        'stepValue' => 'select-time-slot',
      ],
      [
        'step' => 4,
        'stepTitle' => $this->t('Login / Guest'),
        'stepValue' => 'select-login-guest',
      ],
      [
        'step' => 5,
        'stepTitle' => $this->t('Customer details'),
        'stepValue' => 'customer-details',
      ],
      [
        'step' => 6,
        'stepTitle' => $this->t('Confirmation'),
        'stepValue' => 'confirmation',
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
    $settings = [];
    $cache_tags = [];

    $settings['alshaya_appointment'] = [
      'middleware_url' => _alshaya_appointment_get_middleware_url(),
      'user_details' => $this->getUserDetails(),
    ];

    $this->moduleHandler->loadInclude('alshaya_appointment', 'inc', 'alshaya_appointment.static_strings');

    return [
      '#theme' => 'customer_appointments',
      '#strings' => _alshaya_appointment_my_appointments_static_strings(),
      '#attached' => [
        'library' => [
          'alshaya_appointment/alshaya_appointment_view',
          'alshaya_white_label/appointment-booking',
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
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getUserInfo(Request $request) {
    $response = [
      'email' => '',
      'uid' => 0,
    ];

    if ($this->currentUser()->isAuthenticated()) {
      $response['email'] = $this->currentUser()->getEmail();

      // Drupal CORE uses numeric 0 for anonymous but string for logged in.
      // We follow the same.
      $response['uid'] = $this->currentUser()->id();

    }

    return new JsonResponse($response);
  }

}
