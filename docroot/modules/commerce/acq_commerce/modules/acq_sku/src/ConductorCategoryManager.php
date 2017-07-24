<?php

namespace Drupal\acq_sku;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\acq_commerce\Conductor\ClientFactory;

/**
 * Provides a service for category data to taxonomy synchronization.
 *
 * @ingroup acq_sku
 */
class ConductorCategoryManager implements CategoryManagerInterface {

  /**
   * Taxonomy Term Entity Storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  private $termStorage;

  /**
   * Taxonomy Vocabulary Entity Storage.
   *
   * @var \Drupal\taxonomy\VocabularyStorageInterface
   */
  private $vocabStorage;

  /**
   * Taxonomy Vocabulary Entity to Sync.
   *
   * @var \Drupal\taxonomy\VocabularyInterface
   */
  private $vocabulary;

  /**
   * Result (create / update / failed) counts.
   *
   * @var array
   */
  private $results;

  /**
   * API Wrapper object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\acq_commerce\Conductor\ClientFactory $client_factory
   *   ClientFactory object.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   API Wrapper object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ClientFactory $client_factory, APIWrapper $api_wrapper, LoggerChannelFactory $logger_factory) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->vocabStorage = $entity_type_manager->getStorage('taxonomy_vocabulary');
    $this->clientFactory = $client_factory;
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('acq_sku');
  }

  /**
   * {@inheritdoc}
   */
  public function synchronizeTree($vocabulary, $remoteRoot = NULL) {
    $this->resetResults();
    $this->loadVocabulary($vocabulary);

    $config = \Drupal::config('acq_commerce.conductor');
    $debug = $config->get('debug');
    $debug_dir = $config->get('debug_dir');

    foreach (acq_commerce_get_store_language_mapping() as $langcode => $store_id) {
      if ($store_id) {
        // Load Conductor Category data.
        $categories = [$this->loadCategoryData($store_id)];

        if ($debug && !empty($debug_dir)) {
          // Export category data into file.
          $filename = $debug_dir . '/categories_' . $langcode . '.data';
          $fp = fopen($filename, 'w');
          fwrite($fp, var_export($categories, 1));
          fclose($fp);
        }

        // Recurse the category tree and create / update nodes.
        $this->syncCategory($categories, NULL);
      }
    }

    return ($this->results);
  }

  /**
   * Synchronize categories in offline mode, i.e. not connected to middleware.
   *
   * @param string $vocabulary
   *   Vocabulary machine name.
   * @param array $categories
   *   Category tree to import.
   *
   * @return array
   *   Array summarising updates.
   */
  public function synchronizeTreeOffline($vocabulary, array $categories) {

    $this->resetResults();
    $this->loadVocabulary($vocabulary);

    // Recurse the category tree and create / update nodes.
    $this->syncCategory($categories, NULL);

    return ($this->results);
  }

  /**
   * {@inheritdoc}
   */
  public function synchronizeCategory($vocabulary, array $categories) {
    return \Drupal::service('acq_commerce.api')->getCategories();
  }

  /**
   * LoadCategoryData.
   *
   * Load the commerce backend category data from Conductor.
   *
   * @param int $store_id
   *   Store id for which we should get categories.
   *
   * @return array
   *   Array of categories.
   */
  private function loadCategoryData($store_id) {
    $this->apiWrapper->updateStoreContext($store_id);
    return $this->apiWrapper->getCategories();
  }

  /**
   * LoadVocabulary.
   *
   * Load a taxonomy vocabulary from a vid.
   *
   * @param string $vocabulary
   *   Vocabulary VID.
   */
  private function loadVocabulary($vocabulary) {

    if (!strlen($vocabulary)) {
      throw new \InvalidArgumentException('CategoryManager requires a taxonomy vocabulary machine name.');
    }

    $vocab = $this->vocabStorage->load($vocabulary);

    if (!$vocab || !$vocab->id()) {
      throw new \InvalidArgumentException(sprintf(
        'CategoryManager unable to locate vocabulary %s.',
        $vocabulary
      ));
    }

    $this->vocabulary = $vocab;

  }

  /**
   * ResetResults.
   *
   * Reset the results counters.
   */
  private function resetResults() {
    $this->results = [
      'created' => 0,
      'updated' => 0,
      'failed'  => 0,
    ];
  }

  /**
   * SyncCategory.
   *
   * Recursive category synchronization and saving.
   *
   * @param array $categories
   *   Children Categories.
   * @param array|null $parent
   *   Parent Category.
   */
  private function syncCategory(array $categories, $parent = NULL) {

    // Remove top level item (Default Category) from the categories, if its set
    // in configuration and category is with no parent.
    $filter_root_category = \Drupal::config('acq_commerce.conductor')->get('filter_root_category');
    if ($filter_root_category && $parent == NULL) {
      $categories = $categories[0]['children'];
    }

    foreach ($categories as $category) {
      if (!isset($category['category_id']) || !isset($category['name'])) {
        $this->logger->error('Invalid / missing category ID or name.');
        $this->results['failed']++;
        continue;
      }

      $langcode = acq_commerce_get_langcode_from_store_id($category['store_id']);

      $parent_data = ($parent) ? [$parent->id()] : [0];
      $position = (isset($category['position'])) ? (int) $category['position'] : 1;

      // Load existing term (if found).
      $query = $this->termStorage->getQuery();
      $group = $query->andConditionGroup()
        ->condition('field_commerce_id', $category['category_id'])
        ->condition('vid', $this->vocabulary->id());
      $query->condition($group);

      $tids = $query->execute();

      if (count($tids) > 1) {
        $this->logger->error('Multiple terms found for category id @cid', ['@cid' => $category['category_id']]);
        $this->results['failed']++;
        continue;
      }
      elseif (count($tids) == 1) {
        $this->logger->info('Updating category term @name [@id]', [
          '@name' => $category['name'],
          '@id' => $category['category_id'],
        ]);

        // Load and update the term entity.
        /** @var \Drupal\taxonomy\Entity\Term $term */
        $term = $this->termStorage->load(array_shift($tids));

        if (!$term->hasTranslation($langcode)) {
          $term = $term->addTranslation($langcode);
          $term->get('field_commerce_id')->setValue($category['category_id']);
        }
        else {
          $term = $term->getTranslation($langcode);
        }

        $term->setName($category['name']);
        $term->parent = $parent_data;
        $term->weight->value = $position;

        // Break child relationships.
        $children = $this->termStorage->loadChildren($term->id(), $this->vocabulary->id());
        if (count($children)) {
          $child_ids = array_map(function ($child) {
            return ($child->id());
          }, $children);

          $this->termStorage->deleteTermHierarchy($child_ids);
        }

        $this->results['updated']++;

      }
      else {
        // Create the term entity.
        $this->logger->info('Creating category term @name [@id]',
          ['@name' => $category['name'], '@id' => $category['category_id']]
        );

        $term = $this->termStorage->create([
          'vid' => $this->vocabulary->id(),
          'name' => $category['name'],
          'field_commerce_id' => $category['category_id'],
          'parent' => $parent_data,
          'weight' => $position,
          'langcode' => $langcode,
        ]);

        $this->results['created']++;
      }

      $term->get('field_category_include_menu')->setValue($category['in_menu']);
      $term->get('description')->setValue($category['description']);

      $term->save();

      // Recurse to children categories.
      $childCats = (isset($category['children'])) ? $category['children'] : [];
      $this->syncCategory($childCats, $term);
    }
  }

}
