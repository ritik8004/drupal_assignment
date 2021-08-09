<?php

namespace Drupal\alshaya_rcs_main_menu\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Path\AliasManager;

/**
 * Service provides data migration functions in rcs_category taxonomy.
 *
 * Service is responsible to migrate the category data from other existing
 * taxonomies such as acq_product_category.
 */
class AlshayaRcsCategoryDataMigration {

  const VOCABULARY_ID = 'rcs_category';
  const VOCABULARY_FROM_ID = 'acq_product_category';

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
   * Drupal Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Rcs category term cache tags.
   *
   * @var array
   */
  protected $termCacheTags = [];

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Drupal path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManager
   */
  protected $pathAliasManager;

  /**
   * Constructs a new AlshayaRcsCategoryDataMigration instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Drupal Renderer.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection manager.
   * @param \Drupal\Core\Path\AliasManager $pathalias_manager
   *   The path alias manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              RendererInterface $renderer,
                              LanguageManagerInterface $language_manager,
                              Connection $connection,
                              AliasManager $pathalias_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
    $this->languageManager = $language_manager;
    $this->connection = $connection;
    $this->pathAliasManager = $pathalias_manager;
  }

  /**
   * Process term data migration from acq_product_category taxonomy.
   */
  public function processProductCategoryMigration() {
    // Get the current language.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $query = $this->connection->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['tid', 'name', 'description__value', 'langcode']);

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

    // Create a OR condition group, so if any of the above fields has
    // an overidden values, we need to fetch and clone them.
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
    $query->condition($orCondGroup);

    $query->condition('tfd.langcode', $langcode);
    $query->condition('tfd.vid', self::VOCABULARY_FROM_ID);

    // Get the terms statisfying the above conditions.
    $terms = $query->distinct()->execute()->fetchAll();

    // Do not process if no terms are found.
    if (!empty($terms)) {
      foreach ($terms as $pct) {
        // Load the product category term object.
        $pct_data = $this->entityTypeManager->getStorage('taxonomy_term')->load($pct->tid);

        // Create a new rcs category term object.
        $term_obj = $this->entityTypeManager->getStorage('taxonomy_term')->create([
          'vid' => self::VOCABULARY_ID,
          'name' => $pct->name,
          'langcode' => $langcode,
        ]);

        // Add include_in_desktop field value from the old term.
        $include_in_desktop = $pct_data->get('field_include_in_desktop')->getValue();
        $term_obj->get('field_include_in_desktop')->setValue($include_in_desktop);

        // Add include_in_mobile_tablet field value from the old term.
        $include_in_mobile = $pct_data->get('field_include_in_mobile_tablet')->getValue();
        $term_obj->get('field_include_in_mobile_tablet')->setValue($include_in_mobile);

        // Add term_background_color field value from the old term.
        $term_background_color = $pct_data->get('field_term_background_color')->getValue();
        $term_obj->get('field_term_background_color')->setValue($term_background_color);

        // Add term_font_color field value from the old term.
        $term_font_color = $pct_data->get('field_term_font_color')->getValue();
        $term_obj->get('field_term_font_color')->setValue($term_font_color);

        // Add icon field value from the old term.
        $icon_target = $pct_data->get('field_icon')->getValue();
        $term_obj->get('field_icon')->setValue($icon_target);

        // Add main_menu_highlight field value from the old term.
        $main_menu_highlight = $pct_data->get('field_main_menu_highlight')->getValue();
        $term_obj->get('field_main_menu_highlight')->setValue($main_menu_highlight);

        // Add move_to_right field value from the old term.
        $move_to_right = $pct_data->get('field_move_to_right')->getValue();
        $term_obj->get('field_move_to_right')->setValue($move_to_right);

        // Add override_target_link field value from the old term.
        $override_target_link = $pct_data->get('field_override_target_link')->getValue();
        $term_obj->get('field_override_target_link')->setValue($override_target_link);

        // Add target_link field value from the old term,
        // override_target_link field flag is true.
        if ($override_target_link == "1") {
          $target_link = $pct_data->get('field_target_link')->getValue();
          $term_obj->get('field_target_link')->setValue($target_link);
        }

        // Add category_slug field value from the old term path alias.
        $term_slug = $this->pathAliasManager->getAliasByPath('/taxonomy/term/' . $pct->tid);
        $term_obj->get('field_category_slug')->setValue($term_slug);

        // Save the new term object in rcs category.
        $term_obj->save();

        // Check if the translations exists for arabic language.
        if ($pct_data->hasTranslation($langcode)) {
          // Load the translation object.
          $pct_data = $pct_data->getTranslation('ar');

          // Add translation in the new term.
          $term_obj = $term_obj->addTranslation('ar', ['name' => $pct_data->name]);

          $term_background_color = $pct_data->get('field_term_background_color')->getValue();
          $term_obj->get('field_term_background_color')->setValue($term_background_color);

          $term_font_color = $pct_data->get('field_term_font_color')->getValue();
          $term_obj->get('field_term_font_color')->setValue($term_font_color);

          // Save the translations.
          $term_obj->save();
        }
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
    $query->condition('vid', self::VOCABULARY_ID);
    $query->condition('tid', $entity_id, '<>');
    $query->condition('langcode', $langcode);
    $terms = $query->execute();

    // Return if none available.
    if (empty($terms)) {
      return NULL;
    }

    foreach ($terms as $term_id) {
      $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id)->delete();
    }
  }

}
