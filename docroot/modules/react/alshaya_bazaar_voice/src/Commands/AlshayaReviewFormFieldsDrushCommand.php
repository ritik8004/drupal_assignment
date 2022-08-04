<?php

namespace Drupal\alshaya_bazaar_voice\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice;

/**
 * Expose drush commands for alshaya_bazaar_voice.
 */
class AlshayaReviewFormFieldsDrushCommand extends DrushCommands {

  /**
   * File system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $langugageManager;

  /**
   * Alshaya BazaarVoice Service.
   *
   * @var Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice
   */
  protected $alshayaBazaarVoice;

  /**
   * The export directory path.
   *
   * @var string
   */
  public const PATH = 'public://exports/v2/';

  /**
   * The filename prefix for the output file.
   *
   * @var string
   */
  public const FILE_NAME_PREFIX = 'review-fields-config-data';

  /**
   * AlshayaReviewFormFieldsDrushCommand constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   File system service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice $alshaya_bazaar_voice
   *   Alshaya BazaarVoice service.
   */
  public function __construct(
    FileSystemInterface $file_system,
    LoggerChannelFactoryInterface $logger_factory,
    LanguageManagerInterface $language_manager,
    AlshayaBazaarVoice $alshaya_bazaar_voice
    ) {
    parent::__construct();
    $this->fileSystem = $file_system;
    $this->drupalLogger = $logger_factory->get('alshaya_bazaar_voice');
    $this->languageManager = $language_manager;
    $this->alshayaBazaarVoice = $alshaya_bazaar_voice;
  }

  /**
   * Export the review form fields configurations as CSV.
   *
   * @command alshaya_bazaar_voice:export-review-fields-config
   *
   * @aliases export-rfc
   *
   * @usage drush export-review-fields-config
   *   Process and output to the file.
   */
  public function exportReviewFormFieldsConfigs() {
    $path = self::PATH;
    $output_directory = $this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);
    if (!$output_directory) {
      $this->drupalLogger->notice('Could not read/create the directory to export the data.');
      return;
    }

    // Check if it is possible to create the output files.
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      try {
        $location = $this->fileSystem->getDestinationFilename($path . self::FILE_NAME_PREFIX . '-' . $langcode . '-' . time() . '.csv', FileSystemInterface::EXISTS_REPLACE);
        if ($location === FALSE) {
          $this->drupalLogger->warning('Could not create the file to export the data.');
          return;
        }
        $this->drupalLogger->notice('Lancode: ' . $langcode . '. File: ' . file_create_url($location));
        // Make the file empty.
        $file = fopen($location, 'wb+');
        fclose($file);
        // Get optional fields and set based on categories.
        $fields_config = $this->alshayaBazaarVoice->getWriteReviewFieldsConfig();
        if ($langcode !== 'en') {
          // Override the config language to the current language.
          $this->languageManager->setConfigOverrideLanguage($language);
          $fields_config_data = $this->alshayaBazaarVoice->getWriteReviewFieldsConfig();
          // Getting the difference of the field configs
          // with the original language config data.
          $result = array_diff_key($fields_config, $fields_config_data);
          // Merging the field configs of original language which are
          // not present in the translated config data.
          $fields_config = array_merge($fields_config_data, $result);
        }

        // Opening the file in append mode.
        $handle = fopen($location, 'a');
        // Declaring headers for CSV.
        $headers = [
          'ID',
          'Translated Title',
          'Type of field',
          'Possible values (if select)',
          'Required yes/no',
          'Minimum length',
          'Maximum length',
          'Default Value',
          'Visible yes/no',
          'Group Type (possible extension attribute)',
          'Wrapper Attributes',
        ];
        // Writing the headers in CSV.
        fputcsv($handle, $headers);

        // Process all the fields and put the same in csv file.
        foreach ($fields_config as $key => $value) {
          // Assigning default values.
          $possible_val = '';
          $wrapper_attributes_val = '';
          $required_val = 'no';
          $title_val = (isset($value['#title']) && !empty($value['#title'])) ? $value['#title'] : $value['#id'];
          // For select type fields.
          if ($value['#type'] === 'select' && !empty($value['#options'])) {
            $possible_val = implode(", ", array_keys($value['#options']));
          }
          if (isset($value['#wrapper_attributes']) && !empty($value['#wrapper_attributes'])) {
            $wrapper_attributes_val = $value['#wrapper_attributes']["class"][0];
          }
          if ($value['#required'] === 1) {
            $required_val = 'yes';
          }
          // Writing the values in CSV.
          fputcsv($handle, [$key, $title_val, $value['#type'], $possible_val,
            $required_val, $value['#minlength'], $value['#maxlength'],
            $value['#default_value'], $value['#visible'],
            $value['#group_type'], $wrapper_attributes_val,
          ]);
        }
        // Close the file handler since we don't need it anymore.
        fclose($handle);
      }
      catch (\Exception $e) {
        $this->drupalLogger->warning(dt('Could not create the file to export the data. Message: @message.', [
          '@message' => $e->getMessage(),
        ]));
      }
    }
  }

}
