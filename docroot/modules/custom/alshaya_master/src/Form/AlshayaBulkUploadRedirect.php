<?php

namespace Drupal\alshaya_master\Form;

use Drupal\Core\Form\FormBase;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\redirect\Entity\Redirect;

/**
 * Class AlshayaBulkUploadRedirect.
 */
class AlshayaBulkUploadRedirect extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_redirect_pattern_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload file'),
      '#required' => TRUE,
      '#description' => $this->t('Supports csv file.'),
      '#upload_validators'  => [
        'file_validate_extensions' => ['csv CSV'],
      ],
    ];

    $languages = [];
    $languages_list = \Drupal::languageManager()->getLanguages();
    foreach ($languages_list as $language) {
      $languages[$language->getId()] = $language->getName();
    }
    $form['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#default_value' => 'en',
      '#options' => $languages,
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Upload file'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fid = $form_state->getValue('file')[0];
    $langcode = !empty($form_state->getValue('language')) ? $form_state->getValue('language') : 'en';
    if ($file = File::load($fid)) {
      // Get file uri.
      $csv_uri = $file->getFileUri();
      if (!empty($csv_uri)) {
        // Open file handler.
        $handle = fopen($csv_uri, 'r');
        // Read csv file handler.
        while ($row = fgetcsv($handle)) {
          // Prepare redirect array data.
          $redirects = [];
          // Read the complete csv data string.
          $data = str_getcsv($row[0], "\r");
          // If get any data/row from csv file.
          if (!empty($data)) {
            // Unset header row as header not required.
            unset($data[0]);
            foreach ($data as $i => $csv_row) {
              // Explode the row by ';'.
              $exploded_row = explode(';', $csv_row);
              $redirects[$i][] = trim($exploded_row[0], ' "');
              $redirects[$i][] = trim($exploded_row[1], ' "');
            }
          }
        }
        // Close file handler.
        fclose($handle);

        // Prepare batch.
        $batch = [
          'operations' => [],
          'finished' => [AlshayaBulkUploadRedirect::class, 'finishBatch'],
          'title' => $this->t('Importing redirects'),
          'init_message' => $this->t('Starting redirect import.'),
          'progress_message' => $this->t('Completed @current step of @total.'),
          'error_message' => $this->t('Redirect import has encountered an error.'),
        ];

        $redirect_chunks = array_chunk($redirects, 1);
        foreach ($redirect_chunks as $redirect_chunk) {
          $batch['operations'][] = [
            [
              AlshayaBulkUploadRedirect::class, 'processBatch',
            ],
            [
              $redirect_chunk,
              $langcode,
            ],
          ];
        }

        batch_set($batch);
      }
    }
  }

  /**
   * Batch process callback.
   *
   * @param array $redirect_chunk
   *   Redirect chunk array.
   * @param string $langcode
   *   The langcode.
   * @param array $context
   *   Context array.
   */
  public static function processBatch(array $redirect_chunk, $langcode, array &$context) {
    // Include product utility file to use helper functions.
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

    // Redirect storage.
    $redirect_repository = \Drupal::service('redirect.repository');

    foreach ($redirect_chunk as $redirect) {
      // Get the node/product of the sku.
      $node = alshaya_acm_product_get_display_node($redirect[0]);
      // If node object.
      if ($node && $node instanceof NodeInterface) {
        // If redirect already exists for the given source, no need to process.
        $redirect_exists = $redirect_repository->findBySourcePath($redirect[1]);
        if ($redirect_exists && !empty($redirect_exists)) {
          $continue = FALSE;
          /* @var \Drupal\redirect\Entity\Redirect $redirect_object*/
          foreach ($redirect_exists as $redirect_object) {
            // If redirect exist for the given language.
            if ($langcode == $redirect_object->get('language')->getValue()[0]['value']) {
              $continue = TRUE;
              break;
            }
          }

          // If redirect already exists for the given langcode, skip further.
          if ($continue) {
            continue;
          }
        }

        $redirect_entity = [
          'redirect_source' => $redirect[1],
          'redirect_redirect' => 'entity:node/' . $node->id(),
          'status_code' => '301',
          'language' => $langcode,
        ];
        $new_redirect = Redirect::create($redirect_entity);
        $new_redirect->save();
      }
    }
  }

  /**
   * Batch finished callback.
   *
   * @param bool $success
   *   Success or fail import.
   * @param array $results
   *   Result array.
   * @param array $operations
   *   Operation array.
   */
  public static function finishBatch($success, array $results = [], array $operations = []) {
    if ($success) {
      $message = t('Redirects imported successfully.');
      drupal_set_message($message, 'success');
    }
    else {
      $message = t('There was some error while importing redirects.');
      drupal_set_message($message, 'error');
    }
  }

}
