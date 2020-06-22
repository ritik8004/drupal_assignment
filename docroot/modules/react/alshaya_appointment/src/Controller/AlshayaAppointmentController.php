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
    $settings['alshaya_appointment'] = [
      'middleware_url' => _alshaya_appointment_get_middleware_url(),
      'step_labels' => $this->getAppointmentSteps(),
      'appointmentTermsConditionsText' => $this->getAppointmentTermsConditionsText(),
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

  /**
   * Get appointment acknowledgement text.
   *
   * @return string
   *   Acknowledgement text.
   */
  private function getAppointmentTermsConditionsText() {
    // @Todo: Discuss to make this configurable.
    $appointmentTermsConditionsText = "There are many variations of passages of Lorem Ipsum available, but the majority have   suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.";

    return $appointmentTermsConditionsText;
  }

}
