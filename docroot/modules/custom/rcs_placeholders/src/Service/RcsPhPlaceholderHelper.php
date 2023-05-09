<?php

namespace Drupal\rcs_placeholders\Service;

use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides helper services for placeholders.
 */
class RcsPhPlaceholderHelper {

  /**
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Queries array to compute.
   *
   * @var array
   */
  protected $queries = [];

  /**
   * Constructs a new RcsPhPlaceholderHelper object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * Returns all the graphQL queries used for RCS.
   *
   * @return array
   *   The computed graphql queries.
   */
  public function getRcsPlaceholderGraphqlQueries(): array {
    if (empty($this->queries)) {
      // Invoke hook to get default values.
      $queries = $this->moduleHandler->invokeAll('rcs_placeholders_graphql_query');
      // Allow other modules to alter the data.
      $this->moduleHandler->alter('rcs_placeholders_graphql_query', $queries);
      $this->doSet($queries);
    }
    return $this->queries;
  }

  /**
   * Returns graphQL query for type.
   *
   * @return array
   *   The computed graphql query for particular type.
   */
  public function getRcsPlaceholderGraphqlQueryForType(string $type): array {
    $queries = $this->getRcsPlaceholderGraphqlQueries();
    // Check if we need to return particular query type.
    if (array_key_exists($type, $queries)) {
      return $queries[$type];
    }
    return [];
  }

  /**
   * Setter method for graphql queries.
   *
   * @param array $queries
   *   The query array to set.
   */
  public function doSet(array $queries): void {
    $this->queries = $queries;
  }

}
