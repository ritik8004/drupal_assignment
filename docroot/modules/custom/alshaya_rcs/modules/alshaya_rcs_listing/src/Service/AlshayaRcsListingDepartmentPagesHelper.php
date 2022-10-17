<?php

namespace Drupal\alshaya_rcs_listing\Service;

use Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\path_alias\AliasManager;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;

/**
 * Contains helper methods for RCS Department pages.
 */
class AlshayaRcsListingDepartmentPagesHelper extends AlshayaDepartmentPageHelper {

  /**
   * Cache Backend object for "cache.data".
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Alias manager.
   *
   * @var \Drupal\path_alias\AliasManager
   */
  protected $aliasManager;

  /**
   * Constructor for AlshayaDepartmentPageHelper.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   Current path.
   * @param \Drupal\path_alias\AliasManager $alias_manager
   *   Alias manager.
   */
  public function __construct(
    Connection $connection,
    RouteMatchInterface $route_match,
    CacheBackendInterface $cache,
    EntityTypeManagerInterface $entity_type_manager,
    CurrentPathStack $current_path,
    AliasManager $alias_manager
  ) {
    parent::__construct(
      $connection,
      $route_match
    );
    $this->cache = $cache;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentPath = $current_path;
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritDoc}
   */
  public function getDepartmentPages() {
    static $department_pages = NULL;

    // We cache the nid-tid relationship for a single page request.
    if (!isset($department_pages)) {
      $query = $this->database->select('node__field_category_slug', 'nfcs');
      $query->addField('nfcs', 'field_category_slug_value', 'tid');
      $query->addField('nfcs', 'entity_id', 'nid');
      $department_pages = $query->execute()->fetchAllKeyed();
    }

    return $department_pages;
  }

  /**
   * {@inheritDoc}
   */
  public function getDepartmentPageNode() {
    $path = $this->currentPath->getPath();
    $path = $this->aliasManager->getAliasByPath($path);
    // With V2 we use slug and not not term reference so we need the original
    // path (example: shop-kids) and not internal one (taxonomy/term/[tid]).
    // For this RCS provides a way to get original path if it had processed
    // and converted the value available in $path. We use it to get the
    // original path and check from slug.
    $filtered_path = RcsPhPathProcessor::getOrignalPathFromProcessed($path);
    preg_match('/^\/(.*)\/$/', $filtered_path, $matches);
    $filtered_path = $matches[1] ?? '';

    if (empty($filtered_path)) {
      return FALSE;
    }

    $data = [];
    // Check for cache first.
    $cache = $this->cache->get('alshaya_rcs_main_menu:slug:nodes');
    if ($cache) {
      $data = $cache->data;
      // If cache hit.
      if (!empty($data[$path])) {
        return $data[$path];
      }
    }

    // Get all department pages.
    $department_pages = $this->getDepartmentPages();
    // If there is department page available for given term.
    if (isset($department_pages[$filtered_path])) {
      $nid = $department_pages[$filtered_path];
      /** @var \Drupal\node\Entity\Node $node */
      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      if (is_object($node)) {
        if ($node->isPublished()) {
          $data[$filtered_path] = $nid;
          $this->cache->set('alshaya_rcs_main_menu:slug:nodes', $data, Cache::PERMANENT, $node->getCacheTags());
          return $nid;
        }
      }
    }

    return FALSE;
  }

  /**
   * Check if current page is a department page.
   *
   * @return int|bool
   *   Return nid if department page else FALSE.
   */
  public function isDepartmentPage() {
    return $this->getDepartmentPageNode();
  }

}
