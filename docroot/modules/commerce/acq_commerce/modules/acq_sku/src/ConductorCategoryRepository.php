<?php

namespace Drupal\acq_sku;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param string $vocabulary
   *   Taxonomy Vocabulary for categories.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              LoggerChannelFactory $logger_factory,
                              Connection $connection,
                              $vocabulary = NULL) {

    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->vocabStorage = $entity_type_manager->getStorage('taxonomy_vocabulary');
    $this->connection = $connection;
    $this->logger = $logger_factory->get('acq_sku');

    if ($vocabulary) {
      $this->loadVocabulary($vocabulary);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTermIdFromCommerceId($commerce_id) {
    $mappings = &drupal_static(__METHOD__, []);

    if (empty($mappings)) {
      $query = $this->connection->select('taxonomy_term__field_commerce_id');
      $query->fields('taxonomy_term__field_commerce_id', ['field_commerce_id_value', 'entity_id']);
      $mappings = $query->execute()->fetchAllKeyed(0, 1);
    }

    return $mappings[$commerce_id] ?? NULL;
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

    $tid = $this->getTermIdFromCommerceId($commerce_id);

    if (empty($tid)) {
      return NULL;
    }

    $term = $this->termStorage->load($tid);
    $this->terms[$commerce_id] = $term;

    return ($term);
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
