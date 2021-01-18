<?php

namespace Drupal\alshaya_master\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Alshaya 404 Maintenance Settings.
 */
class Alshaya404MaintenanceSettings extends ConfigFormBase {

  /**
   * File Usage service object.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * Language Manager service object.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * File storage manager object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Alshaya404MaintenanceSettings constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   File Usage service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager service object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service object.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              FileUsageInterface $file_usage,
                              LanguageManagerInterface $language_manager,
                              EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory);
    $this->fileUsage = $file_usage;
    $this->languageManager = $language_manager;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('file.usage'),
      $container->get('language_manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_404_maintenance_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_master.maintenanace_404_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_master.maintenanace_404_settings');
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $form['404_container'] = [
      '#type' => 'details',
      '#title' => $this->t('404 Content'),
    ];
    $form['404_container']['404_message'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('404 Text'),
      '#default_value' => $config->get('404_message.value'),
    ];

    $default_404_file = !empty($config->get('404_image.' . $langcode)) ? [$config->get('404_image.' . $langcode)] : [];
    $form['404_container']['404_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload image'),
      '#upload_location' => 'public://404_image/' . $langcode . '/',
      '#default_value' => $default_404_file,
      '#upload_validators'  => [
        'file_validate_extensions' => ['png gif jpg jpeg apng svg'],
      ],
    ];

    $form['maintenance_container'] = [
      '#type' => 'details',
      '#title' => $this->t('Maintenanace mode content'),
    ];
    $form['maintenance_container']['maintenance_mode_rich_message'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Message to display when in maintenance mode'),
      '#default_value' => $config->get('maintenance_mode_rich_message.value'),
    ];

    $default_maintenanace_file = !empty($config->get('maintenance_mode_image')) ? [$config->get('maintenance_mode_image')] : [];
    $form['maintenance_container']['maintenance_mode_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload image'),
      '#upload_location' => 'public://maintenance_mode_image/',
      '#default_value' => $default_maintenanace_file,
      '#upload_validators'  => [
        'file_validate_extensions' => ['png gif jpg jpeg apng svg'],
      ],
    ];

    $form['link_to_maintenance_page'] = [
      '#title' => $this->t('Link to put site in maintenance mode'),
      '#type' => 'link',
      '#url' => Url::fromRoute('system.site_maintenance_mode', [], ['query' => ['destination' => 'admin/config/alshaya/404-maintenance-settings']]),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_master.maintenanace_404_settings');

    $values = $form_state->getValues();
    $fid_404 = '';
    if (!empty($values['404_image'])) {
      $file = $this->fileStorage->load($values['404_image'][0]);
      if ($file) {
        $file->setPermanent();
        $file->save();
        $fid_404 = $file->id();
        // Add file usage or file will be gone in next garbage collection.
        $this->fileUsage->add($file, 'alshaya_master', '404_image', 1);
        // Create image style derivative.
        // Create image style derivative.
        $this->createImageStyle('1284x424', $file->getFileUri());
      }
    }

    $fid_maintenance = '';
    if (!empty($values['maintenance_mode_image'])) {
      $file = $this->fileStorage->load($values['maintenance_mode_image'][0]);
      if ($file) {
        $file->setPermanent();
        $file->save();
        $fid_maintenance = $file->id();
        // Add file usage or file will be gone in next garbage collection.
        $this->fileUsage->add($file, 'alshaya_master', 'system_maintenance', 1);
        // Create image style derivative.
        $this->createImageStyle('1284x424', $file->getFileUri());
      }
    }

    // Get current langcode.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $config->set('404_message', $values['404_message']);
    $config->set('404_image.' . $langcode, $fid_404);
    $config->set('maintenance_mode_rich_message', $values['maintenance_mode_rich_message']);
    $config->set('maintenance_mode_image', $fid_maintenance);
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * Creates the image style.
   *
   * @param string $image_style
   *   Image style name.
   * @param string $uri
   *   Image uri name.
   */
  protected function createImageStyle($image_style, $uri) {
    $style = $this->entityTypeManager->getStorage('image_style')->load($image_style);
    $destination = $style->buildUri($uri);
    $style->createDerivative($uri, $destination);
  }

}
