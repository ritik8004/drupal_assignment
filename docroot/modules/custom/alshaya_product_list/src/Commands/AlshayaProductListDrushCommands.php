<?php

namespace Drupal\alshaya_product_list\Commands;

use Drupal\alshaya_options_list\AlshayaOptionsListHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;
use Drush\Commands\DrushCommands;

/**
 * Class Alshaya Product List Drush Commands.
 *
 * @package Drupal\alshaya_product_list\Commands
 */
class AlshayaProductListDrushCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Entity Type Manager.
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
   * Option list helper.
   *
   * @var \Drupal\alshaya_options_list\AlshayaOptionsListHelper
   */
  protected $optionsListHeper;

  /**
   * AlshayaProductListDrushCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\alshaya_options_list\AlshayaOptionsListHelper $alshayaOptionsListHelper
   *   Options Helper.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              ConfigFactoryInterface $config_factory,
                              AlshayaOptionsListHelper $alshayaOptionsListHelper) {
    parent::__construct();
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $config_factory;
    $this->optionsListHeper = $alshayaOptionsListHelper;
  }

  /**
   * Generates Batch operation for Product list nodes.
   *
   * @param string $attribute
   *   Attribute string.
   *
   * @command alshaya:generate:attribute:nodes
   *
   * @aliases agan
   *
   * @usage drush agan attr_brand
   *   Creates product list nodes for the attribute.
   */
  public function generateProductListNodes($attribute) {
    $attributeCode = str_replace('attr_', '', $attribute);
    $facet_results = $this->optionsListHeper->loadFacetsData([$attributeCode => $attributeCode]);
    if (!$facet_results) {
      $this->io()->error('No attribute values found.');
      return self::EXIT_FAILURE;
    }
    $attribute_values = array_shift($facet_results);

    // Set batch operation.
    $batch = [
      'title' => $this->t('Generate Nodes for attribute: %s', ['%s' => $attribute]),
      'init_message' => $this->t('Started Node generation...'),
      'operations' => [
        ['\Drupal\alshaya_product_list\Commands\AlshayaProductListDrushCommands::createAttributeNodes',
          [$attribute, $attribute_values],
        ],
      ],
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('Synced data could not be cleaned because an error occurred.'),
    ];

    batch_set($batch);
    drush_backend_batch_process();
  }

  /**
   * Batch Operation for creating nodes.
   */
  public static function createAttributeNodes($attribute, $attribute_values, &$context) {
    // Use the $context['sandbox'] at your convenience to store the
    // information needed to track progression between successive calls.
    if (empty($context['sandbox'])) {
      // Get all the entities that need to be deleted.
      $context['sandbox']['results'] = array_unique($attribute_values);

      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $context['sandbox']['max'] = is_countable($context['sandbox']['results']) ? count($context['sandbox']['results']) : 0;
    }

    $results = [];
    if (isset($context['sandbox']['results']) && !empty($context['sandbox']['results'])) {
      $results = $context['sandbox']['results'];
    }

    // Prepare set of attribute values.
    $results = array_slice($results, $context['sandbox']['current'] ?? 0, 10);
    $set_attribute_values = [];
    foreach ($results as $result) {
      $context['results'][] = $result;
      $context['sandbox']['progress']++;
      $context['sandbox']['current_id'] = $result;

      $set_attribute_values[] = $result;

      // Update our progress information.
      $context['sandbox']['current']++;
    }

    // Create nodes.
    $values = [];
    foreach ($set_attribute_values as $attribute_value) {
      try {
        // Changed to entity query for exact match.
        $node = NULL;
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $nids = $node_storage->getQuery()
          ->condition('type', 'product_list')
          ->condition('field_attribute_name', $attribute)
          ->condition('field_attribute_value', $attribute_value, '= BINARY')
          ->execute();
        if (!empty($nids)) {
          $nid = array_shift($nids);
          $node = $node_storage->load($nid);
        }
        // If node exists, we create or we skip.
        if (!($node instanceof Node)) {
          $node = Node::create([
            // The node entity bundle.
            'type' => 'product_list',
            'langcode' => 'en',
            // The user ID.
            'uid' => 1,
            'title' => $attribute_value,
            'field_attribute_value' => [
              'value' => $attribute_value,
            ],
            'field_attribute_name' => [
              'value' => $attribute,
            ],
          ]);
          $node->save();

          // Creating the translation if langcode not passed.
          /** @var \Drupal\node\Entity\Node $node_translation */
          $node_translation = $node->addTranslation('ar');
          $node_translation->setOwnerId(1);
          $node_translation->setTitle($attribute_value);
          $node_translation->set('field_attribute_name', $attribute);
          $node_translation->set('field_attribute_value', $attribute_value);
          $node_translation->save();
          $values[] = $attribute_value;
        }

      }
      catch (\Exception $e) {
        throw new \Exception($e->getMessage());
      }
    }

    $values = (empty($values)) ? ['none'] : $values;
    $context['message'] = 'Processed ' . $context['sandbox']['progress'] . ' out of ' . $context['sandbox']['max'] . '. Added:' . implode(',', $values);

    if ($context['sandbox']['progress'] !== $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

}
