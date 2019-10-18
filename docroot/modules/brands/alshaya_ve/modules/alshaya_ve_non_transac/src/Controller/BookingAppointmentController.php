<?php

namespace Drupal\alshaya_ve_non_transac\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Controller for Booking An Appointment.
 */
class BookingAppointmentController extends ControllerBase {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The BookingAppointmentController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The form builder.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager) {
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * Callback for opening the book an appointment modal window.
   */
  public function openBookAppointmentModal() {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $config = $this->config('alshaya_ve_non_transac.settings');
    $bookAppointmentUrl = $config->get('book_appointment_url');
    $bookAppointmentUrl = $bookAppointmentUrl . "&lang=" . $langcode;
    return [
      '#theme' => 'book_appointment',
      '#iframe_url' => $bookAppointmentUrl,
    ];
  }

}
