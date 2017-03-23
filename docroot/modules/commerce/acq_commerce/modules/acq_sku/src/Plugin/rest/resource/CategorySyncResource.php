<?php
/**
 * @file
 * Contains Drupal\acq_sku\Plugin\rest\resource\CategorySyncResource
 */

namespace Drupal\acq_sku\Plugin\rest\resource;

use Drupal\acq_sku\CategoryManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CategorySyncResource
 * @package Drupal\acq_sku\Plugin
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
   * Taxonomy Vacabulary VID of Acquia Commerce Category Taxonomy
   * @const CATEGORY_TAXONOMY
   */
  const CATEGORY_TAXONOMY = 'acq_product_category';

  /**
   * Drupal Entity Type Manager Instance
   * @var EntityTypeManagerInterface $entityManager
   */
  private $entityManager;

  /**
   * Drupal Config Factory Instance
   * @var ConfigFactoryInterface $configFactory
   */
  private $configFactory;

  /**
   * Drupal Entity Query Factory
   * @var QueryFactory $queryFactory
   */
  private $queryFactory;

  /**
   * Construct
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
   * @param \Drupal\acq_sku\CategoryManagerInterface $category_manager
   *   A CategoryManager instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $serializer_formats, LoggerInterface $logger, CategoryManagerInterface $category_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->categoryManager = $category_manager;
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
      $container->get('logger.factory')->get('acq_commerce'),
      $container->get('acq_sku.category_manager')
    );
  }

  /**
   * post
   *
   * Handle Conductor posting an array of category data for update.
   *
   * @param array $categories Category data for update
   *
   * @return ResourceResponse $response
   */
  public function post(array $categories = [])
  {
    $response = $this->categoryManager->synchronizeCategory(
      self::CATEGORY_TAXONOMY,
      $categories
    );

    $response['success'] = (bool) (($response['created'] > 0) || ($response['updated'] > 0));

    return(new ResourceResponse($response));
  }
}
