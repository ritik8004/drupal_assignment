<?php

namespace Drupal\alshaya_rcs_listing\Service;

use Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
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
   */
  public function __construct(
    Connection $connection,
    RouteMatchInterface $route_match,
    CacheBackendInterface $cache,
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    parent::__construct(
      $connection,
      $route_match
    );
    $this->cache = $cache;
    $this->entityTypeManager = $entity_type_manager;
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
  public function getDepartmentPageNid($path = NULL) {
    $filtered_path = $path;
    if (!empty($filtered_path)) {
      $filtered_path = trim($filtered_path, '/');
    }
    else {
      $path = RcsPhPathProcessor::getFullPagePath();
      // With V3 we use slug and not not term reference so we need the original
      // path (example: shop-kids) and not internal one (taxonomy/term/[tid]).
      // For this RCS provides a way to get original path if it had processed
      // and converted the value available in $path. We use it to get the
      // original path and check from slug.
      $filtered_path = RcsPhPathProcessor::getOrignalPathFromProcessed($path);
      preg_match('/^\/(.*)\/$/', $filtered_path, $matches);
      $filtered_path = $matches[1] ?? '';
    }

    if (empty($filtered_path)) {
      return FALSE;
    }

    $data = [];
    $cid = 'alshaya_rcs_listing:slug:nids';
    // Check for cache first.
    $cache = $this->cache->get($cid);
    if ($cache) {
      $data = $cache->data;
      // If cache hit.
      if (!empty($data[$filtered_path])) {
        return $data[$filtered_path];
      }
    }

    // Get all department pages.
    $department_pages = $this->getDepartmentPages();
    // If there is department page available for given term.
    if (isset($department_pages[$filtered_path])) {
      $nid = $department_pages[$filtered_path];
      /** @var \Drupal\node\Entity\Node $node */
      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      if ($node instanceof NodeInterface && $node->isPublished()) {
        $data[$filtered_path] = $nid;
        $this->cache->set($cid, $data, Cache::PERMANENT, ['node_list:advanced_page']);
        return $nid;
      }
    }

    return FALSE;
  }

}
