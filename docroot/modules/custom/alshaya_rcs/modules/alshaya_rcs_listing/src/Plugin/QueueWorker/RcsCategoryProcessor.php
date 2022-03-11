<?php

namespace Drupal\alshaya_rcs_listing\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Process RCS Category term queue worker.
 *
 * @QueueWorker(
 *   id = "process_rcs_category",
 *   title = @Translation("Process RCS Category term"),
 * )
 */
class RcsCategoryProcessor extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  // Source and Target Vocabulary.
  const TARGET_VOCABULARY_ID = 'rcs_category';
  const SOURCE_VOCABULARY_ID = 'acq_product_category';

  /**
   * RcsCategoryProcessor constructor.
   *
   * @param array $configuration
   *   Plugin config.
   * @param string $plugin_id
   *   Plugin unique id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, AliasManagerInterface $alias_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->aliasManager = $alias_manager;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('path_alias.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Load the product category term object.
    $acq_term_data = $this->entityTypeManager->getStorage('taxonomy_term')->load($data->tid);
    if ($acq_term_data instanceof EntityInterface) {
      $rcs_term = $this->createRcsCategory($acq_term_data);
      // Create parent terms.
      if (!empty($acq_term_data->parent->getString())) {
        $pid = $this->createParentRcsCategory($acq_term_data->parent->getString());
        if ($pid) {
          $rcs_term->set('parent', $pid);
        }
      }

      // Delete the ACM Category item before creating the RCS Category.
      $acq_term_data->delete();
      $rcs_term->save();
    }
    else {
      throw new \Exception('Product category term not found.');
    }
  }

  /**
   * Create Parent RCS Category terms.
   *
   * @param int $tid
   *   Parent product category term id.
   *
   * @return string
   *   Returns RCS Category parent term id.
   */
  private function createParentRcsCategory($tid) {
    $acq_term_data = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);

    // Recursively create parent term.
    if (!empty($acq_term_data->parent->getString())) {
      $pid = $this->createParentRcsCategory($acq_term_data->parent->getString());
    }

    $rcs_term = $this->createRcsCategory($acq_term_data);
    if ($pid) {
      $rcs_term->set('parent', $pid);
    }
    $rcs_term->save();
    return $rcs_term->tid->getString();
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
  public function createRcsCategory($acq_term_data) {
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

}
