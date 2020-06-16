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
    $steps = [
      0 => [
        'step' => 1,
        'stepTitle' => $this->t('appointment type'),
      ],
      1 => [
        'step' => 2,
        'stepTitle' => $this->t('select store'),
      ],
      2 => [
        'step' => 3,
        'stepTitle' => $this->t('select time slot'),
      ],
      3 => [
        'step' => 4,
        'stepTitle' => $this->t('login / guest'),
      ],
      4 => [
        'step' => 5,
        'stepTitle' => $this->t('customer details'),
      ],
      5 => [
        'step' => 6,
        'stepTitle' => $this->t('confirmation'),
      ],
    ];
    return [
      '#type' => 'markup',
      '#markup' => '<div id="appointment-booking"></div>',
      '#attached' => [
        'library' => [
          'alshaya_appointment/alshaya_appointment',
        ],
        'drupalSettings' => [
          'step_labels' => $steps,
        ],
      ],
    ];
  }

}
