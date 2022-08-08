<?php

namespace Drupal\alshaya_i18n\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\locale\StringStorageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Component\Datetime\TimeInterface;

/**
 * Alshaya Export Translation Commands class.
 */
class AlshayaExportTranslationCommands extends DrushCommands {

  /**
   * The export directory path.
   *
   * @var string
   */
  public const PATH = 'public://exports/v3/translations/';

  /**
   * The filename prefix for the output file.
   *
   * @var string
   */
  public const FILE_NAME_PREFIX = 'translation-data';

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
   * Database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The date time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $dateTime;

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
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection service.
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   The date time service.
   */
  public function __construct(
    FileSystemInterface $file_system,
    LoggerChannelFactoryInterface $logger_factory,
    StringStorageInterface $string_storage,
    LanguageManagerInterface $language_manager,
    Connection $database,
    TimeInterface $date_time
  ) {
    $this->fileSystem = $file_system;
    $this->logger = $logger_factory->get('alshaya_rcs_export_translation');
    $this->stringStorage = $string_storage;
    $this->languageManager = $language_manager;
    $this->database = $database;
    $this->dateTime = $date_time;
  }

  /**
   * Exports alshaya translation into CSV.
   *
   * @command alshaya:export-translation
   *
   * @options all-translations
   *   Get all user interface translations.
   *
   * @aliases alshaya:et, aet
   *
   * @usage drush aet
   *   Get user interface translations which has context.
   * @usage drush aet --all-translations
   *   Get all user interface translations with and without context.
   */
  public function exportTranslationCsv($options = ['all-translations' => FALSE]) {

    // Create directory for export if it doesn't exists.
    $path = self::PATH;
    // Delete all existing translations exports.
    try {
      $this->fileSystem->deleteRecursive($path);
    }
    catch (FileException) {
      $this->logger->notice('Files not deleted as directory may not exists.');
    }

    $output_directory = $this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    if (!$output_directory) {
      $this->logger->error('Could not read/create the directory to export the data.');
      return;
    }

    // Create translation csv.
    try {
      $timestamp = $this->dateTime->getCurrentTime();
      $file_location = $this->fileSystem->getDestinationFilename($path . self::FILE_NAME_PREFIX . '-' . $timestamp . '.csv', FileSystemInterface::EXISTS_REPLACE);
      if ($file_location) {
        $file = fopen($file_location, 'wb+');
        // Insert translations into csv file.
        $this->insertTranslationsIntoCsv($file, $options['all-translations']);
        fclose($file);
        $csv_url = file_create_url($file_location);
        $this->output->writeln(dt('Successfully exported translations into csv: @csv_url', ['@csv_url' => $csv_url]));
      }
    }
    catch (FileException) {
      $this->logger->error('Could not create the  translation csv file.');
    }
  }

  /**
   * Put translations into the csv.
   *
   * @param mixed $file
   *   The csv file resource.
   * @param bool $all_translations
   *   Get all translations.
   */
  private function insertTranslationsIntoCsv($file, $all_translations) {
    // Check for file resource.
    if (!\is_resource($file)) {
      $this->logger->error('No file resource found.');
      return;
    }
    // Get array of user interface translations.
    $translation_list = $this->getCustomTranslations($all_translations);
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
   * @param bool $all_translations
   *   Get all translations.
   *
   * @return array
   *   List of user interface translations.
   */
  private function getCustomTranslations($all_translations) {
    $langcodes = $this->languageManager->getLanguages();
    $langcodes_list = array_keys($langcodes);

    // Get all translate contexts if all translations drush option is empty.
    if (!$all_translations) {
      $query = $this->database->select('locales_source', 's')
        ->fields('s', ['context'])
        ->condition('s.context', '', '!=')
        ->distinct(TRUE);
      $contexts = $query->execute()->fetchAllKeyed(0, 0);
    }

    // Iterate through all languages.
    $translate_list = [];
    foreach ($langcodes_list as $langcode) {
      $conditions = [
        'language' => $langcode,
        'translated' => TRUE,
        'customized' => 1,
      ];
      if (!$all_translations) {
        $conditions['context'] = array_values($contexts);
      }
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
