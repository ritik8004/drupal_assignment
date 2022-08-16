<?php

namespace Drupal\acq_sku\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\acq_sku\ConductorCategorySyncHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Category Sync Resource.
 *
 * @package Drupal\acq_sku\Plugin
 *
 * @ingroup acq_sku
 *
 * @RestResource(
 *   id = "acq_categorysync",
 *   label = @Translation("Acquia Commerce Category Sync"),
 *   uri_paths = {
 *     "create" = "/categorysync"
 *   }
 * )
 */
class CategorySyncResource extends ResourceBase {

  /**
   * Taxonomy Vacabulary VID of Acquia Commerce Category Taxonomy.
   *
   * @const CATEGORY_TAXONOMY
   */
  public const CATEGORY_TAXONOMY = 'acq_product_category';

  /**
   * Category sync helper.
   *
   * @var \Drupal\acq_sku\ConductorCategorySyncHelper
   */
  protected $categorySyncHelper;

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
   * @param \Drupal\acq_sku\ConductorCategorySyncHelper $category_sync_helper
   *   Category sync helper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, ConductorCategorySyncHelper $category_sync_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->categorySyncHelper = $category_sync_helper;
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
      $container->get('acq_sku.conductor_cat_sync_helper')
    );
  }

  /**
   * Post.
   *
   * Handle Conductor posting an array of category data for update.
   *
   * @param array $data
   *   Category data for update.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   HTTP Response.
   */
  public function post(array $data) {
    $response = [];
    $this->categorySyncHelper->createItem($data['category_id']);
    $response['success'] = TRUE;
    return (new ModifiedResourceResponse($response));
  }

}
