<?php

namespace Drupal\alshaya_facets_pretty_paths;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\IntegrityConstraintViolationException;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Class AlshayaFacetsPrettyAliases.
 *
 * @package Drupal\alshaya_facets_pretty_paths
 */
class AlshayaFacetsPrettyAliases {

  const ALIAS_TABLE = 'facets_pretty_path';

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
   * AlshayaFacetsPrettyPathsHelper constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   */
  public function __construct(Connection $connection,
                              LanguageManagerInterface $language_manager) {
    $this->connection = $connection;
    $this->languageManager = $language_manager;
  }

  /**
   * Get aliases for given facet.
   *
   * @param string $facet_alias
   *   Facet alias to get all the filter values and aliases for.
   *
   * @return array
   *   Aliases array with value as key.
   */
  public function getAliasesForFacet(string $facet_alias) {
    $static = &drupal_static(self::ALIAS_TABLE, []);
    if (isset($static[$facet_alias])) {
      return $static[$facet_alias];
    }

    $select = $this->connection->select(self::ALIAS_TABLE);
    $select->fields(self::ALIAS_TABLE, ['facet_alias', 'value', 'alias']);

    $language = $this->languageManager->getCurrentLanguage()->getId();
    $select->condition('language', $language);

    $result = $select->execute()->fetchAll();
    foreach ($result as $row) {
      $static[$row->facet_alias][$row->value] = $row->alias;
    }

    return $static[$facet_alias];
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
   */
  public function addAlias(string $facet_alias, string $value, string $alias) {
    $language = $this->languageManager->getCurrentLanguage()->getId();

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

    // Set the value in static array.
    $static = &drupal_static(self::ALIAS_TABLE, []);
    $static[$facet_alias][$value] = $alias;
  }

}
