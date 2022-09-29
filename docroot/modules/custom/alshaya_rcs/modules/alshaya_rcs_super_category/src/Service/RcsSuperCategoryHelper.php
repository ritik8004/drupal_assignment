<?php

namespace Drupal\alshaya_rcs_super_category\Service;

use Drupal\Core\Site\Settings;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\taxonomy\TermInterface;
use Drupal\node\NodeInterface;

/**
 * Rcs Super Category Helper.
 */
class RcsSuperCategoryHelper {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Alshaya Magento API Wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * RcsSuperCategoryHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger service.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Alshaya api wrapper.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    LoggerChannelFactoryInterface $logger_factory,
    AlshayaApiWrapper $api_wrapper
  ) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->logger = $logger_factory->get('alshaya_rcs_super_category');
    $this->apiWrapper = $api_wrapper;
  }
  /**
   * Syncs L1 categories from Mdc backend.
   */
  public function syncSuperCategories() {
    foreach ($this->languageManager->getLanguages() as $language_code => $language) {
      // Fetch categories from mdc.
      $super_categories = $this->apiWrapper->getCategories($language_code);
      if (!empty($super_categories)) {
        $super_categories = $this->processSuperCategories($super_categories['children_data']);
        // Fetch existing L1 rcs categories.
        $existing_super_categories = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('rcs_category',0, 1, TRUE);
        // Delete orphan terms.
        $this->deleteOrphansCategories($super_categories, $existing_super_categories);
        // Sync categories.
        $this->synchronizeCategories($super_categories, $existing_super_categories, $language_code);
      }
    }
  }

  /**
   * Deletes orphan super categories.
   *
   * @param array $super_categories
   *   Mdc categories.
   * @param array $existing_super_categories
   *   Level 1 rcs categories.
   */
  protected function deleteOrphansCategories($super_categories, $existing_super_categories) {
    $default_term_id = $this->configFactory->get('rcs_placeholders.settings')->get('category.placeholder_tid');
    foreach ($existing_super_categories as $category) {
      // Ignore placeholder taxonomy term.
      if ($category->id() === $default_term_id) {
        continue;
      }
      // Delete term if not found in mdc categories and is not level 2.
      $slug = $category->get('field_category_slug')->getString();
      if (count(explode('/', $slug)) < 2
        && !in_array($slug, array_keys($super_categories))
        && !in_array($slug . '/', array_keys($super_categories))
      ) {
        $category->delete();
      }
    }
  }

  /**
   * Creates and Updates Rcs Categories.
   *
   * @param array $super_categories
   *   Mdc categories.
   * @param array $existing_super_categories
   *   Existing rcs categories.
   * @param string $lang_code
   *   Language code.
   */
  protected function synchronizeCategories($super_categories, $existing_super_categories, $lang_code) {
    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    // Map existing cateogeries with their slug.
    $existing_super_categories = $this->processExistingCategories($existing_super_categories, $lang_code);
    foreach ($super_categories as $slug => $category) {
      // Check if rcs category with the slug already exists.
      $existing_category = NULL;
      if (!empty($existing_super_categories[$slug])) {
        $existing_category = $existing_super_categories[$slug];
      }
      elseif (!empty($existing_super_categories[$slug . '/'])) {
        $existing_category = $existing_super_categories[$slug . '/'];
      }

      if (!empty($existing_category)) {
        if ($existing_category->hasTranslation($lang_code)) {
          continue;
        }
        $term = $existing_category->addTranslation($lang_code, [
          'vid' => 'rcs_category',
          'name' => $category['name'],
          'langcode' => $lang_code,
        ]);
      }
      else {
        $term = $term_storage->create([
          'vid' => 'rcs_category',
          'name' => $category['name'],
          'langcode' => $lang_code,
        ]);
      }
      $term->get('field_category_slug')->setValue($category['url_key']);
      $term->get('field_mdc_category_id')->setValue($category['id']);
      $term->save();

      // Create Advanced pages.
      $url_keys = [$category['url_key'], $category['url_key'] . '/'];
      $node_storage = $this->entityTypeManager->getStorage('node');
      $query = $node_storage->getQuery();
      $query->condition('type', 'advanced_page');
      $query->condition('field_category_slug', $url_keys, 'IN');
      $nodes = $query->execute();
      if (empty($nodes)) {
        $node = $node_storage
          ->create(
            [
              'title' => $category['name'],
              'type' => 'advanced_page',
              'langcode' => $lang_code,
            ]
          );
      }
      else {
        $node = $node_storage->load(current($nodes));
        if ($node instanceof NodeInterface && $node->hasTranslation($lang_code)) {
          continue;
        }
        $node = $node->addTranslation($lang_code,
          [
            'title' => $category['name'],
            'type' => 'advanced_page',
            'langcode' => $lang_code,
          ]
        );
      }

      $node->get('field_use_as_department_page')->setValue(TRUE);
      $node->get('field_category_slug')->setValue($category['url_key']);
      $node->save();
    }
  }

  /**
   * Maps existing Rcs Categories with url slug.
   *
   * @param array $existing_super_categories
   *   Rcs Categories.
   * @param string $lang_code
   *   Language code.
   */
  protected function processExistingCategories($existing_super_categories, $lang_code) {
    $categories = [];
    foreach ($existing_super_categories as $category) {
      $categories[$category->get('field_category_slug')->getString()] = $category;
    }
    return $categories;
  }

  /**
   * Process mdc super category attributes.
   */
  protected function processSuperCategories($super_categories) {
    $processed_categories = [];
    foreach ($super_categories as $category) {
      $url_key = $this->getCustomAttribute($category, 'url_key');
      $processed_categories[$url_key] = [
        'name' => $category['name'],
        'url_key' => $url_key,
        'id' => $category['id'],
      ];
    }
    return $processed_categories;
  }

  /**
   * Gets custom attribute.
   *
   * @param array $category
   *   Mdc category.
   * @param string $attribute_code
   *   Attribute code to be fetched.
   *
   * @return string
   *   Custom attribute value.
   */
  protected function getCustomAttribute($category, $attribute_code) {
    $key = array_search($attribute_code, array_column($category['custom_attributes'], 'attribute_code'));
    return $category['custom_attributes'][$key]['value'];
  }

}
