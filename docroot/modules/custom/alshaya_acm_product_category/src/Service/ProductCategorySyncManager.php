<?php

namespace Drupal\alshaya_acm_product_category\Service;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Service to expose category sync related functions.
 */
class ProductCategorySyncManager {

  /**
   * Logger Channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Alshaya API Wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * ProductCategorySyncManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Alshaya API Wrapper.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Channel Factory service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              LanguageManagerInterface $language_manager,
                              ModuleHandlerInterface $module_handler,
                              AlshayaApiWrapper $api_wrapper,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->moduleHandler = $module_handler;
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('ProductCategorySyncManager');
  }

  /**
   * Removes the categories no longer available in Commerce system.
   */
  public function removeOrphanCategories() {
    $this->logger->notice('Removing the categories not available in Commerce system.');

    // We need to do this for one language only, we do it for system default.
    $categories = $this->apiWrapper->getCategories(
      $this->languageManager->getDefaultLanguage()->getId()
    );

    $existing_category_ids = $this->getCategoryIds($categories ?? []);
    if (empty($existing_category_ids)) {
      $this->logger->error('No categories returned by Commerce system.');
      return;
    }

    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');

    // Get the terms which are no longer available in Commerce system.
    $query = $term_storage->getQuery();
    $query->condition('field_commerce_id', $existing_category_ids, 'NOT IN');
    $query->condition('field_commerce_id', 0, '>');
    $orphan_categories = $query->execute();

    if (empty($orphan_categories)) {
      $this->logger->notice('No orphan categories found to delete.');
      return;
    }

    // If there are categories to delete allow other modules first
    // to skip the deleting of terms if they are used for something else.
    $this->moduleHandler->alter('acq_sku_sync_categories_delete', $orphan_categories);

    $this->logger->notice('Attempting to delete the orphan terms with ids: @ids.', [
      '@ids' => implode(',', $orphan_categories),
    ]);

    foreach ($orphan_categories as $tid) {
      $term = $term_storage->load($tid);

      if ($term instanceof TermInterface) {
        $term->delete();

        $this->logger->notice('Deleted orphan category with tid: @tid, commerce_id: @commerce_id, name: @name.', [
          '@tid' => $term->id(),
          '@commerce_id' => $term->get('field_commerce_id')->getString(),
          '@name' => $term->label(),
        ]);
      }
    }

    $this->logger->notice('Completed removing the categories not available in Commerce system.');
  }

  /**
   * Recursive function to get all category ids from the hierarchical response.
   *
   * @param array $category
   *   Category data (with possibly the children data).
   *
   * @return array
   *   Category ids.
   */
  private function getCategoryIds(array $category): array {
    $ids = [];
    $ids[] = $category['id'];
    foreach ($category['children_data'] ?? [] as $child) {
      $ids = array_merge($ids, $this->getCategoryIds($child));
    }
    return $ids;
  }

}
