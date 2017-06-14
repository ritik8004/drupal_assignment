<?php

namespace Drupal\alshaya_stores_finder\Form;

use Drupal\alshaya_stores_finder\StoresFinderUtility;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\GoogleMapsDisplayTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a configuration form for configurable actions.
 */
class StoreFinderAvailableStores extends FormBase {

  use GoogleMapsDisplayTrait;
  /**
   * The action plugin manager.
   *
   * @var \Drupal\alshaya_stores_finder\StoresFinderUtility
   */
  protected $storeFinder;

  /**
   * Constructs a new ActionAdminManageForm.
   *
   * @param \Drupal\alshaya_stores_finder\StoresFinderUtility $storeFinder
   *   The action plugin manager.
   */
  public function __construct(StoresFinderUtility $storeFinder) {
    $this->storeFinder = $storeFinder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_stores_finder.utility')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_stores_available_stores';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getGoogleMapsSettings([]);
    $canvas_id = Html::getUniqueId('available_stores');

    $form['latitude'] = [
      '#type' => 'hidden',
    ];

    $form['longitude'] = [
      '#type' => 'hidden',
    ];

    // Hidden lat,lng input fields.
    $form['location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Check in-store availability'),
      '#title_display' => 'before',
      '#placeholder' => t('Enter your area'),
    ];

    $form['Search'] = [
      '#type' => 'html_tag',
      '#tag' => 'button',
      '#attributes' => [
        'class' => [
          'search-stores-button',
        ],
        'id' => 'search-stores-button',
        'title' => $this->t('search stores'),
      ],
      '#value' => $this->t('search stores'),
    ];

    $form['#attached'] = [
      'library' => ['alshaya_stores_finder/store_finder_autocomplete'],
      'drupalSettings' => [
        'geolocation' => [
          'google_map_url' => $this->getGoogleMapsApiUrl(),
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('action')) {
      $form_state->setRedirect(
        'action.admin_add',
        ['action_id' => $form_state->getValue('action')]
      );
    }
  }

}
