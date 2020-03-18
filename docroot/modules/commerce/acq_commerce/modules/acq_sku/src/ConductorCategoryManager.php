<?php

namespace Drupal\acq_sku;

use Differ\ArrayDiff;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\acq_commerce\Conductor\ClientFactory;
use Drupal\Core\Database\Connection;
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
   * Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $modulehandler;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ClientFactory $client_factory, APIWrapper $api_wrapper, QueryFactory $query_factory, LoggerChannelFactory $logger_factory, I18nHelper $i18n_helper, ModuleHandlerInterface $moduleHandler, Connection $connection) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->vocabStorage = $entity_type_manager->getStorage('taxonomy_vocabulary');
    $this->clientFactory = $client_factory;
    $this->apiWrapper = $api_wrapper;
    $this->queryFactory = $query_factory;
    $this->logger = $logger_factory->get('acq_sku');
    $this->i18nHelper = $i18n_helper;
    $this->modulehandler = $moduleHandler;
    $this->connection = $connection;
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
  public function loadCategoryData($store_id) {
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
      'created' => [],
      'updated' => [],
      'failed'  => [],
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
    /** @var \Drupal\Core\Lock\PersistentDatabaseLockBackend $lock */
    $lock = \Drupal::service('lock.persistent');

    // Remove top level item (Default Category) from the categories, if its set
    // in configuration and category is with no parent.
    $filter_root_category = \Drupal::config('acq_commerce.conductor')->get('filter_root_category');
    if ($filter_root_category && $parent === NULL) {
      $categories = $categories[0]['children'];
    }

    foreach ($categories as $category) {
      if (!isset($category['category_id']) || empty($category['name'])) {
        $this->logger->error('Invalid / missing category ID or name.');
        $this->results['failed'][] = $category['category_id'];
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

      $existingTermData = [];

      // Always use the first term and continue.
      if (count($tids) > 0) {
        // Load and update the term entity.
        /** @var \Drupal\taxonomy\Entity\Term $term */
        $term = $this->getTranslatedTerm(array_shift($tids), $langcode);
        $existingTermData = $term->toArray();

        $term->get('field_commerce_id')->setValue($category['category_id']);
        $term->setName($category['name']);
        $term->parent = $parent_data;
        $term->weight->value = $position;

        // Break child relationships.
        $children = $this->termStorage->loadChildren($term->id(), $this->vocabulary->id());
        if (count($children)) {
          $child_ids = array_map(function ($child) use ($category) {
            // If term having commerce id, means its sync from magento and
            // thus we process. Term not having commerce id means its created
            // only on Drupal and thus we skip processing.
            if ($commerce_id = $child->get('field_commerce_id')->first()) {
              // We check if the child exists in the response get from magento.
              foreach ($category['children'] as $sync_cat_child) {
                if ($commerce_id->getString() == $sync_cat_child['category_id']) {
                  return $child->id();
                }
              }
            }
          }, $children);

          $this->termStorage->deleteTermHierarchy($child_ids);
        }

        $this->results['updated'][] = $category['category_id'];
        // Set a flag whether category is new or updated.
        $term->isNewCategory = FALSE;
      }
      else {
        // Create the term entity.
        $term = $this->termStorage->create([
          'vid' => $this->vocabulary->id(),
          'name' => $category['name'],
          'field_commerce_id' => $category['category_id'],
          'parent' => $parent_data,
          'weight' => $position,
          'langcode' => $langcode,
        ]);

        // Set a flag whether category is new or updated.
        $term->isNewCategory = TRUE;
        $this->results['created'][] = $category['category_id'];
      }

      // Store status of category.
      $term->get('field_commerce_status')->setValue((int) $category['is_active']);

      $term->get('field_category_include_menu')->setValue($category['in_menu']);
      $term->get('description')->setValue($category['description']);
      $term->setFormat('rich_text');

      // Invoke the alter hook to allow all modules to update the term.
      \Drupal::moduleHandler()->alter('acq_sku_commerce_category', $term, $category, $parent);

      try {
        $term->save();

        // $existingTermData will have value when it is updating.
        if ($existingTermData) {
          $updatedTerm = $this->getTranslatedTerm($term->id(), $langcode);
          $updatedTermData = $updatedTerm->toArray();

          $differ = new ArrayDiff();
          $diff = $differ->diff($existingTermData, $updatedTermData);

          $this->logger->info('Updated category @magento_id for @langcode: @diff.', [
            '@langcode' => $langcode,
            '@magento_id' => $category['category_id'],
            '@diff' => json_encode($diff),
          ]);
        }
        else {
          $this->logger->info('New category @magento_id for @langcode saved.', [
            '@langcode' => $langcode,
            '@magento_id' => $category['category_id'],
          ]);
        }
      }
      catch (\Exception $e) {
        $this->logger->warning('Failed saving category term @name [@magento_id] for @langcode', [
          '@name' => $category['name'],
          '@langcode' => $langcode,
          '@magento_id' => $category['category_id'],
        ]);

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

  /**
   * Wrapper function to get translated term for tid.
   *
   * @param mixed $tid
   *   Not sure about Drupal standards as of now, it can be int/string.
   * @param string $langcode
   *   Language code.
   *
   * @return \Drupal\taxonomy\TermInterface
   *   Loaded, translated term.
   */
  private function getTranslatedTerm($tid, string $langcode): TermInterface {
    $term = $this->termStorage->load($tid);

    if (!$term->hasTranslation($langcode)) {
      $term = $term->addTranslation($langcode);

      // We doing this because when the translation of term is created by
      // addTranslation(), pathauto alias is not created for the translated
      // version.
      // @see https://www.drupal.org/project/pathauto/issues/2995829.
      if ($this->modulehandler->moduleExists('pathauto')) {
        $term->path->pathauto = 1;
      }
    }
    else {
      $term = $term->getTranslation($langcode);
    }

    return $term;
  }

  /**
   * Identify categories which not in commerce backend and must be deleted.
   *
   * @param array $sync_categories
   *   Sync categories.
   *
   * @return array
   *   Orphan categories.
   */
  public function getOrphanCategories(array $sync_categories) {
    // Get all category terms with commerce id.
    $query = $this->connection->select('taxonomy_term_field_data', 'ttd');
    $query->fields('ttd', ['tid', 'name']);
    $query->leftJoin('taxonomy_term__field_commerce_id', 'tcid', 'ttd.tid=tcid.entity_id');
    $query->fields('tcid', ['field_commerce_id_value']);
    $query->condition('ttd.vid', 'acq_product_category');
    $result = $query->execute()->fetchAllAssoc('tid', \PDO::FETCH_ASSOC);

    $affected_terms = array_unique(array_merge($sync_categories['created'], $sync_categories['updated']));
    // Filter terms which are not in sync response.
    return $result = array_filter($result, function ($val) use ($affected_terms) {
      return !in_array($val['field_commerce_id_value'], $affected_terms);
    });
  }

}
