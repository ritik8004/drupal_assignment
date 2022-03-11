<?php

namespace Drupal\alshaya_rcs_main_menu\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Entity\EntityInterface;

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
   */
  public function processProductCategoryMigration() {
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

      $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
      $delete_acq_terms = [];
      $acq_term_mapping = [];
      foreach ($terms as $term) {
        // Load the product category term object.
        $acq_term_data = $term_storage->load($term->tid);
        if ($acq_term_data instanceof EntityInterface) {
          $rcs_term = $this->createRcsCategory($acq_term_data);
          // Create parent terms.
          if (!empty($acq_term_data->parent->getString())) {
            $pid = $this->createParentRcsCategory($acq_term_data->parent->getString(), $acq_term_mapping);
            if ($pid) {
              $rcs_term->set('parent', $pid);
            }
          }

          $delete_acq_terms[$term->tid] = $acq_term_data;
          $rcs_term->save();
          // Save term mapping to get rcs parent terms.
          $acq_term_mapping[$term->tid] = $rcs_term->id();
        }
        else {
          throw new \Exception('Product category term not found.');
        }
      }
      // Delete ACM category items.
      $term_storage->delete($delete_acq_terms);
    }
  }

  /**
   * Create Parent RCS Category terms.
   *
   * @param int $tid
   *   Parent product category term id.
   * @param array $acq_term_mapping
   *   Mapping between ACM and RCS ids.
   *
   * @return string
   *   Returns RCS Category parent term id.
   */
  private function createParentRcsCategory($tid, array &$acq_term_mapping) {
    // Already saved so return parent tid.
    if (!empty($acq_term_mapping[$tid])) {
      return $acq_term_mapping[$tid];
    }
    $acq_term_data = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);

    // Recursively create parent term.
    if (!empty($acq_term_data->parent->getString())) {
      $pid = $this->createParentRcsCategory($acq_term_data->parent->getString(), $acq_term_mapping);
    }

    $rcs_term = $this->createRcsCategory($acq_term_data);
    if ($pid) {
      $rcs_term->set('parent', $pid);
    }
    $rcs_term->save();
    // Save parent term in mapping.
    $acq_term_mapping[$tid] = $rcs_term->id();
    return $rcs_term->id();
  }

  /**
   * Create RCS Category terms.
   *
   * @param object $acq_term_data
   *   Product category result set object.
   *
   * @return TermInterface
   *   Returns RCS Category term.
   */
  private function createRcsCategory($acq_term_data) {
    // Get the current language.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $paragraph_storage = $this->entityTypeManager->getStorage('paragraph');

    // Create a new rcs category term object.
    $rcs_term = $term_storage->create([
      'vid' => self::TARGET_VOCABULARY_ID,
      'name' => $acq_term_data->name,
      'langcode' => $langcode,
    ]);
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
    $term_slug = $this->aliasManager->getAliasByPath('/taxonomy/term/' . $acq_term->tid);
    $term_slug = ltrim($term_slug, '/');
    $rcs_term->get('field_category_slug')->setValue($term_slug);

    // Check if the translations exists for other available languages.
    foreach ($this->languageManager->getLanguages() as $language_code => $language) {
      if ($language_code != $langcode && $acq_term_data->hasTranslation($language_code)) {
        // Load the translation object.
        $acq_term_data = $acq_term_data->getTranslation($language_code);

        // Add translation in the new term.
        $rcs_term = $rcs_term->addTranslation($language_code, ['name' => $acq_term_data->name]);

        $rcs_term->get('field_term_background_color')
          ->setValue($acq_term_data->get('field_term_background_color')->getValue());

        $rcs_term->get('field_term_font_color')
          ->setValue($acq_term_data->get('field_term_font_color')->getValue());

        // Save the translations.
        $rcs_term->save();
      }
    }

    // Save the new term depth.
    $rcs_term->depth = $acq_term_data->depth_level->getString();
    return $rcs_term;
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
