<?php

namespace Drupal\alshaya_click_collect\Form;

use Drupal\alshaya_stores_finder_transac\StoresFinderUtility;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\geolocation\MapProviderManager;

/**
 * Provides a configuration form for configurable actions.
 */
class ClickCollectAvailableStores extends FormBase {

  /**
   * The action plugin manager.
   *
   * @var \Drupal\alshaya_stores_finder_transac\StoresFinderUtility
   */
  protected $storeFinder;

  /**
   * The map provider.
   *
   * @var \Drupal\geolocation\MapProviderManager
   */
  protected $mapProvider;

  /**
   * Constructs a new ActionAdminManageForm.
   *
   * @param \Drupal\alshaya_stores_finder_transac\StoresFinderUtility $storeFinder
   *   The action plugin manager.
   * @param \Drupal\geolocation\MapProviderManager $mapProvider
   *   The map provider.
   */
  public function __construct(StoresFinderUtility $storeFinder, MapProviderManager $mapProvider) {
    $this->storeFinder = $storeFinder;
    $this->mapProvider = $mapProvider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_stores_finder_transac.utility'),
      $container->get('plugin.manager.geolocation.mapprovider')
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
    // We don't add token for this form, it will never be user specific.
    $form['#token'] = FALSE;

    // We set the action to empty string, it will always use AJAX anyways.
    $form['#action'] = '';

    // Hidden latitude field.
    $form['latitude'] = [
      '#type' => 'hidden',
    ];

    // Hidden longitude field.
    $form['longitude'] = [
      '#type' => 'hidden',
    ];

    // Location field to search store.
    $form['location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Check in-store availability'),
      '#title_display' => 'before',
      '#placeholder' => $this->t('Enter your area'),
      '#prefix' => '<span class="label">' . $this->t('Check in-store availability') . '</span>',
      '#attributes' => ['class' => ['store-location-input']],
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

    $site_country = _alshaya_custom_get_site_level_country_code();
    $form['#attached'] = [
      'library' => ['alshaya_click_collect/click-and-collect.pdp'],
      'drupalSettings' => [
        'geolocation' => [
          'google_map_url' => $this->mapProvider->getMapProvider('google_maps')->getGoogleMapsApiUrl(),
          'geocoder' => [
            'google_geocoding_api' => [
              'componentRestrictions' => ['country' => $site_country],
            ],
          ],
        ],
        'alshaya_acm' => ['storeFinder' => TRUE],
        'alshaya_click_collect' => ['searchForm' => TRUE],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('action')) {
      $form_state->setRedirect($this->getRouteMatch()->getRouteName(), $this->getRouteMatch()->getRawParameters()->all());
    }
  }

}
