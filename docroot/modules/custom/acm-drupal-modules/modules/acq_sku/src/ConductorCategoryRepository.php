<?php

/**
 * @file
 * Contains \Drupal\acq_sku\ConductorCategoryRepository
 */

namespace Drupal\acq_sku;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\taxonomy\VocabularyInterface;
use Drupal\acq_commerce\Conductor\ClientFactory;

/**
 * Provides a service for Conductor product synchronization to load
 * taxonomy categories.
 *
 * @ingroup acq_sku
 */
class ConductorCategoryRepository implements CategoryRepositoryInterface {

  /**
   * Loaded Taxonomy Terms By Commerce ID
   * @var TermInterface[] $terms
   */
  private $terms = [];

  /**
   * Taxonomy Term Entity Storage
   * @var TermStorageInterface $termStorage
   */
  private $termStorage;

  /**
   * Taxonomy Vocabulary Entity Storage
   * @var VocabularyStorageInterface $vocabStorage
   */
  private $vocabStorage;

  /**
   * Taxonomy Vocabulary Entity to Sync
   * @var VocabularyInterface $vocabulary
   */
  private $vocabulary;

  /**
   * Drupal Entity Query Factory
   * @var QueryFactory $queryFactory
   */
  private $queryFactory;

  /**
   * Constructor
   *
   * @param EntityTypeManagerInterface $entity_type_manager
   * @param QueryFactory $query_factory
   * @param LoggerChannelFactory $loggerFactory
   * @param string $vocabulary Taxonomy Vocabulary for categories
   *
   * @return void
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, QueryFactory $query_factory, LoggerChannelFactory $logger_factory, $vocabulary = NULL)
  {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->vocabStorage = $entity_type_manager->getStorage('taxonomy_vocabulary');
    $this->queryFactory = $query_factory;
    $this->logger = $logger_factory->get('acq_sku');

    if ($vocabulary) {
      $this->loadVocabulary($vocabulary);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function loadCategoryTerm($commerce_id)
  {
    if (!$this->vocabulary) {
      throw new \RuntimeException('No Taxonomy vocabulary set.');
    }

    $commerce_id = (int) $commerce_id;
    if ($commerce_id < 1) {
      $this->logger->error(
        'Invalid category id @cid',
        array('@cid' => $commerce_id)
      );

      return(NULL);
    }

    if (isset($this->terms[$commerce_id])) {
      return($this->terms[$commerce_id]);
    }

    $query = $this->queryFactory->get('taxonomy_term');
    $group = $query->andConditionGroup()
      ->condition('field_commerce_id', $commerce_id)
      ->condition('vid', $this->vocabulary->id());
    $query->condition($group);

    $tids = $query->execute();

    if (count($tids) > 1) {

      $this->logger->error(
        'Multiple terms found for category id @cid',
        array('@cid' => $category['category_id'])
      );

      return(NULL);

    } elseif (count($tids) == 1) {
      $term = $this->termStorage->load(array_shift($tids));
      $this->terms[$commerce_id] = $term;
      return($term);
    }

    return(NULL);
  }

  /**
   * {@inheritDoc}
   */
  public function setVocabulary($vocabulary)
  {
    $this->loadVocabulary($vocabulary);
    return($this);
  }

  /**
   * loadVocabulary
   *
   * Load a taxonomy vocabulary from a vid.
   *
   * @param string $vocabulary Vocabulary VID
   *
   * @return void
   * @throws \InvalidArgumentException
   */
  private function loadVocabulary($vocabulary)
  {
    if (!strlen($vocabulary)) {
      throw new \InvalidArgumentException(
        'ConductorCategoryRepository requires a taxonomy vocabulary machine name.'
      );
    }

    $vocab = $this->vocabStorage->load($vocabulary);

    if (!$vocab || !$vocab->id()) {
      throw new \InvalidArgumentException(sprintf(
        'ConductorCategoryRepository unable to locate vocabulary %s.',
        $vocabulary
      ));
    }

    $this->vocabulary = $vocab;
  }
}
