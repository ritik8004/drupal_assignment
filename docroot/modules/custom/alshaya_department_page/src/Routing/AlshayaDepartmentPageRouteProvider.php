<?php

namespace Drupal\alshaya_department_page\Routing;

use Drupal\Core\Routing\RouteProvider;

/**
 * Class Alshaya Department Page Route Provider.
 */
class AlshayaDepartmentPageRouteProvider extends RouteProvider {

  /**
   * {@inheritdoc}
   */
  protected function getRoutesByPath($path) {
    $collection = parent::getRoutesByPath($path);
    // If collection has term view route.
    if (!empty($collection) && $collection->get('entity.taxonomy_term.canonical')) {
      $exploded_path = explode('/', $path);
      // Get tid from path.
      if (isset($exploded_path[3]) && is_numeric($exploded_path[3])) {
        $department_node = alshaya_department_page_is_department_page($exploded_path[3]);
        // If department page exists.
        if ($department_node) {
          $node_route = $this->connection->query("SELECT name, route, fit FROM {" . $this->connection->escapeTable($this->tableName) . "} WHERE name = 'entity.node.canonical'")
            ->fetchAll(\PDO::FETCH_ASSOC);
          if ($node_route) {
            /** @var \Symfony\Component\Routing\Route $route */
            // phpcs:ignore
            $route = unserialize($node_route[0]['route']);
            // Setting options to identify the department page later.
            $route->setOption('_department_page_term', $exploded_path[3]);
            $route->setOption('_department_page_node', $department_node);
            $collection->add($node_route[0]['name'], $route);
            $collection->remove('entity.taxonomy_term.canonical');
          }
        }
      }
    }

    return $collection;
  }

}
