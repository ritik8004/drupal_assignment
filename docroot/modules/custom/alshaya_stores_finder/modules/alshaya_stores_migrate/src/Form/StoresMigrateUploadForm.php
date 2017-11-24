<?php

namespace Drupal\alshaya_stores_migrate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;

/**
 * Class StoresMigrateUploadForm.
 */
class StoresMigrateUploadForm extends FormBase {

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
    drupal_set_message('Please import stores in English language first.', 'warning');

    $languages = \Drupal::languageManager()->getLanguages();
    foreach ($languages as $language) {
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
        $form_state->setErrorByName('upload', $this->t('The logo could not be uploaded.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $language = $form_state->getValue('language');
    $filepath = $form_state->getValue('upload')->getFileUri();

    $migrate_plus_migration_store_config = \Drupal::service('config.factory')->getEditable('migrate_plus.migration.store_' . $language);

    // Store the initial migrate configuration.
    $initial_filepath = $migrate_plus_migration_store_config->get('source.path');

    // Configure the migrate source path with the uploaded filepath.
    $migrate_plus_migration_store_config->set('source.path', $filepath);
    $migrate_plus_migration_store_config->save();

    /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
    $migration = \Drupal::service('plugin.manager.migration')->createInstance('store_' . $language, ['source' => ['path' => $filepath]]);
    // Set the nodes for updating.
    $migration->getIdMap()->prepareUpdate();
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();

    // Restore the initial migrate configuration.
    $migrate_plus_migration_store_config->set('source.path', $initial_filepath);
    $migrate_plus_migration_store_config->save();

    drupal_set_message(t('Stores have been imported.'), 'status');
  }

}
