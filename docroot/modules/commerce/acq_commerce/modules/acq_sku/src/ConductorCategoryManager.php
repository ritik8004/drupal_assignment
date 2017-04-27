<?php

namespace Drupal\acq_sku;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\acq_commerce\Conductor\ClientFactory;

/**
 * Provides a service for category data to taxonomy synchronization.
 *
 * @ingroup acq_sku
 */
class ConductorCategoryManager implements CategoryManagerInterface {

  /**
   * Conductor Agent Category Data API Endpoint.
   *
   * @const CONDUCTOR_API_CATEGORY
   */
  const CONDUCTOR_API_CATEGORY = 'categories';

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
   * Drupal Entity Query Factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  private $queryFactory;

  /**
   * Result (create / update / failed) counts.
   *
   * @var array
   */
  private $results;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\acq_commerce\Conductor\ClientFactory $client_factory
   *   ClientFactory object.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   QueryFactory object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ClientFactory $client_factory, QueryFactory $query_factory, LoggerChannelFactory $logger_factory) {

    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->vocabStorage = $entity_type_manager->getStorage('taxonomy_vocabulary');
    $this->queryFactory = $query_factory;
    $this->clientFactory = $client_factory;
    $this->logger = $logger_factory->get('acq_sku');
  }

  /**
   * {@inheritdoc}
   */
  public function synchronizeTree($vocabulary, $remoteRoot = NULL) {

    $this->resetResults();
    $this->loadVocabulary($vocabulary);

    // Load Conductor Category data.
    $remoteRoot = ($remoteRoot !== NULL) ? (int) $remoteRoot : NULL;
    $categories = [$this->loadCategoryData($remoteRoot)];

    // Recurse the category tree and create / update nodes.
    $this->syncCategory($categories, NULL);

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

    $this->resetResults();
    $this->loadVocabulary($vocabulary);

    // Load the current parent of the updated node (if any).
    $parent = NULL;
    $query = $this->queryFactory->get('taxonomy_term');
    $group = $query->andConditionGroup()
      ->condition('field_commerce_id', $categories['category_id'])
      ->condition('vid', $this->vocabulary->id());
    $query->condition($group);

    $tids = $query->execute();

    if (count($tids)) {
      $tid = array_shift($tids);
      $parents = $this->termStorage->loadParents($tid);
      $parent = array_shift($parents);
      $parent = ($parent && $parent->id()) ? $parent : NULL;
    }

    // Recurse the category tree and create / update nodes.
    $this->syncCategory([$categories], $parent);

    return ($this->results);
  }

  /**
   * LoadCategoryData.
   *
   * Load the commerce backend category data from Conductor.
   *
   * @param int $remoteRoot
   *   Remote Root ID (optional)
   *
   * @return array
   *   Array of categories.
   */
  private function loadCategoryData($remoteRoot) {

    $endpoint = self::CONDUCTOR_API_CATEGORY;

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    $categories = [];

    try {
      $categories = $this->tryAgentRequest($doReq, 'loadCategoryData', 'categories');
    }
    catch (ConductorException $e) {
      $this->logger->error('Unable to load conductor category data.');
    }

    return ($categories);
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
    $filter_category = \Drupal::config('acq_commerce.conductor')->get('filter_root_category');
    if ($filter_category && $parent == NULL) {
      $categories = $categories[0]['children'];
    }

    foreach ($categories as $category) {
      if (!isset($category['category_id']) || !isset($category['name'])) {
        $this->logger->error('Invalid / missing category ID or name.');
        $this->results['failed']++;
        continue;
      }

      $parent_data = ($parent) ? [$parent->id()] : [0];
      $position = (isset($category['position'])) ? (int) $category['position'] : 1;

      // Load existing term (if found).
      $query = $this->queryFactory->get('taxonomy_term');
      $group = $query->andConditionGroup()
        ->condition('field_commerce_id', $category['category_id'])
        ->condition('vid', $this->vocabulary->id());
      $query->condition($group);

      $tids = $query->execute();

      if (count($tids) > 1) {

        $this->logger->error(
          'Multiple terms found for category id @cid',
          ['@cid' => $category['category_id']]
        );

        $this->results['failed']++;
        continue;

      }
      elseif (count($tids) == 1) {

        $this->logger->info('Updating category term @name [@id]',
          ['@name' => $category['name'], '@id' => $category['category_id']]
        );

        // Load and update the term entity.
        $term = $this->termStorage->load(array_shift($tids));
        $term->name->value = $category['name'];
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
          'vid'               => $this->vocabulary->id(),
          'name'              => $category['name'],
          'field_commerce_id' => $category['category_id'],
          'parent'            => $parent_data,
          'weight'            => $position,
        ]);

        $this->results['created']++;
      }

      $term->save();

      // Recurse to children categories.
      $childCats = (isset($category['children'])) ? $category['children'] : [];
      $this->syncCategory($childCats, $term);
    }
  }

}
