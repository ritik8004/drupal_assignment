<?php

namespace Drupal\alshaya_appointment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
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
   * AlshayaAppointmentController constructor.
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

    $cache_tags = Cache::mergeTags($cache_tags, array_merge(
      $alshaya_appointment_config->getCacheTags(),
      $store_finder_config->getCacheTags(),
      $geolocation_config->getCacheTags(),
      $alshaya_master_config->getCacheTags()
    ));

    // Get country code.
    $country_code = _alshaya_custom_get_site_level_country_code();

    $settings['alshaya_appointment'] = [
      'middleware_url' => _alshaya_appointment_get_middleware_url(),
      'step_labels' => $this->getAppointmentSteps(),
      'appointment_terms_conditions_text' => $alshaya_appointment_config->get('appointment_terms_conditions_text'),
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
    ];

    return [
      '#type' => 'markup',
      '#markup' => '<div id="appointment-booking"></div>',
      '#attached' => [
        'library' => [
          'alshaya_appointment/alshaya_appointment',
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
   * Verifies the mobile number and email.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function validateInfo(Request $request) {
    $data = $request->getContent();
    if (!empty($data)) {
      $data = json_decode($data, TRUE);
    }

    if (empty($data)) {
      return new JsonResponse(['status' => FALSE]);
    }

    $status = [];

    foreach ($data as $key => $value) {
      $status[$key] = FALSE;

      switch ($key) {
        case 'mobile':
          $country_code = _alshaya_custom_get_site_level_country_code();
          $country_mobile_code = '+' . $this->mobileUtil->getCountryCode($country_code);

          if (strpos($value, $country_mobile_code) === FALSE) {
            $value = $country_mobile_code . $value;
          }

          try {
            if ($this->mobileUtil->testMobileNumber($value)) {
              $status[$key] = TRUE;
            }
          }
          catch (\Exception $e) {
            $status[$key] = FALSE;
          }
          break;

        case 'email':
          $domain = explode('@', $value)[1];
          $dns_records = dns_get_record($domain);
          if (empty($dns_records)) {
            $status[$key] = FALSE;
          }
          else {
            $status[$key] = TRUE;
          }
          break;
      }
    }

    return new JsonResponse(['status' => TRUE] + $status);
  }

}
