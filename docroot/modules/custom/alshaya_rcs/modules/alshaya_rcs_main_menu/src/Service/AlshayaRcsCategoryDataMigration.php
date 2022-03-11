<?php

namespace Drupal\alshaya_rcs_main_menu\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Queue\QueueFactory;

/**
 * Service provides data migration functions in rcs_category taxonomy.
 *
 * Service is responsible to migrate the category data
 * from acq_product_category.
 */
class AlshayaRcsCategoryDataMigration {

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
   * The queue service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

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
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $language_manager,
                              Connection $connection,
                              QueueFactory $queue_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->connection = $connection;
    $this->queueFactory = $queue_factory;
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
      // Instantiate queue worker to create rcs category terms.
      $queue = $this->queueFactory->get('process_rcs_category');
      foreach ($terms as $term) {
        $queue->createItem($term);
      }
    }
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
