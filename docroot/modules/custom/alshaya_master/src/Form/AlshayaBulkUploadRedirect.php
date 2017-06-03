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
   * Contains redirect data read from CSV.
   *
   * @var array
   */
  protected $redirects = [];

  /**
   * Contains list of skus read from csv.
   *
   * @var array
   */
  protected $skus = [];

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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Re-initialising variables as causing duplicate entries.
    $this->redirects = [];
    $this->skus = [];

    $fid = $form_state->getValue('file')[0];
    // Load file.
    if ($file = File::load($fid)) {
      $csv_uri = $file->getFileUri();
      if (!empty($csv_uri)) {
        // Open file handler.
        if ($handle = fopen($csv_uri, 'r')) {
          // Read csv file handler.
          $i = 0;
          while ($row = fgetcsv($handle)) {
            $i++;
            // Process only for exact two columns count.
            if (count($row) == 2) {
              // Both column have data.
              if (!empty($row[0]) && !empty($row[1])) {
                // If url contains no space in between.
                if (strpos($row[1], ' ') === FALSE) {
                  // If duplicate sku in csv.
                  if (!in_array($row[0], $this->skus)) {
                    $this->skus[] = $row[0];
                    $this->redirects[] = [$row[0], $row[1]];
                  }
                  else {
                    $form_state->setErrorByName('file', $this->t('Duplicate sku id at row @row. Please check.', ['@row' => $i]));
                  }
                }
                else {
                  $form_state->setErrorByName('file', $this->t('Url is containing space at row @row. Please check.', ['@row' => $i]));
                }
              }
              else {
                $form_state->setErrorByName('file', $this->t('Data is not available at row @row. Please check.', ['@row' => $i]));
              }
            }
            else {
              // If there is some discrepancy in column count.
              $form_state->setErrorByName('file', $this->t('There is some discrepancy in column at row @row. Please check.', ['@row' => $i]));
            }
          }
          // Close file handler.
          fclose($handle);

          // If no data after processing csv or just contains only header.
          if (empty($this->redirects) || count($this->redirects) < 2) {
            $form_state->setErrorByName('file', $this->t('CSV file has no data.'));
          }
        }
        else {
          $form_state->setErrorByName('file', $this->t('There was some error in opening the file. Please try again.'));
        }
      }
      else {
        // If unable to get file uri.
        $form_state->setErrorByName('file', $this->t('There was some error in loading file. Please try again.'));
      }
    }
    else {
      // If unable to load file.
      $form_state->setErrorByName('file', $this->t('There was some error in loading file. Please try again.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $langcode = !empty($form_state->getValue('language')) ? $form_state->getValue('language') : 'en';
    // Prepare batch.
    $batch = [
      'operations' => [],
      'finished' => [AlshayaBulkUploadRedirect::class, 'finishBatch'],
      'title' => $this->t('Importing redirects'),
      'init_message' => $this->t('Starting redirect import.'),
      'progress_message' => $this->t('Completed @current step of @total.'),
      'error_message' => $this->t('Redirect import has encountered an error.'),
    ];

    // Removing the header data.
    unset($this->redirects[0]);

    $redirect_chunks = array_chunk($this->redirects, 1);
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
        try {
          $redirect_exists = $redirect_repository->findMatchingRedirect($redirect[1], [], $langcode);
          // If redirect already exists.
          if ($redirect_exists) {
            continue;
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
        catch (\Exception $e) {
          // If any exception.
          drupal_set_message(t('There was some problem in adding redirect for the url @url. Please check if redirect already exists or not.', ['@url' => $redirect[1]]));
        }
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
