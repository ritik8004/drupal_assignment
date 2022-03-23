<?php

namespace Drupal\alshaya_rcs_main_menu\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Service provides data migration functions in rcs_category taxonomy.
 *
 * Service is responsible to migrate the category data
 * from acq_product_category.
 */
class AlshayaRcsCategoryDataMigration {

  // Source and Target Vocabulary.
  const TARGET_VOCABULARY_ID = 'rcs_category';
  const SOURCE_VOCABULARY_ID = 'acq_product_category';

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
   * @var \Drupal\Core\Path\AliasManagerInterface
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
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
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
   * @param int $batch_size
   *   Limits the number of rcs category processed per batch.
   */
  public function processProductCategoryMigration(int $batch_size) {
    // Get the current language.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $query = $this->connection->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['tid', 'name']);

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
    $terms = $query->distinct()->execute()->fetchAll();
    // Do not process if no terms are found.
    if (!empty($terms)) {
      // Set batch operations to migrate terms.
      $operations = [];
      foreach (array_chunk($terms, $batch_size) as $term_chunk) {
        $operations[] = [
          [__CLASS__, 'batchProcess'],
          [$term_chunk, $batch_size],
        ];
      }
      $batch = [
        'title' => dt('Migrating enriched Product Category'),
        'init_message' => dt('Starting processing rcs category...'),
        'operations' => $operations,
        'error_message' => dt('Unexpected error while migrating enriched ACM Categories.'),
        'finished' => [__CLASS__, 'batchFinished'],
      ];
      batch_set($batch);
    }
  }

  /**
   * Batch operation to create rcs category terms.
   *
   * @param array $terms
   *   Current Chunk of terms to be processed.
   * @param int $batch_size
   *   Batch size.
   * @param mixed|array $context
   *   The batch current context.
   */
  public static function batchProcess(array $terms, int $batch_size, &$context) {
    // Initialize progess counter to zero.
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
    }
    // Store Product category and RCS category term mapping to get parent terms.
    if (!isset($context['results']['acq_term_mapping'])) {
      $context['results']['acq_term_mapping'] = [];
      $context['results']['batch_size'] = $batch_size;
    }

    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $language_manager = \Drupal::service('language_manager');
    $langcode = $language_manager->getCurrentLanguage()->getId();
    foreach ($terms as $term) {
      // Load the product category term object.
      $acq_term_data = $term_storage->load($term->tid);
      $acq_term_data = ($acq_term_data->language()->getId() == $langcode) ? $acq_term_data : $acq_term_data->getTranslation($langcode);
      if ($acq_term_data instanceof TermInterface) {
        $rcs_term = self::createRcsCategory($acq_term_data, $langcode);
        // Create parent terms.
        if (!empty($acq_term_data->parent->getString())) {
          $pid = self::createParentRcsCategory($acq_term_data->parent->getString(), $context['results']['acq_term_mapping'], $langcode);
          if ($pid) {
            $rcs_term->set('parent', $pid);
          }
        }

        $rcs_term->save();
        // Save term mapping to get rcs parent terms.
        $context['results']['acq_term_mapping'][$term->tid] = $rcs_term->id();
        $context['results']['delete_acq_terms'][$acq_term_data->id()] = $acq_term_data;
        $context['sandbox']['progress']++;
      }
      else {
        throw new \Exception('Product category term not found.');
      }
    }

