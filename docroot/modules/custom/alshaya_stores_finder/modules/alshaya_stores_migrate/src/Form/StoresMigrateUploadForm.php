<?php

namespace Drupal\alshaya_stores_migrate\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Stores Migrate Upload Form.
 */
class StoresMigrateUploadForm extends FormBase {

  /**
   * Language Manager service object.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * ConfigEntityMigration plugin manager object.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $configEntityMigrationPluginManager;

  /**
   * Migration plugin manager object.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $migrationPluginManager;

  /**
   * Alshaya404MaintenanceSettings constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager service object.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $config_entity_migration_plugin_manager
   *   ConfigEntityMigration plugin manager object.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $migration_plugin_manager
   *   Migration plugin manager object.
   */
  public function __construct(LanguageManagerInterface $language_manager,
                              PluginManagerInterface $config_entity_migration_plugin_manager,
                              PluginManagerInterface $migration_plugin_manager) {
    $this->languageManager = $language_manager;
    $this->configEntityMigrationPluginManager = $config_entity_migration_plugin_manager;
    $this->migrationPluginManager = $migration_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('plugin.manager.config_entity_migration'),
      $container->get('plugin.manager.migration')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_stores_migrate_upload';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Set message for users.
    $this->messenger()->addMessage($this->t('Please import stores in English language first.'), 'warning');

    foreach ($this->languageManager->getLanguages() as $language) {
      $langs[$language->getId()] = $language->getName();
    }

    $form['language'] = [
      '#title' => $this->t('Select Language'),
      '#description' => $this->t('Language for which you want to upload CSV.'),
      '#type' => 'select',
      '#options' => $langs,
    ];
    $form['upload'] = [
      '#title' => $this->t('Stores CSV'),
      '#description' => $this->t('Upload the CSV exported from Google Business Places'),
      '#type' => 'file',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import stores'),
      '#button_type' => 'primary',
    ];
    $form['#theme'] = 'system_config_form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $validators = ['file_validate_extensions' => ['csv']];

    $file = file_save_upload('upload', $validators, FALSE, 0);
    if (isset($file)) {
      if ($file) {
        $form_state->setValue('upload', $file);
      }
      else {
        $form_state->setErrorByName('upload', $this->t('The file could not be uploaded.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $language = $form_state->getValue('language');
    $filepath = $form_state->getValue('upload')->getFileUri();

    $migrate_plus_migration_store_config = $this->configFactory()->getEditable('migrate_plus.migration.store.' . $language);

    // Store the initial migrate configuration.
    $initial_filepath = $migrate_plus_migration_store_config->get('source.path');

    // Configure the migrate source path with the uploaded filepath.
    $migrate_plus_migration_store_config->set('source.path', $filepath);
    $migrate_plus_migration_store_config->save();

    // Get migration yml config data.
    $config_data = $migrate_plus_migration_store_config->getRawData();

    // Initialize the migration.
    $this->configEntityMigrationPluginManager->createInstances([]);

    /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
    $migration = $this->migrationPluginManager->createInstance('store_' . $language, $config_data);
    // Set the nodes for updating.
    $migration->getIdMap()->prepareUpdate();
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();

    // Restore the initial migrate configuration.
    $migrate_plus_migration_store_config->set('source.path', $initial_filepath);
    $migrate_plus_migration_store_config->save();

    $this->messenger()->addMessage($this->t('Stores have been imported.'), 'status');
  }

}
