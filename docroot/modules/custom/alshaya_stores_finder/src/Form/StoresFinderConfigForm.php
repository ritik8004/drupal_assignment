<?php

namespace Drupal\alshaya_stores_finder\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Stores Finder Config Form.
 */
class StoresFinderConfigForm extends ConfigFormBase {

  /**
   * File system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ProductReportController constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The filesystem service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(FileSystemInterface $file_system, ConfigFactoryInterface $config_factory) {
    $this->fileSystem = $file_system;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_stores_finder_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_stores_finder.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_stores_finder.settings');

    $form['filter_path'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Path to MDC stores API'),
      '#default_value' => $config->get('filter_path') ?: '',
      '#description' => $this->t('Set the value using drush command: <b>drush cset alshaya_stores_finder.settings filter_path test/url</b>'),
      '#disabled' => TRUE,
    ];

    $form['stores_finder_page_status'] = [
      '#type' => 'select',
      '#title' => $this->t('Allow user to view and find stores in the stores finder page'),
      '#required' => TRUE,
      '#options' => [
        0 => $this->t('No'),
        1 => $this->t('Yes'),
      ],
      '#default_value' => (int) $config->get('stores_finder_page_status') ?? 1,
      '#weight' => 0,
    ];

    $form['enable_disable_store_finder_search'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable or disable store finder search on site'),
      '#required' => TRUE,
      '#default_value' => $config->get('enable_disable_store_finder_search'),
      '#options' => [0 => $this->t('Disable'), 1 => $this->t('Enable')],
    ];

    $form['load_more_item_limit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Load more item count'),
      '#description' => $this->t('Number of stores after which load more button should be displayed. Click on load more will pull down these number of stores.'),
      '#default_value' => $config->get('load_more_item_limit'),
    ];

    $form['store_list_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label for back to store list link'),
      '#description' => $this->t('Configure the label for the link that will redirect back to all the sores list.'),
      '#default_value' => $config->get('store_list_label'),
    ];

    $form['store_search_placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder for store search'),
      '#description' => $this->t('Configure the placeholder for the store search textfield.'),
      '#default_value' => $config->get('store_search_placeholder'),
    ];

    $form['search_proximity_radius'] = [
      '#type' => 'number',
      '#min' => 1,
      '#step' => 1,
      '#title' => $this->t('Store finder proximity radius'),
      '#description' => $this->t('Proximity radius for store search. This will be in KM.'),
      '#default_value' => $config->get('search_proximity_radius'),
    ];

    $form['marker'] = [
      '#type' => 'details',
      '#title' => $this->t('Marker settings'),
      '#open' => TRUE,
    ];

    $form['marker']['use_default'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use the default marker icon supplied by the module.'),
      '#default_value' => $config->get('marker.use_default'),
      '#tree' => FALSE,
    ];

    $form['marker']['settings'] = [
      '#type' => 'container',
      '#states' => [
        // Hide the marker settings when using the default marker.
        'invisible' => [
          'input[name="use_default"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['marker']['settings']['marker_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to custom marker icon'),
      '#default_value' => $config->get('marker.use_default') ? '' : $config->get('marker.path'),
    ];

    $form['marker']['settings']['marker_upload'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload marker icon'),
      '#maxlength' => 40,
      '#description' => $this->t("If you don't have direct file access to the server, use this field to upload your marker."),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Handle marker image file upload and validation.
    $validators = ['file_validate_extensions' => ['png gif jpg jpeg apng svg']];

    // Check for a new uploaded file.
    $file = file_save_upload('marker_upload', $validators, FALSE, 0);
    if (isset($file)) {
      // File upload was attempted.
      if ($file) {
        // Put the temporary file in form_values so we can save it on submit.
        $form_state->setValue('marker_upload', $file);
      }
      else {
        // File upload failed.
        $form_state->setErrorByName('marker_upload', $this->t('The marker icon could not be uploaded.'));
      }
    }
    elseif (!$form_state->getValue('use_default') && empty($form_state->getValue('marker_path'))) {
      // No files uploaded and marker path is empty.
      $form_state->setErrorByName('marker_upload', $this->t('Please upload marker icon.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // When intending to use the default image, unset the marker_path.
    if ($form_state->getValue('use_default')) {
      $form_state->unsetValue('marker_path');
      $marker_path = drupal_get_path('module', 'alshaya_stores_finder') . '/images/google-map-marker.svg';
    }
    else {
      $marker_upload = $form_state->getValue('marker_upload');
      if (!empty($marker_upload)) {
        $source = $marker_upload->getFileUri();
        $destination = file_build_uri($this->fileSystem->basename($source));
        $filename = $this->fileSystem->copy($source, $destination);
        $marker_path = $filename;
      }
      else {
        $marker_path = $form_state->getValue('marker_path');
      }

      if (!empty($marker_path) && empty($marker_upload)) {
        $marker_path = alshaya_master_validate_path($marker_path);
      }
    }

    $config = $this->config('alshaya_stores_finder.settings');
    $config->set('filter_path', $form_state->getValue('filter_path'));
    $config->set('stores_finder_page_status', (int) $form_state->getValue('stores_finder_page_status'));
    $config->set('enable_disable_store_finder_search', $form_state->getValue('enable_disable_store_finder_search'));
    $config->set('load_more_item_limit', $form_state->getValue('load_more_item_limit'));
    $config->set('search_proximity_radius', $form_state->getValue('search_proximity_radius'));
    $config->set('store_list_label', $form_state->getValue('store_list_label'));
    $config->set('store_search_placeholder', $form_state->getValue('store_search_placeholder'));
    $config->set('marker.use_default', $form_state->getValue('use_default'));
    $config->set('marker.path', $marker_path);
    $config->set('marker.url', file_url_transform_relative(file_create_url($marker_path)));
    $config->save();

    // Invalidate the cache tag.
    $tags = ['store-finder-cache-tag'];
    Cache::invalidateTags($tags);

    parent::submitForm($form, $form_state);
  }

}
