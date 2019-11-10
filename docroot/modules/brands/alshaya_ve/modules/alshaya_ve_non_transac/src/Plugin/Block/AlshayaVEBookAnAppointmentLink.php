<?php

namespace Drupal\alshaya_ve_non_transac\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Site\Settings;

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
        'class' => ['use-ajax', 'book-appointment-desktop'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => '{"width":"60%"}',
      ],
    ];

    // Add book an appontment link for mobile devices.
    $bookAppointmentUrl = ($this->config->get('book_appointment_url')) ?? Settings::get('alshaya_ve_non_transac.settings')['book_appointment_url'];
    $link[] = [
      '#type' => 'link',
      '#title' => $this->t('Book an Appointment'),
      '#url' => Url::fromUri($bookAppointmentUrl . "&lang=" . $this->languageManager->getCurrentLanguage()->getId()),
      '#attributes' => [
        'class' => ['book-appointment-mobile'],
        'target' => '_blank',
      ],
    ];

    return [
      '#theme' => 'item_list',
      '#items' => $link,
      '#attached' => [
        'library' => [
          'core/drupal.dialog.ajax',
        ],
      ],
    ];
  }

}
