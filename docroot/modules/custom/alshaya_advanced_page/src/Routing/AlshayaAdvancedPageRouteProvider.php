<?php

namespace Drupal\alshaya_advanced_page\Routing;

use Drupal\Core\Routing\RouteProvider;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class Alshaya Advanced Page Route Provider.
 */
class AlshayaAdvancedPageRouteProvider extends RouteProvider {

  /**
   * {@inheritdoc}
   */
  protected function getRoutesByPath($path) {
    $exploded_path = [];
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
        $collection = $this->setRouteOptions($collection, $exploded_path, $department_node, TRUE);
      }
    }

    // If V2 function exist for checking department page,
    // proceed and check for department page existance.
    if (function_exists('alshaya_rcs_main_menu_is_department_page')
      && !empty($collection) && isset($exploded_path[3])
      && is_numeric($exploded_path[3])) {
      // With V2 we use slug and not not term reference so we need the original
      // path (example: shop-kids) and not internal one (taxonomy/term/[tid]).
      // For this RCS provides a way to get original path if it had processed
      // and converted the value available in $path. We use it to get the
      // original path and check from slug.
      $filtered_path = RcsPhPathProcessor::getOrignalPathFromProcessed($path);
      preg_match('/^\/(.*)\/$/', $filtered_path, $matches);
      $filtered_path = $matches[1] ?? '';
      if ($filtered_path) {
        // Get list of department pages.
        $department_node = alshaya_rcs_main_menu_is_department_page($filtered_path);
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
