<?php

namespace Drupal\alshaya_appointment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;

/**
 * Class AlshayaAppointmentController.
 *
 * @package Drupal\alshaya_appointments\Controller
 */
class AlshayaAppointmentController extends ControllerBase {

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

    $cache_tags = Cache::mergeTags($cache_tags, array_merge(
      $alshaya_appointment_config->getCacheTags(),
      $store_finder_config->getCacheTags(),
      $geolocation_config->getCacheTags()
    ));

    $settings['alshaya_appointment'] = [
      'middleware_url' => _alshaya_appointment_get_middleware_url(),
      'step_labels' => $this->getAppointmentSteps(),
      'appointment_terms_conditions_text' => $alshaya_appointment_config->get('appointment_terms_conditions_text'),
      'appointment_companion_limit' => $alshaya_appointment_config->get('appointment_companion_limit'),
      'local_storage_expire' => $alshaya_appointment_config->get('local_storage_expire'),
      'store_finder' => array_merge($alshaya_appointment_config->get('store_finder'), $store_finder_config->get('country_center')),
      'google_map_api_key' => $geolocation_config->get('google_map_api_key'),
    ];

    return [
      '#type' => 'markup',
      '#markup' => '<div id="appointment-booking"></div>',
      '#attached' => [
        'library' => [
          'alshaya_appointment/alshaya_appointment',
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
      ],
      [
        'step' => 2,
        'stepTitle' => $this->t('select store'),
      ],
      [
        'step' => 3,
        'stepTitle' => $this->t('select time slot'),
      ],
      [
        'step' => 4,
        'stepTitle' => $this->t('login / guest'),
      ],
      [
        'step' => 5,
        'stepTitle' => $this->t('customer details'),
      ],
      [
        'step' => 6,
        'stepTitle' => $this->t('confirmation'),
      ],
    ];

    return $steps;
  }

}
