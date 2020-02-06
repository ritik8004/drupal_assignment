<?php

namespace Drupal\alshaya_acm_product_category\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GoogleCategoryForm.
 */
class GoogleCategoryForm extends FormBase {

  /**
   * File Storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * Contains list of categories read from csv.
   *
   * @var array
   */
  protected $categories = [];

  /**
   * Contains csv file name that was used for import.
   *
   * @var string
   */
  protected $importFileName = '';

  /**
   * GoogleCategoryForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->fileStorage = $entity_type_manager->getStorage('file');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_acm_product_category_google_category';
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $action = $form_state->getUserInput()['op'] ?? '';

    switch ($action) {
      case $this->t('Import'):
        $this->categories = [];
        $this->importFileName = '';
        $fid = !empty($form_state->getValue('file')) ? $form_state->getValue('file')[0] : '';

        // Load file.
        if (($fid) && $file = $this->fileStorage->load($fid)) {
          $csv_uri = $file->getFileUri();
          $this->importFileName = $file->getFileName();;
          if (!empty($csv_uri)) {
            // Open file handler.
            if ($handle = fopen($csv_uri, 'r')) {
              // Read csv file handler.
              $i = 0;
              while ($data = fgetcsv($handle, NULL, ",")) {
                $i++;
                // Skip header.
                if ($i == 1) {
                  continue;
                }
                // Process only for exact 4 columns count.
                if (count($data) == 4) {
                  $this->categories[] = [$data[0], $data[1], $data[2], $data[3]];
                }
                else {
                  // If there is some discrepancy in column count.
                  $form_state->setErrorByName('file', $this->t('There is some discrepancy in column at row @row. Please check.', ['@row' => $i]));
                }
              }
              // Close file handler.
              fclose($handle);

              // If no data after processing csv or just contains only header.
              if (empty($this->categories) || count($this->categories) < 1) {
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
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['google_mapping_export_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Export Google category mapping'),
    ];
    $form['google_mapping_export_fieldset']['export_mapping'] = [
      '#type' => 'link',
      '#title' => $this->t('Export'),
      '#url' => Url::fromRoute('alshaya_acm_product_category.export_google_category_mapping', [], [
        'attributes' => [
          'class' => [
            'button',
          ],
        ],
      ]),
    ];

    $form['google_mapping_import_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Import Google category mapping'),
    ];
    $form['google_mapping_import_fieldset']['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload file'),
      '#upload_location' => 'public://google_category_mapping/',
      '#required' => TRUE,
      '#description' => $this->t('Supports csv file.'),
      '#upload_validators'  => [
        'file_validate_extensions' => ['csv CSV'],
      ],
    ];
    $form['google_mapping_import_fieldset']['product_options'] = [
      '#type' => 'actions',
      'product_options_action' => [
        '#type' => 'submit',
        '#value' => $this->t('Import'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $action = $form_state->getUserInput()['op'];

    switch ($action) {
      case $this->t('Import'):
        // Prepare batch.
        $batch = [
          'operations' => [],
          'finished' => [GoogleCategoryForm::class, 'finishBatch'],
          'title' => $this->t('Importing google category mapping'),
          'init_message' => $this->t('Starting google category mapping import.'),
          'progress_message' => $this->t('Completed @current of @total.'),
          'error_message' => $this->t('google category mapping import has encountered an error.'),
        ];

        $categories_chunks = array_chunk($this->categories, 1);
        foreach ($categories_chunks as $categories_chunk) {
          $batch['operations'][] = [
            [
              GoogleCategoryForm::class, 'processBatch',
            ],
            [
              $categories_chunk,
              $this->importFileName,
            ],
          ];
        }

        batch_set($batch);
        break;
    }
  }

  /**
   * Batch process callback.
   *
   * @param array $categories_chunk
   *   Categories chunk array.
   * @param string $filename
   *   Filename string.
   * @param array $context
   *   Context array.
   */
  public static function processBatch(array $categories_chunk, $filename, array &$context) {
    foreach ($categories_chunk as $category) {
      /** @var \Drupal\taxonomy\Entity\Term $term */
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($category[1]);
      // If term object.
      if ($term && $term instanceof TermInterface) {
        try {
          if (!empty($category[3])) {
            $field_category_google = $term->get('field_category_google')->getString();
            // Update if not available or
            // there any changes in google category.
            if (!isset($field_category_google) || ($field_category_google != $category[3])) {
              $term->get('field_category_google')->setValue($category[3]);
              $term->save();
            }
          }
        }
        catch (\Exception $e) {
          // If any exception.
          \Drupal::messenger()->addWarning(t('There was some problem in updating category @category. exception: @message.', [
            '@category' => $category[2],
            '@message' => $e->getMessage(),
          ]), 'error');
          \Drupal::logger('alshaya_acm_product_category')->error(t('There was some problem in updating category @category. exception: @message.', [
            '@category' => $category[2],
            '@message' => $e->getMessage(),
          ]));
        }
      }
    }
    // Set filename that will be used in log message.
    if (!isset($context['results']['filename'])) {
      $context['results']['filename'] = $filename;
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
      $message = t('Google category mapping imported successfully.');
      \Drupal::messenger()->addStatus($message);
      \Drupal::logger('alshaya_acm_product_category')->info(t('File used for google category mapping import was @filename', ['@filename' => $results['filename']]));
    }
    else {
      $message = t('There was some error while importing google category mapping.');
      \Drupal::messenger()->addError($message);
      \Drupal::logger('alshaya_acm_product_category')->error(t('File used for google category mapping import was @filename', ['@filename' => $results['filename']]));
    }
  }

}
