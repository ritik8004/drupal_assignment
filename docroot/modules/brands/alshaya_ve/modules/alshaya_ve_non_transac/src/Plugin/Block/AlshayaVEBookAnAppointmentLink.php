<?php

namespace Drupal\alshaya_ve_non_transac\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display 'Book appointment link' elements.
 *
 * @Block(
 *   id = "alshaya_ve_book_appointment_link",
 *   admin_label = @Translation("Book An Appointment Link")
 * )
 */
class AlshayaVEBookAnAppointmentLink extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * User Settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaVEBookAnAppointmentLink constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The form builder.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config_factory->get('alshaya_ve_non_transac.settings');
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $link = [];

    // Add book an appontment link for Desktop.
    $link[] = [
      '#type' => 'link',
      '#title' => $this->t('Book an Appointment'),
      '#url' => Url::fromRoute('alshaya_ve_non_transac.appointment_modal_form'),
      '#attributes' => [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => json_encode(static::getDataDialogOptions()),
        // Add this id so that we can test this form.
        'id' => 'book-appointment-modal-link-id',
      ],
    ];

    // Add book an appontment link for mobile devices.
    $link[] = [
      '#type' => 'link',
      '#title' => $this->t('Book an Appointment'),
      '#url' => Url::fromUri($this->config->get('book_appointment_url') . "&lang=" . $this->languageManager->getCurrentLanguage()->getId()),
      '#attributes' => [
        'class' => ['book-appointment-mobile-link'],
        'id' => 'book-appointment-mobile-link-id',
        'target' => '_blank',
      ],
    ];

    return [
      '#theme' => 'item_list',
      '#items' => $link,
      '#attached' => [
        'library' => [
          'core/drupal.dialog.ajax',
          'alshaya_ve_non_transac/book_appointment',
        ],
      ],
    ];
  }

  /**
   * Helper method so we can have consistent dialog options.
   *
   * @return string[]
   *   An array of jQuery UI elements to pass on to our dialog form.
   */
  protected static function getDataDialogOptions() {
    return [
      'width' => '60%',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['book-an-appointment-link']);
  }

}
