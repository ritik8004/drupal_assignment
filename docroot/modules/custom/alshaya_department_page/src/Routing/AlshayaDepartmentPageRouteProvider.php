<?php

namespace Drupal\alshaya_department_page\Routing;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Routing\RouteProvider;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class AlshayaDepartmentPageRouteProvider.
 */
class AlshayaDepartmentPageRouteProvider extends RouteProvider {

  /**
   * {@inheritdoc}
   */
  protected function getRoutesByPath($path) {
    // Split the path up on the slashes, ignoring multiple slashes in a row
    // or leading or trailing slashes. Convert to lower case here so we can
    // have a case-insensitive match from the incoming path to the lower case
    // pattern outlines from \Drupal\Core\Routing\RouteCompiler::compile().
    // @see \Drupal\Core\Routing\CompiledRoute::__construct()
    $parts = preg_split('@/+@', Unicode::strtolower($path), NULL, PREG_SPLIT_NO_EMPTY);

    $collection = new RouteCollection();

    $ancestors = $this->getCandidateOutlines($parts);
    if (empty($ancestors)) {
      return $collection;
    }

    // The >= check on number_parts allows us to match routes with optional
    // trailing wildcard parts as long as the pattern matches, since we
    // dump the route pattern without those optional parts.
    try {
      $routes = $this->connection->query("SELECT name, route, fit FROM {" . $this->connection->escapeTable($this->tableName) . "} WHERE pattern_outline IN ( :patterns[] ) AND number_parts >= :count_parts", [
        ':patterns[]' => $ancestors,
        ':count_parts' => count($parts),
      ])
        ->fetchAll(\PDO::FETCH_ASSOC);
    }
    catch (\Exception $e) {
      $routes = [];
    }

    // Check if need department page when coming from the term view page.
    if (!empty($routes)) {
      foreach ($routes as $route) {
        // If term view page.
        if ($route['name'] == 'entity.taxonomy_term.canonical') {
          $exploded_path = explode('/', $path);
          // Get tid from path.
          if (isset($exploded_path[3]) && is_numeric($exploded_path[3])) {
            $department_node = alshaya_department_page_is_department_page($exploded_path[3]);
            if ($department_node) {
              $node_route = $this->connection->query("SELECT name, route, fit FROM {" . $this->connection->escapeTable($this->tableName) . "} WHERE name = 'entity.node.canonical'")
                ->fetchAll(\PDO::FETCH_ASSOC);
              $routes = $node_route;
              break;
            }
          }
        }
      }
    }

    // We sort by fit and name in PHP to avoid a SQL filesort and avoid any
    // difference in the sorting behavior of SQL back-ends.
    usort($routes, [$this, 'routeProviderRouteCompare']);

    foreach ($routes as $row) {
      $collection->add($row['name'], unserialize($row['route']));
    }

    return $collection;
  }

}
