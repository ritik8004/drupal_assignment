<?php

namespace Drupal\alshaya_seo_transac\Form;

use Drupal\Core\Form\FormBase;
use Drupal\redirect\Entity\Redirect;
use Drupal\Core\Form\FormStateInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Markup;

/**
 * Class Alshaya Bulk Upload Redirect url.
 */
class AlshayaBulkUploadRedirectUrl extends FormBase {

  /**
   * Contains redirect data read from CSV.
   *
   * @var array
   */
  protected $redirects = [];

  /**
   * File Storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * Language Manager service object.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Path alias anager service object.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $pathAlias;

  /**
   * AlshayaBulkUploadRedirectUrl constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager service object.
   * @param \Drupal\path_alias\AliasManagerInterface $path_alias_manager
   *   Alias manager service object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              LanguageManagerInterface $language_manager,
                              AliasManagerInterface $path_alias_manager) {
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->languageManager = $language_manager;
    $this->pathAlias = $path_alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('path_alias.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_redirect_url_pattern_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload file'),
      '#description' => Markup::create($this->helpText()),
      '#required' => TRUE,
      '#upload_validators'  => [
        'file_validate_extensions' => ['csv CSV'],
      ],
    ];

    $languages = [];
    $languages_list = $this->languageManager->getLanguages();
    foreach ($languages_list as $language) {
      $languages[$language->getId()] = $language->getName();
    }
    $form['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#description' => $this->t('Choose your language for redirection.'),
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
    $fid = $form_state->getValue('file')[0];
    // Load file.
    $file = $this->fileStorage->load($fid);
    if (!$file) {
      // If unable to load file.
      $form_state->setErrorByName('file', $this->t('There was some error in loading file. Please try again.'));
    }
    $csv_uri = $file->getFileUri();
    if (empty($csv_uri)) {
      // If unable to get file uri.
      $form_state->setErrorByName('file', $this->t('There was some error in loading file. Please try again.'));
    }
    // Check for csv data.
    $this->csvDataChecks($form_state, $csv_uri);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $langcode = !empty($form_state->getValue('language')) ? $form_state->getValue('language') : 'en';
    // Prepare batch.
    $batch = [
      'operations' => [],
      'finished' => [AlshayaBulkUploadRedirectUrl::class, 'finishBatch'],
      'title' => $this->t('Importing redirects'),
      'init_message' => $this->t('Starting redirect import.'),
      'progress_message' => $this->t('Completed @current step of @total.'),
      'error_message' => $this->t('Redirect import has encountered an error.'),
    ];

    $redirect_chunks = array_chunk($this->redirects, 1);
    foreach ($redirect_chunks as $redirect_chunk) {
      $batch['operations'][] = [
        [
          AlshayaBulkUploadRedirectUrl::class, 'processBatch',
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
    // Redirect storage.
    $redirect_repository = \Drupal::service('redirect.repository');

    foreach ($redirect_chunk as $redirect) {
      // If redirect already exists for the given source, no need to process.
      try {
        $redirect_exists = $redirect_repository->findMatchingRedirect($redirect[0], [], $langcode);
        // If redirect already exists.
        if ($redirect_exists) {
          \Drupal::messenger()->addMessage(t('Please check, redirect from @source is already existing.', [
            '@source' => $redirect[0],
          ]), 'warning');

          continue;
        }

        $redirect_entity = [
          'redirect_source' => $redirect[0],
          'redirect_redirect' => $redirect[1],
          'status_code' => '301',
          'language' => $langcode,
        ];
        $new_redirect = Redirect::create($redirect_entity);
        $new_redirect->save();
      }
      catch (\Exception $e) {
        // If any exception.
        $message = t('There was some problem in adding redirect for the url @url. Please check if redirect already exists or not.',
        ['@url' => $redirect[0]]);

        \Drupal::messenger()->addMessage($message, 'error');
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
    // Setting the message.
    $message = isset($success) ? t('Redirects imported successfully.') :
    t('There was some error while importing redirects.');
    // Setting the status of message.
    $status = isset($success) ? 'status' : 'error';

    \Drupal::messenger()->addMessage($message, $status);
  }

  /**
   * Csv data checks function.
   *
   * @param object $form_state
   *   Form state object.
   * @param string $csv_uri
   *   Csv uri.
   */
  private function csvDataChecks($form_state, $csv_uri) {
    // Re-initialising variables as causing duplicate entries.
    $this->redirects = [];
    $handle = fopen($csv_uri, 'r');
    if (empty($handle)) {
      return $form_state->setErrorByName('file', $this->t('There was some error in opening the file. Please try again.'));
    }
    // Read csv file handler.
    $i = 0;
    while ($data = fgetcsv($handle, NULL, "\r")) {
      // Iterate row values.
      foreach ($data as $d) {
        $i++;
        $row = explode(',', $d);
        // Process only for exact two columns count.
        if (count($row) < 2) {
          // If there is some discrepancy in column count.
          return $form_state->setErrorByName('file', $this->t('There is some discrepancy in column at row @row. Please check.', ['@row' => $i]));
        }
        if (empty($row[0]) && empty($row[1])) {
          // Missing data.
          return $form_state->setErrorByName('file', $this->t('Data is not available at row @row. Please check.', ['@row' => $i]));
        }
        if ((strpos($row[0], ' ') === TRUE) ||
        (strpos($row[1], ' ') === TRUE)) {
          // Corrupted urls.
          return $form_state->setErrorByName('file', $this->t('Url is containing space at row @row. Please check.', ['@row' => $i]));
        }
        if ($i > 1) {
          $redirection_data = $this->prepareRedirectionData($row);
          $this->redirects[] = $redirection_data;
        }
      } // loop end.
    }

    // Close file handler.
    fclose($handle);

    // If no data after processing csv or just contains only header.
    if (empty($this->redirects) || count($this->redirects) < 2) {
      return $form_state->setErrorByName('file', $this->t('CSV file has no data.'));
    }

  }

  /**
   * Function to prepare redirect data.
   *
   * @param array $row
   *   Csv row data.
   *
   * @return array
   *   Redirection paths.
   */
  private function prepareRedirectionData(array $row) {
    $data = [];
    // Get path from both rows and
    // exclude langcode & backslash trailing.
    // Store only url path.
    $from = parse_url($row[0], PHP_URL_PATH);
    // Remove langcode and both end backslash.
    // Example: "/en/test/level1/" convert into "test/level1".
    $redirect_source = trim(substr($from, strpos($from, '/', 1)), '/');

    $to = parse_url($row[1], PHP_URL_PATH);
    // Remove langcode and back end backslash.
    // Example: "/en/test/another-path/" convert into "/test/another-path".
    $redirect_to = rtrim(substr($to, strpos($to, '/', 1)), '/');

    // From and to urls are different.
    if ($redirect_source != trim($redirect_to, '/')) {
      // Get the entity path by path alias.
      $path = $this->pathAlias->getPathByAlias($redirect_to);
      $redirect_redirect = "internal:$path";
      // Store redirect source and destination path.
      $data = [
        $redirect_source,
        $redirect_redirect,
      ];
    }

    return $data;
  }

  /**
   * Function to have help text.
   *
   * @return string
   *   Help text.
   */
  private function helpText() {
    $help_text = '<span>Supported comma separator csv file format:</span>
                  <ul>
                    <li>Total 2 columns => From,To</li>
                    <li>
                      <p> Sample CSV with 1 row: </p>
                      <strong><p> From,To</p>
                      <p> http://example.com/test,http://example.com/test/test-l2</p></strong>
                    </li>
                  </ul>';

    return $help_text;
  }

}
