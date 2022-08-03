<?php

namespace Drupal\alshaya_facets_pretty_paths;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\IntegrityConstraintViolationException;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class Alshaya Facets Pretty Aliases.
 *
 * @package Drupal\alshaya_facets_pretty_paths
 */
class AlshayaFacetsPrettyAliases {

  public const ALIAS_TABLE = 'facets_pretty_path';

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * AlshayaFacetsPrettyPathsHelper constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   */
  public function __construct(Connection $connection,
                              LanguageManagerInterface $language_manager,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->connection = $connection;
    $this->languageManager = $language_manager;
    $this->logger = $logger_factory->get('alshaya_facets_pretty_paths');
  }

  /**
   * Get aliases for given facet.
   *
   * @param string $facet_alias
   *   Facet alias to get all the filter values and aliases for.
   * @param string|null $langcode
   *   The language code.
   *
   * @return array
   *   Aliases array with value as key.
   */
  public function getAliasesForFacet(string $facet_alias, string $langcode = NULL) {
    $static = &drupal_static(self::ALIAS_TABLE, []);

    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    if (isset($static[$facet_alias][$langcode])) {
      return $static[$facet_alias][$langcode];
    }

    $select = $this->connection->select(self::ALIAS_TABLE);
    $select->fields(self::ALIAS_TABLE, ['facet_alias', 'value', 'alias']);
    $select->condition('language', $langcode);
    $result = $select->execute()->fetchAll();

    foreach ($result as $row) {
      $static[$row->facet_alias][$langcode][trim($row->value)] = trim($row->alias);
    }

    return $static[$facet_alias][$langcode] ?? [];
  }

  /**
   * Add alias for particular value in database.
   *
   * @param string $facet_alias
   *   Facet Alias.
   * @param string $value
   *   Facet filter value.
   * @param string $alias
   *   Alias for the filter value.
   * @param string|null $language
   *   The language code.
   *
   * @throws \Exception
   */
  public function addAlias(string $facet_alias, string $value, string $alias, string $language = NULL) {
    if (empty($language)) {
      $language = $this->languageManager->getCurrentLanguage()->getId();
    }

    $data = [
      'value' => $value,
      'alias' => $alias,
      'facet_alias' => $facet_alias,
      'language' => $language,
    ];

    try {
      $insert = $this->connection->insert(self::ALIAS_TABLE);
      $insert->fields(array_keys($data));
      $insert->values($data);
      $insert->execute();
    }
    catch (IntegrityConstraintViolationException $e) {
      // Do nothing, other process might have entered the data already.
    }
    catch (\Exception $e) {
      // Log exceptions other than Integrity constraints.
      $this->logger->warning('Error occurred while inserting facet alias: %message', [
        '%message' => $e->getMessage(),
      ]);
    }

    // Set the value in static array.
    $static = &drupal_static(self::ALIAS_TABLE, []);
    $static[$facet_alias][$value] = $alias;
  }

}