    $context['message'] = dt('Processed @progress RCS Category terms.', ['@progress' => $context['sandbox']['progress']]);
  }

  /**
   * Deletes Product category terms after migration is complete.
   *
   * @param bool $success
   *   Indicate that the batch API tasks were all completed successfully.
   * @param array $results
   *   An array of all the results that were updated in operations.
   * @param array $operations
   *   A list of all the operations that had not been completed by batch API.
   */
  public static function batchFinished($success, array $results, array $operations) {
    $logger = \Drupal::logger('alshaya_rcs_category');
    if ($success) {
      // Delete product category terms that have been migrated.
      $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
      foreach (array_chunk($results['delete_acq_terms'], $results['batch_size']) as $acq_delete_terms) {
        $term_storage->delete($acq_delete_terms);
        $logger->notice('@count acq product categories deleted successfully.', ['@count' => count($acq_delete_terms)]);
      }
    }
  }

  /**
   * Create Parent RCS Category terms.
   *
   * @param int $tid
   *   Parent product category term id.
   * @param array $acq_term_mapping
   *   Mapping between ACM and RCS ids.
   * @param string $langcode
   *   Default language code.
   *
   * @return string
   *   Returns RCS Category parent term id.
   */
  private static function createParentRcsCategory(int $tid, array &$acq_term_mapping, string $langcode) {
    // Already saved so return parent rcs category tid.
    if (!empty($acq_term_mapping[$tid])) {
      return $acq_term_mapping[$tid];
    }
    // Load parent product category.
    $acq_parent_term_data = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    $acq_parent_term_data = ($acq_parent_term_data->language()->getId() == $langcode) ? $acq_parent_term_data : $acq_parent_term_data->getTranslation($langcode);

    // Recursively create parent term.
    if (!empty($acq_parent_term_data->parent->getString())) {
      $pid = self::createParentRcsCategory($acq_parent_term_data->parent->getString(), $acq_term_mapping, $langcode);
    }

    $rcs_parent_term = self::createRcsCategory($acq_parent_term_data, $langcode);
    if ($pid) {
      $rcs_parent_term->set('parent', $pid);
    }
    $rcs_parent_term->save();
    // Save parent term in mapping.
    $acq_term_mapping[$tid] = $rcs_parent_term->id();
    return $rcs_parent_term->id();
  }

  /**
   * Create RCS Category terms.
   *
   * @param \Drupal\taxonomy\TermInterface $acq_term_data
   *   Product category result set object.
   * @param string $langcode
   *   Default language code.
   *
   * @return \Drupal\taxonomy\TermInterface
   *   Returns RCS Category term.
   */
  private static function createRcsCategory(TermInterface $acq_term_data, string $langcode) {
    // Get the current language.
    $language_manager = \Drupal::service('language_manager');
    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

    // Create a new rcs category term object.
    $rcs_term = $term_storage->create([
      'vid' => self::TARGET_VOCABULARY_ID,
      'name' => $acq_term_data->name,
      'langcode' => $langcode,
    ]);

    // Copy enriched values from product category term.
    self::enrichRcsTerm($acq_term_data, $rcs_term);

    // Check if the translations exists for other available languages.
    foreach ($language_manager->getLanguages() as $language_code => $language) {
      if ($language_code != $langcode && $acq_term_data->hasTranslation($language_code)) {
        // Load the translation object.
        $acq_term_data_trans = $acq_term_data->getTranslation($language_code);

        // Add translation in the new term.
        $rcs_term_trans = $rcs_term->addTranslation($language_code, ['name' => $acq_term_data_trans->name]);
        self::enrichRcsTerm($acq_term_data_trans, $rcs_term_trans);

        // Save the translations.
        $rcs_term_trans->save();
      }
    }

    // Save the new term depth.
    $rcs_term->depth = $acq_term_data->depth_level->getString();
    return $rcs_term;
  }

  /**
   * Enriches the rcs category term fields.
   *
   * @param \Drupal\taxonomy\TermInterface $acq_term_data
   *   Product category term.
   * @param \Drupal\taxonomy\TermInterface $rcs_term
   *   RCS category term.
   */
  private static function enrichRcsTerm(TermInterface $acq_term_data, TermInterface &$rcs_term) {
    $paragraph_storage = \Drupal::entityTypeManager()->getStorage('paragraph');
    $alias_manager = \Drupal::service('path_alias.manager');
    // Add include_in_desktop field value from the old term.
    $rcs_term->get('field_include_in_desktop')
      ->setValue($acq_term_data->get('field_include_in_desktop')->getValue());

    // Add include_in_mobile_tablet field value from the old term.
    $rcs_term->get('field_include_in_mobile_tablet')
      ->setValue($acq_term_data->get('field_include_in_mobile_tablet')->getValue());

    // Add term_background_color field value from the old term.
    $rcs_term->get('field_term_background_color')
      ->setValue($acq_term_data->get('field_term_background_color')->getValue());

    // Add term_font_color field value from the old term.
    $rcs_term->get('field_term_font_color')
      ->setValue($acq_term_data->get('field_term_font_color')->getValue());

    // Add icon field value from the old term.
    $rcs_term->get('field_icon')
      ->setValue($acq_term_data->get('field_icon')->getValue());
    // Add main_menu_highlight field value from the old term.
    $main_menu_highlights = $acq_term_data->get('field_main_menu_highlight')->getValue();
    if (!empty($main_menu_highlights)) {
      $rcs_term_paragraphs = [];
      foreach ($main_menu_highlights as $highlight) {
        // Load source paragraph entity.
        $source_paragraphs = $paragraph_storage->load($highlight['target_id']);

        // Create a duplicate of the sourced entity.
        $cloned_paragraphs = $source_paragraphs->createDuplicate();

        // Save the new paragraph entity.
        $cloned_paragraphs->save();

        // Add target and revision id to value array for the rcs term.
        $rcs_term_paragraphs[] = [
          'target_id' => $cloned_paragraphs->id(),
          'target_revision_id' => $cloned_paragraphs->getRevisionId(),
        ];
      }

      // Attach highlights with rcs term if available.
      if (!empty($rcs_term_paragraphs)) {
        $rcs_term->get('field_main_menu_highlight')
          ->setValue($rcs_term_paragraphs);
      }
    }

    // Add move_to_right field value from the old term.
    $rcs_term->get('field_move_to_right')
      ->setValue($acq_term_data->get('field_move_to_right')->getValue());

    // Add override_target_link field value from the old term.
    $override_target_link = $acq_term_data->get('field_override_target_link')->getString();
    $rcs_term->get('field_override_target_link')->setValue($override_target_link);

    // Add remove_term_in_breadcrumb field value from the old term.
    $remove_term_breadcrumb = $acq_term_data->get('field_remove_term_in_breadcrumb')->getString();
    $rcs_term->get('field_remove_term_in_breadcrumb')->setValue($remove_term_breadcrumb);

    // Add display_as_clickable_link field value from the old term.
    $display_as_clickable = $acq_term_data->get('field_display_as_clickable_link')->getString();
    $rcs_term->get('field_display_as_clickable_link')->setValue($display_as_clickable);

    // Add target_link field value from the old term,
    // override_target_link field flag is true.
    if ($override_target_link == "1") {
      $rcs_term->get('field_target_link')
        ->setValue($acq_term_data->get('field_target_link')->getValue());
    }

    // Add category_slug field value from the old term path alias.
    $term_slug = $alias_manager->getAliasByPath('/taxonomy/term/' . $acq_term->tid);
    $term_slug = ltrim($term_slug, '/');
    $rcs_term->get('field_category_slug')->setValue($term_slug);
  }

  /**
   * Rollback term data migration from acq_product_category taxonomy.
   */
  public function rollbackProductCategoryMigration() {
    // Get the placeholder term from config.
    $config = $this->configFactory->get('rcs_placeholders.settings');
    $entity_id = $config->get('category.placeholder_tid');

    // Get all the terms from rcs_category taxonomy.
    $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', self::TARGET_VOCABULARY_ID);
    $query->condition('tid', $entity_id, '<>');
    $terms = $query->execute();

    // Return if none available.
    if (empty($terms)) {
      return NULL;
    }
    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $terms = $term_storage->loadMultiple($terms);
    $term_storage->delete($terms);
  }

}
