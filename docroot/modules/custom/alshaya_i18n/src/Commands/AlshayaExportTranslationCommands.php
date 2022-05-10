<?php

namespace Drupal\alshaya_i18n\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\locale\StringStorageInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Alshaya Export Translation Commands class.
 */
class AlshayaExportTranslationCommands extends DrushCommands {

  /**
   * The export directory path.
   *
   * @var string
   */
  const PATH = 'public://exports/v3/';

  /**
   * The filename prefix for the output file.
   *
   * @var string
   */
  const FILE_NAME_PREFIX = 'translation-data';

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
  protected $logger;

  /**
   * The locale string storage.
   *
   * @var Drupal\locale\StringStorageInterface
   */
  protected $stringStorage;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaExportTranslationCommands constructor.
   *
   * @param Drupal\Core\File\FileSystemInterface $file_system
   *   File system service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\locale\StringStorageInterface $string_storage
   *   Locale string storage.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(
    FileSystemInterface $file_system,
    LoggerChannelFactoryInterface $logger_factory,
    StringStorageInterface $string_storage,
    LanguageManagerInterface $language_manager
  ) {
    $this->fileSystem = $file_system;
    $this->logger = $logger_factory->get('alshaya_rcs_export_translation');
    $this->stringStorage = $string_storage;
    $this->languageManager = $language_manager;
  }

  /**
   * Exports alshaya translation into CSV.
   *
   * @command alshaya:export-translation
   *
   * @aliases alshaya:et, aet
   *
   * @usage drush aet
   */
  public function exportTranslationCsv($options = []) {

    // Create directory for export if it doesn't exists.
    $path = self::PATH;
    $output_directory = $this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    if (!$output_directory) {
      $this->logger->error('Could not read/create the directory to export the data.');
      return;
    }

    // Create translation csv.
    try {
      $file_location = $this->fileSystem->getDestinationFilename($path . self::FILE_NAME_PREFIX . '.csv', FileSystemInterface::EXISTS_REPLACE);
      if ($file_location) {
        $file = fopen($file_location, 'wb+');
        // Insert translations into csv file.
        $this->insertTranslationsIntoCsv($file);
        fclose($file);
        $csv_url = file_create_url($file_location);
        $this->output->writeln(dt('Successfully exported translations into csv: @csv_url', ['@csv_url' => $csv_url]));
      }
    }
    catch (FileException $e) {
      $this->logger->error('Could not create the  translation csv file.');
    }
  }

  /**
   * Put translations into the csv.
   *
   * @param mixed $file
   *   The csv file resource.
   */
  private function insertTranslationsIntoCsv($file) {
    // Check for file resource.
    if (!\is_resource($file)) {
      $this->logger->error('No file resource found.');
      return;
    }
    // Get array of user interface translations.
    $translation_list = $this->getCustomTranslations();
    if (!empty($translation_list)) {
      $headers = [
        'Language',
        'Source String',
        'Translation',
        'Context',
      ];
      // Insert headers as first row.
      fputcsv($file, $headers);
      foreach ($translation_list as $row) {
        fputcsv($file, $row);
      }
    }
    else {
      $this->logger->error('No user interface translations found.');
    }
  }

  /**
   * Get rows of translations to be put into csv.
   *
   * @return array
   *   List of user interface translations.
   */
  private function getCustomTranslations() {
    $langcodes = $this->languageManager->getLanguages();
    $langcodes_list = array_keys($langcodes);

    // Iterate through all languages.
    $translate_list = [];
    foreach ($langcodes_list as $langcode) {
      $conditions = [
        'language' => $langcode,
        'translated' => TRUE,
        'customized' => 1,
      ];
      // Get all custom translation for language.
      $translations = $this->stringStorage->getTranslations($conditions);
      foreach ($translations as $translation) {
        $translate_list[] = [
          'lang' => $langcode,
          'source' => $translation->source,
          'translation' => $translation->translation,
          'context' => $translation->context,
        ];
      }
    }
    return $translate_list;
  }

}
