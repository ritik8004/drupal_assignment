<?php

namespace Drupal\alshaya_rcs_main_menu\Service;

use Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Service provides helper functions for the rcs category taxonomy.
 */
class AlshayaRcsCategoryHelper {

  public const VOCABULARY_ID = 'rcs_category';

  /**
   * Prefix used for the endpoint.
   */
  public const ENDPOINT_PREFIX_V1 = '/rest/v1/';

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Cache Backend object for "cache.data".
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Department page helper.
   *
   * @var \Drupal\alshaya_rcs_listing\Service\AlshayaRcsListingDepartmentPagesHelper
   */
  protected $departmentPageHelper;

  /**
   * An event dispatcher instance to use for configuration events.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Constructs a new AlshayaRcsCategoryHelper instance.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend object.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher.
   */
  public function __construct(LanguageManagerInterface $language_manager,
                              AliasManagerInterface $alias_manager,
                              CacheBackendInterface $cache,
                              Connection $connection,
                              ModuleHandlerInterface $module_handler,
                              EventDispatcherInterface $event_dispatcher) {
    $this->aliasManager = $alias_manager;
    $this->cache = $cache;
    $this->connection = $connection;
    $this->moduleHandler = $module_handler;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * Sets optional dependency on department pager helper service.
   *
   * @param \Drupal\alshaya_advanced_page\Service\AlshayaDepartmentPageHelper $department_pages_helper
   *   Department pages helper.
   */
  public function setDepartmentHelper(AlshayaDepartmentPageHelper $department_pages_helper) {
    $this->departmentPageHelper = $department_pages_helper;
  }

  /**
   * Get Deep link based on give object.
   *
   * @param object $object
   *   Object of term containing term data.
   *
   * @return string
   *   Return deeplink url.
   */
  public function getDeepLink($object) {
    $slug = $object->get('field_category_slug')->getString();
    // Get all the departments pages having category slug value.
    $department_pages = $this->departmentPageHelper->getDepartmentPages();
    // @todo Change the logic here once we get the prefixed response from
    // magento.
    if (array_key_exists($slug, $department_pages)) {
      return self::ENDPOINT_PREFIX_V1 . 'page/advanced?url=' .
      ltrim(
        $this->aliasManager->getAliasByPath(
          '/node/' . $department_pages[$slug],
          $this->languageManager->getCurrentLanguage()->getId(),
        ),
        '/'
      );
    }

    return '';
  }

  /**
   * Helper function to build the graphql query dynamically.
   *
   * @param int $depth
   *   Define the depth of the query.
   * @param bool $is_root_level
   *   Checks if depth is at root level.
   *
   * @return string
   *   The graphql query to fetch data using API.
   */
  public function getRcsCategoryMenuQuery($depth = 0, $is_root_level = TRUE) {
    $item_key = $is_root_level ? 'items' : 'children';
    $category_fields = [
      'id',
      'level',
      'name',
      'meta_title',
      'include_in_menu',
      'url_path',
      'url_key',
      'show_on_dpt',
      'show_in_lhn',
      'show_in_app_navigation',
      'position',
      'is_anchor',
      'display_view_all',
    ];

    $this->moduleHandler->alter('alshaya_rcs_category_query_fields', $category_fields, $depth);

    $query = [
      $item_key => $category_fields,
    ];
    if ($depth > 0) {
      $query[$item_key] = array_merge($query[$item_key], $this->getRcsCategoryMenuQuery($depth - 1, FALSE));
    }
    return $query;
  }

}
