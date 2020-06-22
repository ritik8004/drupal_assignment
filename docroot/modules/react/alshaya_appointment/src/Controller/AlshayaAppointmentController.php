<?php

namespace Drupal\alshaya_appointment\Controller;

use Drupal\Core\Controller\ControllerBase;

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
    $config = $this->config('alshaya_appointment.settings');

    $settings['alshaya_appointment'] = [
      'middleware_url' => _alshaya_appointment_get_middleware_url(),
      'step_labels' => $this->getAppointmentSteps(),
      'appointment_terms_conditions_text' => $config->get('appointment_terms_conditions_text'),
      'appointment_companion_limit' => $config->get('appointment_companion_limit'),
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
