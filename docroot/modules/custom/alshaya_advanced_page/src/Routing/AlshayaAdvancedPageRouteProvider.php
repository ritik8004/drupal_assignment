<?php

namespace Drupal\alshaya_advanced_page\Routing;

use Drupal\Core\Routing\RouteProvider;

/**
 * Class AlshayaAdvancedPageRouteProvider.
 */
class AlshayaAdvancedPageRouteProvider extends RouteProvider {

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

        // If we have both route `term canonical` and `term edit` available,
        // this case happens only for the term edit url. We check for the
        // `edit` in url as well to make sure term/edit screen and not process
        // further if term edit screen.
        if (($collection->get('entity.taxonomy_term.edit_form')
            || $collection->get('entity.taxonomy_term.content_translation_add')
            || $collection->get('entity.taxonomy_term.content_translation_overview')
            || $collection->get('entity.taxonomy_term.delete_form'))
          && isset($exploded_path[4])
          && in_array($exploded_path[4], ['edit', 'translations', 'delete'])) {
          return $collection;
        }

        $department_node = alshaya_advanced_page_is_department_page($exploded_path[3]);
        // If department page exists.
        if ($department_node) {
          $node_route = $this->connection->query("SELECT name, route, fit FROM {" . $this->connection->escapeTable($this->tableName) . "} WHERE name = 'entity.node.canonical'")
            ->fetchAll(\PDO::FETCH_ASSOC);
          if ($node_route) {
            /* @var \Symfony\Component\Routing\Route $route */
            $route = unserialize($node_route[0]['route']);
            // Setting options to identify the department page later.
            $route->setOption('_department_page_term', $exploded_path[3]);
            $route->setOption('_department_page_node', $department_node);
            $collection->add($node_route[0]['name'], $route);
          }
        }
      }
    }

    return $collection;
  }

}
