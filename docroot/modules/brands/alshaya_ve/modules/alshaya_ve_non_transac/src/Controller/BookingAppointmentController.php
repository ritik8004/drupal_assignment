<?php

namespace Drupal\alshaya_ve_non_transac\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;

/**
 * Controller for Booking An Appointment.
 */
class BookingAppointmentController extends ControllerBase {

  /**
   * Callback for opening the book an appointment modal window.
   */
  public function bookAppointmentModal() {
    $config = $this->config('alshaya_ve_non_transac.settings');
    $bookAppointmentUrl = ($config->get('book_appointment_url')) ?? Settings::get('alshaya_ve_non_transac.settings')['book_appointment_url'];
    $bookAppointmentUrl = $bookAppointmentUrl . "&lang=" . parent::languageManager()->getCurrentLanguage()->getId();
    return [
      '#type' => 'inline_template',
      '#template' => '<iframe id="bookAppontmentModal" name="bookAppontmentModal" sandbox="allow-modals allow-forms allow-popups allow-scripts allow-same-origin" src="{{bookAppointmentUrl}}" width="100%" height="700px"></iframe>',
      '#context' => [
        'bookAppointmentUrl' => $bookAppointmentUrl,
      ],
    ];
  }

}
