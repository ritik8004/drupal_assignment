<?php

namespace Drupal\alshaya_rcs_product\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Path\AliasManager;

/**
 * Class Alshaya Rcs Product Helper.
 *
 * @package Drupal\alshaya_rcs_product\Services
 */
class AlshayaRcsProductHelper {

  /**
   * RCS Content type id.
   */
  const RCS_CONTENT_TYPE_ID = 'rcs_product';

  /**
   * Source Content type.
   */
  const SOURCE_CONTENT_TYPE_ID = 'acq_product';

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs a new AlshayaRcsCategoryHelper instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Path\AliasManager $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $language_manager,
                              AliasManager $alias_manager,
                              Connection $connection,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->aliasManager = $alias_manager;
    $this->connection = $connection;
    $this->logger = $logger_factory->get('alshaya_rcs_product');
  }

  /**
   * Process node data migration to RCS content type.
   */
  public function processProductMigrationToRcsCt() {
    $langcode = $this->languageManager->getDefaultLanguage()->getId();

    $query = $this->connection->select('node_field_data', 'nfd');
    $query->fields('nfd', ['nid', 'title', 'langcode']);

    // Join pdp layout field table to select only those nodes
    // that have value in select pdp layout field.
    $query->innerJoin('node__field_select_pdp_layout', 'nfspl', 'nfspl.entity_id = nfd.nid AND nfspl.langcode = nfd.langcode');

    $query->condition('nfd.langcode', $langcode);
    $query->condition('nfd.status', 1);
    $query->condition('nfd.type', self::SOURCE_CONTENT_TYPE_ID);

    $nodes = $query->distinct()->execute()->fetchAll();

    // Do not process if no nodes are found.
    if (empty($nodes)) {
      return;
    }

    // Migrate rcs content type.
    foreach ($nodes as $node) {
      try {
        /** @var \Drupal\node\Entity\Node $node_data */
        $node_data = $this->entityTypeManager->getStorage('node')->load($node->nid);

        // Create a new rcs_product node object.
        /** @var \Drupal\node\Entity\Node $rcs_node */
        $rcs_node = $this->entityTypeManager->getStorage('node')->create([
          'type' => self::RCS_CONTENT_TYPE_ID,
          'title' => $node_data->getTitle(),
          'langcode' => $langcode,
        ]);

        $rcs_node->get('field_select_pdp_layout')
          ->setValue($node_data->get('field_select_pdp_layout')->getValue());

        // Get slug field value from old node alias.
        $slug = $this->aliasManager->getAliasByPath('/node/' . $node_data->id());
        $slug = ltrim($slug, '/');
        $rcs_node->get('field_product_slug')->setValue($slug);

        // Save the new node object in rcs content type.
        $rcs_node->save();

        // Check if the translations exists for arabic language.
        if ($node_data->hasTranslation($langcode)) {
          // Get node translation.
          $node_translation_data = $node_data->getTranslation('ar');

          // Add translation to the new node.
          $rcs_node = $rcs_node->addTranslation('ar', ['title' => $node_translation_data->getTitle()]);
          $rcs_node->save();
        }
      }
      catch (\Exception $exception) {
        $this->logger->error('Error while migrating nodes to RCS content type. message:@message', [
          '@message:' => $exception->getMessage(),
        ]);
      }
    }
  }

  /**
   * Rollback node data from RCS content type.
   */
  public function rollbackProductMigration() {
    // Get the placeholder node from config.
    $entity_id = $this->configFactory->get('rcs_placeholders.settings')->get('product.placeholder_nid');

    // Get all the nodes from rcs content type, except placeholder node.
    try {
      $query = $this->entityTypeManager->getStorage('node')->getQuery();
      $query->condition('type', self::RCS_CONTENT_TYPE_ID);
      $query->condition('nid', $entity_id, '<>');
      $nodes = $query->execute();
    }
    catch (\Exception $exception) {
      $this->logger->error('Error while fetching RCS nodes for deletion. message:@message', [
        '@message:' => $exception->getMessage(),
      ]);
    }

    // Return if none available.
    if (empty($nodes)) {
      return;
    }

    // Delete nodes from RCS content type.
    foreach ($nodes as $node) {
      try {
        $this->entityTypeManager->getStorage('node')->load($node)->delete();
      }
      catch (\Exception $exception) {
        $this->logger->error('Error while deleting nodes from RCS content type. message:@message', [
          '@message:' => $exception->getMessage(),
        ]);
      }
    }
  }

}
