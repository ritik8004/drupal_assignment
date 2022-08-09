<?php

namespace Drupal\alshaya_rcs_main_menu\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Service provides migration and rollback functions in rcs_category taxonomy.
 *
 * Service is responsible to migrate the category data.
 */
class AlshayaRcsCategoryDataMigration {

  // Source and Target Vocabulary.
  public const TARGET_VOCABULARY_ID = 'rcs_category';
  public const SOURCE_VOCABULARY_ID = 'acq_product_category';

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Alias manager service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs a new AlshayaRcsCategoryDataMigration instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection manager.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $language_manager,
                              Connection $connection,
                              AliasManagerInterface $alias_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->connection = $connection;
    $this->aliasManager = $alias_manager;
  }

  /**
   * Process term data migration from acq_product_category taxonomy.
   *
   * @param int|null $batch_size
   *   Limits the number of rcs category processed per batch.
   * @param bool $execute_batch
   *   Check if batch process can be executed.
   */
  public function processProductCategoryMigration($batch_size = 30, $execute_batch = TRUE) {
    // Get the current language.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $query = $this->connection->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['tid']);

    // For the `Term background color`.
    $query->leftJoin('taxonomy_term__field_term_background_color', 'ttbc', 'ttbc.entity_id = tfd.tid AND ttbc.langcode = tfd.langcode');
    // For the `Term font color`.
    $query->leftJoin('taxonomy_term__field_term_font_color', 'ttfc', 'ttfc.entity_id = tfd.tid AND ttfc.langcode = tfd.langcode');

    // For the `Term icon`.
    $query->leftJoin('taxonomy_term__field_icon', 'ttic', 'ttic.entity_id = tfd.tid');

    // For the `Include in desktop`.
    $query->leftJoin('taxonomy_term__field_include_in_desktop', 'in_desktop', 'in_desktop.entity_id = tfd.tid');

    // For the `Include in mobile`.
    $query->leftJoin('taxonomy_term__field_include_in_mobile_tablet', 'in_mobile', 'in_mobile.entity_id = tfd.tid');

    // For the `move to right`.
    $query->leftJoin('taxonomy_term__field_move_to_right', 'mtr', 'mtr.entity_id = tfd.tid');

    // For the `Overridden target link`.
    $query->leftJoin('taxonomy_term__field_target_link', 'tttl', 'tttl.entity_id = tfd.tid');

    // For the `Override target link flag`.
    $query->leftJoin('taxonomy_term__field_override_target_link', 'ttotl', 'ttotl.entity_id = tfd.tid');

    // For the `Highlights paragraphs`.
    $query->leftJoin('taxonomy_term__field_main_menu_highlight', 'ttmmh', 'ttmmh.entity_id = tfd.tid');

    // For the `Remove term in breadcrumb`.
    $query->leftJoin('taxonomy_term__field_remove_term_in_breadcrumb', 'ttrtb', 'ttrtb.entity_id = tfd.tid');

    // For the `Display as clickable link`.
    $query->leftJoin('taxonomy_term__field_display_as_clickable_link', 'ttdacl', 'ttdacl.entity_id = tfd.tid');

    // Create a OR condition group, so if any of the above fields has
    // an overridden values, we need to fetch and clone them.
    $orCondGroup = $query->orConditionGroup();
    $orCondGroup->isNotNull('ttbc.field_term_background_color_value');
    $orCondGroup->isNotNull('ttfc.field_term_font_color_value');
    $orCondGroup->isNotNull('ttic.field_icon_target_id');
    $orCondGroup->isNotNull('tttl.field_target_link_uri');
    $orCondGroup->isNotNull('ttmmh.field_main_menu_highlight_target_id');
    $orCondGroup->condition('in_desktop.field_include_in_desktop_value', '0');
    $orCondGroup->condition('in_mobile.field_include_in_mobile_tablet_value', '0');
    $orCondGroup->condition('mtr.field_move_to_right_value', '1');
    $orCondGroup->condition('ttotl.field_override_target_link_value', '1');
    $orCondGroup->condition('ttrtb.field_remove_term_in_breadcrumb_value', '1');
    $orCondGroup->condition('ttdacl.field_display_as_clickable_link_value', '0');
    $query->condition($orCondGroup);

    $query->condition('tfd.langcode', $langcode);
    $query->condition('tfd.vid', self::SOURCE_VOCABULARY_ID);

    // Get the terms satisfying the above conditions.
    $tids = $query->distinct()->execute()->fetchAllKeyed(0, 0);
    // Batch set in install hook.
    if (!$execute_batch) {
      return $tids;
    }

    // Do not process if no terms are found.
    if (!empty($tids)) {
      // Set batch operations to migrate terms.
      $operations = [];
      foreach (array_chunk($tids, $batch_size) as $tid_chunk) {
        $operations[] = [
          [self::class, 'batchProcess'],
          [$tid_chunk, $batch_size, self::TARGET_VOCABULARY_ID],
        ];
      }
      $batch = [
        'title' => dt('Migrating enriched Product Category'),
        'init_message' => dt('Starting processing rcs category...'),
        'operations' => $operations,
        'error_message' => dt('Unexpected error while migrating enriched ACM Categories.'),
        'finished' => [self::class, 'batchFinished'],
      ];
      batch_set($batch);
      drush_backend_batch_process();
    }
  }

  /**
   * Batch operation to migrate/rollback category terms.
   *
   * @param array $tids
   *   Batch of the source tids to be migrated.
   * @param int $batch_size
   *   Migrate batch size.
   * @param string $vid
   *   Vocabulary Id for the migrated term.
   * @param mixed|array $context
   *   Batch context.
   */
  public static function batchProcess(array $tids, int $batch_size, string $vid, &$context) {
    // Initialized term count to zero.
    if (empty($context['sandbox'])) {
      $context['sandbox']['term_count'] = 0;
    }

    // Store Product category and RCS category term mapping to get parent terms.
    if (empty($context['results']['acq_term_mapping'])) {
      $context['results']['acq_term_mapping'] = [];
      $context['results']['batch_size'] = $batch_size;
      // Get Commerce id from MDC for Product Category.
      if ($vid == self::SOURCE_VOCABULARY_ID) {
        $context['results']['commerce_ids'] = self::getCommerceIdMapping();
      }
    }

    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $language_manager = \Drupal::service('language_manager');
    $langcode = $language_manager->getCurrentLanguage()->getId();
    $path_alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
    // ACQ and RCS category vocabularies.
    $category_names = [
      self::SOURCE_VOCABULARY_ID => 'Product Category',
      self::TARGET_VOCABULARY_ID => 'RCS Category',
    ];

    foreach ($tids as $tid) {
      // Check if category term already exists.
      if (!empty($context['results']['acq_term_mapping'][$tid])) {
        continue;
      }

      $source_term = $term_storage->load($tid);
      if ($source_term instanceof TermInterface) {
        // Create new migrate category term.
        $migrate_term = self::createCategory($source_term, $langcode, $vid);
        // Create parent terms.
        if (!empty($source_term->parent->getString())) {
          $pid = self::createParentCategory($source_term->parent->getString(), $langcode, $vid, $context['results'], $context['sandbox']['term_count']);
          if ($pid) {
            $migrate_term->set('parent', $pid);
          }
        }
        // Delete source path alias so that there is no conflict.
        $aliases = $path_alias_storage->loadByProperties([
          'path' => '/taxonomy/term/' . $tid,
        ]);
        $path_alias_storage->delete($aliases);
        // Set commerce id if we are migrating product category.
        if ($vid == self::SOURCE_VOCABULARY_ID) {
          self::updateCommerceId($source_term, $migrate_term, $context['results']['commerce_ids']);
        }

        $migrate_term->save();
        // Set source/migrate term mapping.
        $context['results']['acq_term_mapping'][$tid] = $migrate_term->id();
        $context['results']['delete_terms'][$source_term->id()] = $source_term;
        $context['sandbox']['term_count']++;
      }
      else {
        throw new \Exception($category_names[$vid] . ' term not found.');
      }
    }
    $context['message'] = dt('Processed @term_count @category_name terms.', [
      '@term_count' => $context['sandbox']['term_count'],
      '@category_name' => $category_names[$vid],
    ]);
  }

  /**
   * Deletes source category terms after migration is complete.
   *
   * @param bool $success
   *   Indicate that the batch API tasks were all completed successfully.
   * @param array $results
   *   An array of all the results that were updated in operations.
   * @param array $operations
   *   A list of all the operations that had not been completed by batch API.
   */
  public static function batchFinished($success, array $results, array $operations) {
    if ($success) {
      $logger = \Drupal::logger('alshaya_rcs_category');
      // Delete product category terms that have been migrated.
      $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
      foreach (array_chunk($results['delete_terms'], $results['batch_size']) as $source_terms) {
        $term_storage->delete($source_terms);
        $logger->notice('@count source categories deleted successfully.', ['@count' => count($source_terms)]);
      }
    }
  }

  /**
   * Create Parent Category terms.
   *
   * @param int $tid
   *   Source category term id.
   * @param string $langcode
   *   Default language code.
   * @param string $vid
   *   Vocabulary Id for the term migrated.
   * @param array|null $results
   *   Batch Context results.
   * @param int|null $term_count
   *   Migrated Term count.
   *
   * @return string
   *   Returns migrated parent term id.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private static function createParentCategory(int $tid, string $langcode, string $vid, array &$results = NULL, int &$term_count = NULL) {
    $pid = NULL;
    // Already saved so return parent category tid.
    if (!empty($results['acq_term_mapping'][$tid])) {
      return $results['acq_term_mapping'][$tid];
    }

    // Load parent product category.
    $source_parent_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    $source_parent_term = ($source_parent_term->language()->getId() == $langcode) ? $source_parent_term : $source_parent_term->getTranslation($langcode);

    // Recursively create parent term.
    if (!empty($source_parent_term->parent->getString())) {
      $pid = self::createParentCategory($source_parent_term->parent->getString(), $langcode, $vid, $results, $term_count);
    }

    $migrate_parent_term = self::createCategory($source_parent_term, $langcode, $vid);
    if ($pid) {
      $migrate_parent_term->set('parent', $pid);
    }

    // Delete aliases for source product category so there is no conflict.
    $path_alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
    $aliases = $path_alias_storage->loadByProperties([
      'path' => '/taxonomy/term/' . $tid,
    ]);
    $path_alias_storage->delete($aliases);

    // Set Commerce id for Product Category.
    if ($vid == self::SOURCE_VOCABULARY_ID) {
      self::updateCommerceId($source_parent_term, $migrate_parent_term, $results['commerce_ids']);
    }
    $migrate_parent_term->save();
    $term_count++;
    // Save parent term in mapping.
    $results['acq_term_mapping'][$tid] = $migrate_parent_term->id();
    $results['delete_terms'][$source_parent_term->id()] = $source_parent_term;
    return $migrate_parent_term->id();
  }

  /**
   * Creates Category terms for migration.
   *
   * @param \Drupal\taxonomy\TermInterface $source_term
   *   Source Term to be migrated.
   * @param string $langcode
   *   Default language code.
   * @param string $vid
   *   Vocabulary Id of the migrated term.
   *
   * @return \Drupal\taxonomy\TermInterface
   *   Returns Migrated term.
   */
  private static function createCategory(TermInterface $source_term, string $langcode, string $vid) {
    // Get the current language.
    $language_manager = \Drupal::service('language_manager');
    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

    // Create a category term object.
    $migrate_term = $term_storage->create([
      'vid' => $vid,
      'name' => $source_term->name,
      'langcode' => $langcode,
    ]);

    // Copy enriched values from source category term.
    self::enrichTerm($source_term, $migrate_term, $vid);

    // Check if the translations exists for other available languages.
    foreach ($language_manager->getLanguages() as $language_code => $language) {
      if ($language_code != $langcode && $source_term->hasTranslation($language_code)) {
        // Load the translation object.
        $source_term_trans = $source_term->getTranslation($language_code);

        // Add translation in the new term.
        $migrate_term_trans = $migrate_term->addTranslation($language_code, ['name' => $source_term_trans->name]);

        $migrate_term_trans->get('field_term_background_color')
          ->setValue($source_term_trans->get('field_term_background_color')->getValue());

        $migrate_term_trans->get('field_term_font_color')
          ->setValue($source_term_trans->get('field_term_font_color')->getValue());

        // Save the translations.
        $migrate_term_trans->save();
      }
    }

    // Save the new term depth.
    $migrate_term->depth = $source_term->depth_level->getString();
    return $migrate_term;
  }

  /**
   * Enriches the category term fields.
   *
   * @param \Drupal\taxonomy\TermInterface $source_term
   *   The source term.
   * @param \Drupal\taxonomy\TermInterface $migrate_term
   *   New migrate term.
   * @param string $vid
   *   Vocabulary Id of the Migrated term.
   */
  private static function enrichTerm(TermInterface $source_term, TermInterface &$migrate_term, string $vid) {
    $paragraph_storage = \Drupal::entityTypeManager()->getStorage('paragraph');
    $alias_manager = \Drupal::service('path_alias.manager');
    // Add include_in_desktop field value from the old term.
    $migrate_term->get('field_include_in_desktop')
      ->setValue($source_term->get('field_include_in_desktop')->getValue());

    // Add include_in_mobile_tablet field value from the old term.
    $migrate_term->get('field_include_in_mobile_tablet')
      ->setValue($source_term->get('field_include_in_mobile_tablet')->getValue());

    // Add term_background_color field value from the old term.
    $migrate_term->get('field_term_background_color')
      ->setValue($source_term->get('field_term_background_color')->getValue());

    // Add term_font_color field value from the old term.
    $migrate_term->get('field_term_font_color')
      ->setValue($source_term->get('field_term_font_color')->getValue());

    // Add icon field value from the old term.
    $migrate_term->get('field_icon')
      ->setValue($source_term->get('field_icon')->getValue());
    // Add main_menu_highlight field value from the old term.
    $main_menu_highlights = $source_term->get('field_main_menu_highlight')->getValue();
    if (!empty($main_menu_highlights)) {
      $migrate_paragraphs = [];
      foreach ($main_menu_highlights as $highlight) {
        // Load source paragraph entity.
        $source_paragraphs = $paragraph_storage->load($highlight['target_id']);

        // Create a duplicate of the sourced entity.
        $cloned_paragraphs = $source_paragraphs->createDuplicate();

        // Save the new paragraph entity.
        $cloned_paragraphs->save();

        // Add target and revision id to value array for the rcs term.
        $migrate_paragraphs[] = [
          'target_id' => $cloned_paragraphs->id(),
          'target_revision_id' => $cloned_paragraphs->getRevisionId(),
        ];
      }

      // Attach highlights migrate_paragraphs rcs term if available.
      if (!empty($migrate_paragraphs)) {
        $migrate_term->get('field_main_menu_highlight')
          ->setValue($migrate_paragraphs);
      }
    }

    // Add move_to_right field value from the old term.
    $migrate_term->get('field_move_to_right')
      ->setValue($source_term->get('field_move_to_right')->getValue());

    // Add override_target_link field value from the old term.
    $override_target_link = $source_term->get('field_override_target_link')->getString();
    $migrate_term->get('field_override_target_link')->setValue($override_target_link);

    // Add remove_term_in_breadcrumb field value from the old term.
    $remove_term_breadcrumb = $source_term->get('field_remove_term_in_breadcrumb')->getString();
    $migrate_term->get('field_remove_term_in_breadcrumb')->setValue($remove_term_breadcrumb);

    // Add display_as_clickable_link field value from the old term.
    $display_as_clickable = $source_term->get('field_display_as_clickable_link')->getString();
    $migrate_term->get('field_display_as_clickable_link')->setValue($display_as_clickable);

    // Add target_link field value from the old term,
    // override_target_link field flag is true.
    if ($override_target_link == "1") {
      $migrate_term->get('field_target_link')
        ->setValue($source_term->get('field_target_link')->getValue());
    }

    // Add category_slug field value from the old term path alias for rcs path.
    if ($vid == self::TARGET_VOCABULARY_ID) {
      $term_slug = $alias_manager->getAliasByPath('/taxonomy/term/' . $source_term->id());
      $term_slug = ltrim($term_slug, '/');
      $migrate_term->get('field_category_slug')->setValue($term_slug);
    }
  }

  /**
   * Rollback term data migration from acq_product_category taxonomy.
   *
   * @param int|null $batch_size
   *   Migrate Batch size.
   */
  public function rollbackProductCategoryMigration($batch_size = 50) {
    // Get the placeholder term from config.
    $config = $this->configFactory->get('rcs_placeholders.settings');
    $entity_id = $config->get('category.placeholder_tid');

    // Get all the terms from rcs_category taxonomy.
    $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', self::TARGET_VOCABULARY_ID);
    $query->condition('tid', $entity_id, '<>');
    $tids = $query->execute();

    // Batch to reinstate acq terms.
    if (!empty($tids)) {
      // Set batch operations to migrate terms.
      $operations = [];
      foreach (array_chunk($tids, $batch_size) as $tid_chunk) {
        $operations[] = [
          [self::class, 'batchProcess'],
          [$tid_chunk, $batch_size, self::SOURCE_VOCABULARY_ID],
        ];
      }
      $batch = [
        'title' => dt('Reinstate ACM Categories.'),
        'init_message' => dt('Starting processing acq category...'),
        'operations' => $operations,
        'error_message' => dt('Unexpected error while rollback of enriched ACM Categories.'),
        'finished' => [self::class, 'batchFinished'],
      ];
      batch_set($batch);
      drush_backend_batch_process();
    }

  }

  /**
   * Get commerce id mapping with the category url.
   *
   * @return array
   *   Mapping of category url with commerce id.
   *   e.g ['shop-women' => 20, 'shop-men/jeans' => 129]
   */
  private static function getCommerceIdMapping() {
    $language_manager = \Drupal::service('language_manager');
    $langcode = $language_manager->getCurrentLanguage()->getId();
    // Get Categories from MDC.
    $categories = \Drupal::service('alshaya_api.api')->getCategories($langcode);
    $commerce_ids = array_flip(self::getCategoryIdsMapping($categories));
    return $commerce_ids;
  }

  /**
   * Get commerce ids mapping with url recursively.
   *
   * @param array $category
   *   MDC Category list.
   *
   * @return array
   *   Return commerce id url mapping.
   */
  private static function getCategoryIdsMapping(array $category) {
    $ids = [];
    $url_path = '';
    // Get url path from custom attributes.
    foreach ($category['custom_attributes'] ?? [] as $attribute) {
      if ($attribute['attribute_code'] == 'url_path') {
        $url_path = $attribute['value'];
        break;
      }
    }
    if (!empty($url_path)) {
      $ids[$category['id']] = $url_path;
    }

    // Recursively get commerce id mappings.
    foreach ($category['children_data'] ?? [] as $child) {
      $ids = array_replace($ids, self::getCategoryIdsMapping($child));
    }
    return $ids;
  }

  /**
   * Set commerce id for product category.
   *
   * @param \Drupal\taxonomy\TermInterface $source_term
   *   The source term.
   * @param \Drupal\taxonomy\TermInterface $migrate_term
   *   New migrate term.
   * @param array|null $commerce_ids
   *   Mapping of category url with commerce id.
   */
  private static function updateCommerceId(TermInterface $source_term, TermInterface &$migrate_term, $commerce_ids) {
    $alias = $source_term->get('field_category_slug')->getString();
    // Get commerce id matching the category url slug.
    if (!empty($alias) && $commerce_ids[$alias]) {
      $commerce_id = $commerce_ids[$alias];
      $migrate_term->set('field_commerce_id', $commerce_id);
    }
    else {
      $logger = \Drupal::logger('alshaya_rcs_category');
      $logger->error('Error occured while updating commerce id for term: @label [@slug]', [
        '@label' => $source_term->label(),
        '@slug' => $alias,
      ]);
    }
  }

}
