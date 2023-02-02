<?php

namespace Drupal\alshaya_advanced_page\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;

/**
 * Helper class for Department pages.
 */
class AlshayaDepartmentPageHelper {

  /**
   * Database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructor for AlshayaDepartmentPageHelper.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match.
   */
  public function __construct(
    Connection $connection,
    RouteMatchInterface $route_match
  ) {
    $this->database = $connection;
    $this->routeMatch = $route_match;
  }

  /**
   * Helper function to fetch list of advanced department pages.
   *
   * @return array
   *   Nids of department pages keyed by term ids.
   */
  public function getDepartmentPages() {
    static $department_pages = NULL;

    // We cache the nid-tid relationship for a single page request.
    if (!isset($department_pages)) {
      $query = $this->database->select('node__field_product_category', 'nfpc');
      $query->addField('nfpc', 'field_product_category_target_id', 'tid');
      $query->addField('nfpc', 'entity_id', 'nid');
      $department_pages = $query->execute()->fetchAllKeyed();
    }

    return $department_pages;
  }

  /**
   * Get the department node object based on current route.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return mixed|false
   *   Return Node object if department page, else FALSE.
   */
  public function getDepartmentPageNode(Request $request = NULL) {
    $result = &drupal_static(__FUNCTION__);
    // Return if the static cache is set.
    if (isset($result)) {
      return $result;
    }

    $result = FALSE;
    $node = $this->routeMatch->getParameter('node');

    // Load the processed parameter.
    if ($node) {
      // Check if the current node type is department_page. For revision related
      // pages $node will have nid.
      if ($node instanceof Node && $node->bundle() == 'advanced_page' && $node->get('field_use_as_department_page')->getString()) {
        $result = $node;
      }
    }

    return $result;
  }

}
