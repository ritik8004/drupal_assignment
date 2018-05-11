<?php

namespace Drupal\acq_sku;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\acq_commerce\Conductor\ClientFactory;
use Drupal\taxonomy\TermInterface;

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
   * Drupal Entity Query Factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  private $queryFactory;

  /**
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  private $i18nHelper;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\acq_commerce\Conductor\ClientFactory $client_factory
   *   ClientFactory object.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   API Wrapper object.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   Query factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ClientFactory $client_factory, APIWrapper $api_wrapper, QueryFactory $query_factory, LoggerChannelFactory $logger_factory, I18nHelper $i18n_helper) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->vocabStorage = $entity_type_manager->getStorage('taxonomy_vocabulary');
    $this->clientFactory = $client_factory;
    $this->apiWrapper = $api_wrapper;
    $this->queryFactory = $query_factory;
    $this->logger = $logger_factory->get('acq_sku');
    $this->i18nHelper = $i18n_helper;
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

    foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
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
    $this->resetResults();
    $this->loadVocabulary($vocabulary);

    // If parent is 0, means term will be created at root level.
    $parent = 0;
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
      $parent = ($parent && $parent->id()) ? $parent : 0;
    }
    else {
      // This might be the case of new term which doesn't exist yet. In this
      // case, we need to find the existing parent or new term will be created
      // at root level.
      if (isset($categories['parent_id'])) {
        $query = $this->queryFactory->get('taxonomy_term');
        $group = $query->andConditionGroup()
          ->condition('field_commerce_id', $categories['parent_id'])
          ->condition('vid', $this->vocabulary->id());
        $query->condition($group);

        $tids = $query->execute();
        // If term with given commerce id exists.
        if (count($tids)) {
          $tid = array_shift($tids);
          $parent = $this->termStorage->load($tid);
        }
      }
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
    $lock = \Drupal::lock();

    // Remove top level item (Default Category) from the categories, if its set
    // in configuration and category is with no parent.
    $filter_root_category = \Drupal::config('acq_commerce.conductor')->get('filter_root_category');
    if ($filter_root_category && $parent === NULL) {
      $categories = $categories[0]['children'];
    }

    foreach ($categories as $category) {
      if (!isset($category['category_id']) || !isset($category['name'])) {
        $this->logger->error('Invalid / missing category ID or name.');
        $this->results['failed']++;
        continue;
      }

      $langcode = $this->i18nHelper->getLangcodeFromStoreId($category['store_id']);

      // If lancode is not available, means no mapping of store and language.
      if (!$langcode) {
        continue;
      }

      $lock_key = 'syncCategory' . $category['category_id'];

      // Acquire lock to ensure parallel processes are executed one by one.
      do {
        $lock_acquired = $lock->acquire($lock_key);

        // Sleep for half a second before trying again.
        if (!$lock_acquired) {
          usleep(500000);
        }
      } while (!$lock_acquired);

      // Get current language translation if available.
      if ($parent instanceof TermInterface && $parent->hasTranslation($langcode)) {
        $parent = $parent->getTranslation($langcode);
      }

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
      }

      // Always use the first term and continue.
      if (count($tids) > 0) {
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

      // Get status of category.
      $status = (int) $category['is_active'];

      // Get the parent's status if parent available.
      // By default we use TRUE.
      $parent_status = $parent ? $parent->get('field_commerce_status')->getString() : TRUE;

      // We set the status to true only if both parent and child are enabled.
      $status = $status && $parent_status;
      $term->get('field_commerce_status')->setValue((int) $status);

      $term->get('field_category_include_menu')->setValue($category['in_menu']);
      $term->get('description')->setValue($category['description']);
      $term->setFormat('rich_text');

      try {
        $term->save();
      }
      catch (\Exception $e) {
        $this->logger->warning('Failed saving category term @name [@id]',
          ['@name' => $category['name'], '@id' => $category['category_id']]
        );

        // Release the lock.
        $lock->release($lock_key);

        continue;
      }

      // Release the lock.
      $lock->release($lock_key);

      // Recurse to children categories.
      $childCats = (isset($category['children'])) ? $category['children'] : [];
      $this->syncCategory($childCats, $term);
    }
  }

}
