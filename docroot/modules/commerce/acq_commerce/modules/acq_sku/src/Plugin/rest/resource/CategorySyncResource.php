<?php

namespace Drupal\acq_sku\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\Core\Queue\QueueFactory;
use Drupal\acq_sku\Plugin\QueueWorker\CategorySync;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CategorySyncResource.
 *
 * @package Drupal\acq_sku\Plugin
 *
 * @ingroup acq_sku
 *
 * @RestResource(
 *   id = "acq_categorysync",
 *   label = @Translation("Acquia Commerce Category Sync"),
 *   uri_paths = {
 *     "canonical" = "/categorysync",
 *     "https://www.drupal.org/link-relations/create" = "/categorysync"
 *   }
 * )
 */
class CategorySyncResource extends ResourceBase {

  /**
   * Taxonomy Vacabulary VID of Acquia Commerce Category Taxonomy.
   *
   * @const CATEGORY_TAXONOMY
   */
  const CATEGORY_TAXONOMY = 'acq_product_category';

  /**
   * Queue factory service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   Queue factory service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, QueueFactory $queue_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->queueFactory = $queue_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get(self::class),
      $container->get('queue')
    );
  }

  /**
   * Post.
   *
   * Handle Conductor posting an array of category data for update.
   *
   * @param array $categories
   *   Category data for update.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   HTTP Response.
   */
  public function post(array $categories) {
    $queue = $this->queueFactory->get(CategorySync::QUEUE_NAME);
    // Add flag in queue for category sync.
    // @see \Drupal\acq_commerce\Plugin\QueueWorker\CategorySync
    $queue->createItem('category sync');
    $response['success'] = TRUE;
    return (new ModifiedResourceResponse($response));
  }

}
