<?php

namespace Drupal\acq_sku;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Provides a service for product synchronization to load categories.
 *
 * @ingroup acq_sku
 */
class ConductorCategoryRepository implements CategoryRepositoryInterface {

  /**
   * Loaded Taxonomy Terms By Commerce ID.
   *
   * @var \Drupal\taxonomy\TermInterface[]
   */
  private $terms = [];

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
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   QueryFactory object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerFactory object.
   * @param string $vocabulary
   *   Taxonomy Vocabulary for categories.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, QueryFactory $query_factory, LoggerChannelFactory $logger_factory, $vocabulary = NULL) {

    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->vocabStorage = $entity_type_manager->getStorage('taxonomy_vocabulary');
    $this->queryFactory = $query_factory;
    $this->logger = $logger_factory->get('acq_sku');

    if ($vocabulary) {
      $this->loadVocabulary($vocabulary);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function loadCategoryTerm($commerce_id) {

    if (!$this->vocabulary) {
      throw new \RuntimeException('No Taxonomy vocabulary set.');
    }

    $commerce_id = (int) $commerce_id;
    if ($commerce_id < 1) {
      $this->logger->error(
        'Invalid category id @cid',
        ['@cid' => $commerce_id]
      );

      return (NULL);
    }

    if (isset($this->terms[$commerce_id])) {
      return ($this->terms[$commerce_id]);
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
        ['@cid' => $category['category_id']]
      );

      return (NULL);

    }
    elseif (count($tids) == 1) {
      $term = $this->termStorage->load(array_shift($tids));
      $this->terms[$commerce_id] = $term;
      return ($term);
    }

    return (NULL);
  }

  /**
   * {@inheritdoc}
   */
  public function setVocabulary($vocabulary) {

    $this->loadVocabulary($vocabulary);
    return ($this);
  }

  /**
   * LoadVocabulary.
   *
   * Load a taxonomy vocabulary from a vid.
   *
   * @param string $vocabulary
   *   Vocabulary VID.
   *
   * @throws \InvalidArgumentException
   */
  private function loadVocabulary($vocabulary) {

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
