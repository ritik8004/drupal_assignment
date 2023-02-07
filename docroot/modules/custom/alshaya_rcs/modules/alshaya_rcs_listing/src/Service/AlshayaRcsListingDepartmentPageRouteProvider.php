<?php

namespace Drupal\alshaya_rcs_listing\Service;

use Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper;
use Drupal\Core\Routing\RouteProvider;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Database\Connection;

/**
 * Class Alshaya RCS Listing Route Provider.
 */
class AlshayaRcsListingDepartmentPageRouteProvider extends RouteProvider {

  /**
   * Department page helper.
   *
   * @var \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper
   */
  protected $departmentPageHelper;

  /**
   * Constructs a new AlshayaRcsListingDepartmentPageRouteProvider.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   A database connection object.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Drupal\Core\PathProcessor\InboundPathProcessorInterface $path_processor
   *   The path processor.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tag_invalidator
   *   The cache tag invalidator.
   * @param string $table
   *   (Optional) The table in the database to use for matching.
   *   Defaults to 'router'.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   (Optional) The language manager.
   * @param \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper $department_page_helper
   *   Department page helper.
   */
  public function __construct(
    Connection $connection,
    StateInterface $state,
    CurrentPathStack $current_path,
    CacheBackendInterface $cache_backend,
    InboundPathProcessorInterface $path_processor,
    CacheTagsInvalidatorInterface $cache_tag_invalidator,
    $table,
    LanguageManagerInterface $language_manager,
    AlshayaDepartmentPageHelper $department_page_helper
  ) {
    parent::__construct(
      $connection,
      $state,
      $current_path,
      $cache_backend,
      $path_processor,
      $cache_tag_invalidator,
      $table,
      $language_manager
    );

    $this->departmentPageHelper = $department_page_helper;
  }

  /**
   * {@inheritdoc}
   */
  protected function getRoutesByPath($path) {
    $exploded_path = [];
    $collection = parent::getRoutesByPath($path);
    // If collection has term view route.
    if (!empty($collection) && $collection->get('entity.taxonomy_term.canonical')) {
      $exploded_path = explode('/', $path);
    }

    // If RCS function exist for checking department page,
    // proceed and check for department page existance.
    if (!empty($collection)
      && isset($exploded_path[3])
      && is_numeric($exploded_path[3])
    ) {
      // With V3 we use slug and not not term reference so we need the original
      // path (example: shop-kids) and not internal one (taxonomy/term/[tid]).
      // For this RCS provides a way to get original path if it had processed
      // and converted the value available in $path. We use it to get the
      // original path and check from slug.
      $filtered_path = RcsPhPathProcessor::getOrignalPathFromProcessed($path);
      preg_match('/^\/(.*)\/$/', $filtered_path, $matches);
      $filtered_path = $matches[1] ?? '';
      if ($filtered_path) {
        // Get list of department pages.
        $department_node = $this->departmentPageHelper->getDepartmentPageNode($filtered_path);
        $collection = $this->setRouteOptions($collection, $exploded_path, $department_node, TRUE);
      }
    }
    return $collection;
  }

  /**
   * Set the route options.
   *
   * @param Symfony\Component\Routing\RouteCollection $collection
   *   The RouteCollection object.
   * @param array $exploded_path
   *   An array of exploded url items.
   * @param mixed $department_node
   *   The department node id/FALSE if empty.
   * @param bool $term_option
   *   A boolean value to check if the options needs to be set for term.
   *
   * @return Symfony\Component\Routing\RouteCollection
   *   The routecollection object.
   */
  private function setRouteOptions(RouteCollection $collection, array $exploded_path, $department_node, bool $term_option = FALSE) {
    // If department page exists.
    if ($department_node) {
      $node_route = $this->connection->select($this->connection->escapeTable($this->tableName), 'rp')
        ->fields('rp', ['name', 'route'])
        ->condition('name', 'entity.node.canonical')
        ->execute()->fetchAll(\PDO::FETCH_ASSOC);

      if ($node_route) {
        /** @var \Symfony\Component\Routing\Route $route */
        // phpcs:ignore
        $route = unserialize($node_route[0]['route']);
        // Setting options to identify the department page later.
        if ($term_option) {
          $route->setOption('_department_page_term', $exploded_path[3]);
        }
        $route->setOption('_department_page_node', $department_node);
        $collection->add($node_route[0]['name'], $route);
      }
    }

    return $collection;
  }

}
