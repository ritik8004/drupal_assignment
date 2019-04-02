<?php

namespace Drupal\alshaya_options_list;

use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Helper functions for alshaya_options_list.
 */
class AlshayaOptionsListService {

  /**
   * Database connection service object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;


  /**
   * File storage.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * AlshayaOptionsListService constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(Connection $connection,
                              LanguageManagerInterface $language_manager,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->connection = $connection;
    $this->languageManager = $language_manager;
    $this->fileStorage = $entity_type_manager->getStorage('file');
  }

  /**
   * Returns the build for options page.
   *
   * @param string $attributeCode
   *   Attribute code.
   * @param bool $showImages
   *   Whether images should be shown with the attribute.
   * @param bool $group
   *   Whether the attribute should be grouped alphabetically or not.
   * @param string $searchString
   *   Search string to match with name.
   *
   * @return array
   *   All term names array.
   */
  public function fetchAllTermsForAttribute($attributeCode, $showImages = FALSE, $group = FALSE, $searchString = '') {
    $return = [];
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $query = $this->connection->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['name', 'tid']);
    $query->innerJoin('taxonomy_term__field_sku_attribute_code', 'tfa', 'tfd.tid = tfa.entity_id');
    $query->condition('tfa.field_sku_attribute_code_value', $attributeCode);
    $query->condition('tfd.langcode', $langcode);
    if ($showImages) {
      $query->addField('tfs', 'field_attribute_swatch_image_target_id', 'image');
      $query->leftJoin('taxonomy_term__field_attribute_swatch_image', 'tfs', 'tfa.entity_id = tfs.entity_id');
    }
    if ($group) {
      $query->orderBy('tfd.name');
    }
    if (!empty($searchString)) {
      $query->condition('tfd.name', "%" . $this->connection->escapeLike($searchString) . "%", 'LIKE');
    }
    $options = $query->execute()->fetchAllAssoc('tid');
    foreach ($options as $option) {
      if (!empty($option->name)) {
        $list_object['title'] = $option->name;
        $url = [
          'query' => [
            'f[0]' => $attributeCode . ':' . $option->name,
            'sort_bef_combine' => 'search_api_relevance DESC',
            'show_on_load' => '12',
          ],
        ];
        $list_object['url'] = Url::fromUri('internal:/search', $url);
        if ($showImages) {
          $list_object['image_url'] = '';
          if (!empty($option->image)) {
            $file = $this->fileStorage->load($option->image);
            if ($file instanceof File) {
              $list_object['image_url'] = $file->getFileUri();
            }
          }
        }
        $return[] = $list_object;
      }
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function groupAlphabetically($options_array) {
    $return_array = [];
    foreach ($options_array as $option) {
      $char = strtolower($option['title'][0]);
      $return_array[$char][] = $option;
    }
    return $return_array;
  }

}
