<?php

namespace Drupal\alshaya_boots_appointment\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class AlshayaBootsAppointmentController.
 *
 * @package Drupal\alshaya_appointments\Controller
 */
class AlshayaBootsAppointmentController extends ControllerBase {

  /**
   * Appointment multi step form page.
   *
   * @return array
   *   Return array of markup with react lib attached.
   */
  public function appointment() {
    return [
      '#type' => 'markup',
      '#markup' => '<div id="appointment-booking"></div>',
      '#attached' => [
        'library' => [
          'alshaya_boots_appointment/alshaya_boots_appointment',
        ],
      ],
    ];
  }

}
