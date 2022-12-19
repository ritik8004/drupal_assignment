<?php

namespace Drupal\alshaya_rcs_super_category\Service;

use Drupal\Core\Site\Settings;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\node\NodeInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Drupal\file\Entity\File;

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
   * The file_system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

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
    AlshayaApiWrapper $api_wrapper,
    FileSystemInterface $file_system
  ) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->logger = $logger_factory->get('alshaya_rcs_super_category');
    $this->apiWrapper = $api_wrapper;
    $this->fileSystem = $file_system;
  }

  /**
   * Syncs L1 categories from Mdc backend.
   *
   * @param boolean $force_download
   *   Force download logos.
   */
  public function syncSuperCategories($force_download) {
    foreach ($this->languageManager->getLanguages() as $language_code => $language) {
      $this->logger->notice('Synchronizing super categories for language @language.', [
        '@language' => $language_code,
      ]);

      $this->languageManager->setConfigOverrideLanguage($language);

      // Fetch categories from mdc.
      $super_categories = $this->apiWrapper->getCategories($language_code);
      if (empty($super_categories)) {
        $this->logger->notice('No categories available in API response for language @language.', [
          '@language' => $language_code,
        ]);

        continue;
      }

      $super_categories = $this->processSuperCategories($super_categories['children_data']);

      // Fetch existing L1 rcs categories.
      $existing_super_categories = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('rcs_category',0, 1, TRUE);

      // Delete orphan terms.
      $this->deleteOrphansCategories($super_categories, $existing_super_categories);

      // Sync categories.
      $this->synchronizeCategories($super_categories, $existing_super_categories, $language_code, $force_download);
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
      if ($slug
        && count(explode('/', trim($slug, '/'))) < 2
        && !in_array($slug, array_keys($super_categories))
        && !in_array($slug . '/', array_keys($super_categories))) {
        // @todo delete the advanced page too if available.
        $category->delete();

        $this->logger->warning('Category with id @id and slug @slug deleted as no longer available in API response.', [
          '@id' => $category->id(),
          '@slug' => $slug,
        ]);
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
   * @param boolean $force_download
   *   Force download logos.
   */
  protected function synchronizeCategories($super_categories, $existing_super_categories, $lang_code, $force_download) {
    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');

    // Map existing categories with their slug.
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

      if (empty($existing_category)) {
        $term = $term_storage->create([
          'vid' => 'rcs_category',
          'name' => $category['name'],
          'langcode' => $lang_code,
        ]);
      }
      else {
        if (!$existing_category->hasTranslation($lang_code)) {
          $term = $existing_category->addTranslation($lang_code, [
            'vid' => 'rcs_category',
            'name' => $category['name'],
            'langcode' => $lang_code,
          ]);
        }
        else {
          $term = $existing_category->getTranslation($lang_code);
        }
      }

      // Fetch super category logos and save them.
      $this->setLogoImages($term, $category, $force_download);

      $term->get('field_category_slug')->setValue($category['url_path']);
      $term->get('field_commerce_id')->setValue($category['id']);
      $term->get('weight')->setValue($category['position']);
      $term->save();

      // Create / Update Advanced pages.
      $url_keys = [$category['url_path'], $category['url_path'] . '/'];
      $node_storage = $this->entityTypeManager->getStorage('node');
      $query = $node_storage->getQuery();
      $query->condition('type', 'advanced_page');
      $query->condition('field_category_slug', $url_keys, 'IN');
      $nodes = $query->execute();
      if (empty($nodes)) {
        $node = $node_storage->create([
          'title' => $category['name'],
          'type' => 'advanced_page',
          'langcode' => $lang_code,
        ]);

        $node->setPublished();
        $node->set('moderation_state', 'published');
        $node->setPromoted(NodeInterface::NOT_PROMOTED);
      }
      else {
        $node = $node_storage->load(current($nodes));
        if ($node instanceof NodeInterface) {
          if ($node->hasTranslation($lang_code)) {
            $node = $node->getTranslation($lang_code);
          }
          else {
            $node = $node->addTranslation($lang_code, [
              'title' => $category['name'],
              'type' => 'advanced_page',
              'langcode' => $lang_code,
            ]);

            $node->setPublished();
            $node->set('moderation_state', 'published');
            $node->setPromoted(NodeInterface::NOT_PROMOTED);
          }
        }
      }

      $node->get('field_use_as_department_page')->setValue(TRUE);
      $node->get('field_category_slug')->setValue($category['url_path']);
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
   * Process MDC categories to add required attributes.
   *
   * @param array $categories
   *   Categories to process.
   *
   * @return array
   *   Processed category data.
   */
  protected function processSuperCategories(array $categories): array {
    $processed_categories = [];
    foreach ($categories as $category) {
      $category['url_path'] = $this->getCustomAttribute($category, 'url_path');
      $category['field_logo_active_image'] = $this->getCustomAttribute($category, 'field_logo_active_image');
      $category['field_logo_inactive_image'] = $this->getCustomAttribute($category, 'field_logo_inactive_image');
      $category['field_logo_header_image'] = $this->getCustomAttribute($category, 'field_logo_header_image');
      $processed_categories[$category['url_path']] = $category;
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

  /**
   * Sets Logo images for rcs categories.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Rcs Category term.
   * @param array $category
   *   Mdc category.
   * @param boolean $force_download
   *   Force download logos.
   */
  protected function setLogoImages(&$term, $category, $force_download) {
    $mdc_url = $this->configFactory->get('alshaya_api.settings')->get('magento_host');
    $mdc_url = rtrim($mdc_url, '/') . '/';

    $directory = $this->configFactory->get('system.file')->get('default_scheme') . '://category-images';
    $this->fileSystem->prepareDirectory(
      $directory,
      FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
    );

    $field_arr = [
      'field_logo_active_image',
      'field_logo_header_image',
      'field_logo_inactive_image',
    ];

    //Retreive logo images for each field and save it.
    foreach ($field_arr as $field_name) {
      $file_url = !empty($category[$field_name])
        ? $mdc_url . ltrim($category[$field_name], '/')
        : NULL;
      $fid = !empty($term->get($field_name)->getValue())
        ? $term->get($field_name)->getValue()[0]['target_id']
        : NULL;
      // Do not download logos again if aleady present.
      if (!$force_download && $fid) {
        $file = File::load($fid);
        if ($file instanceof FileInterface
          && $file->getFilename() === basename($file_url)
        ) {
          continue;
        }
      }
      // Delete old file entity.
      if ($fid) {
        $file = File::load($fid);
        if ($file instanceof FileInterface) {
          $file->delete();
          $term->get($field_name)->setValue(NULL);
        }
      }
      // Retreive file from mdc url.
      if ($file_url) {
        $file = system_retrieve_file($file_url, $directory, TRUE, FileSystemInterface::EXISTS_RENAME);
        if ($file instanceof FileInterface) {
          $term->get($field_name)->setValue($file->id());
        }
      }
    }
  }

}
