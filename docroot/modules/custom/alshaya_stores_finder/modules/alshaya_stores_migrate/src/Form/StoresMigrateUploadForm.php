<?php

namespace Drupal\alshaya_stores_migrate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

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
    $form['upload'] = [
      '#title' => $this->t('Stores CSV'),
      '#description' => $this->t('Upload the CSV exported from Google Business Places'),
      '#type' => 'file',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Upload'),
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
    file_unmanaged_copy($form_state->getValue('upload')->getFileUri(), 'public://import/stores/', FILE_EXISTS_REPLACE);
  }

}
