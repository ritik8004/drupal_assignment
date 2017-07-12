<?php

namespace Drupal\acq_promotion\Plugin\rest\resource;

use Drupal\acq_promotion\AcqPromotionsManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PromotionSyncResource.
 *
 * @package Drupal\acq_promotion\Plugin
 *
 * @ingroup acq_promotion
 *
 * @RestResource(
 *   id = "acq_promotionsync",
 *   label = @Translation("Acquia Commerce Promotion Sync"),
 *   uri_paths = {
 *     "canonical" = "/promotionsync",
 *     "https://www.drupal.org/link-relations/create" = "/promotionsync"
 *   }
 * )
 */
class PromotionSyncResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The promotion manager service.
   *
   * @var \Drupal\acq_promotion\AcqPromotionsManager
   */
  protected $promotionManager;

  /**
   * Queue Factory service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queue;

  /**
   * Config factory service.
   * 
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructs a new PromotionSyncResource object.
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
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   * @param \Drupal\acq_promotion\AcqPromotionsManager $promotionManager
   *   Promotion Manager service.
   * @param \Drupal\Core\Queue\QueueFactory $queue
   *   Queue factory service.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config factory service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    AcqPromotionsManager $promotionManager,
    QueueFactory $queue,
    ConfigFactory $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
    $this->promotionManager = $promotionManager;
    $this->logger = $logger;
    $this->queue = $queue;
    $this->configFactory = $configFactory;
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
      $container->get('logger.factory')->get('acq_promotion'),
      $container->get('current_user'),
      $container->get('acq_promotion.promotions_manager'),
      $container->get('queue'),
      $container->get('config.factory')
    );
  }

  /**
   * Responds to POST requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @param array $promotions
   *   List of promotions being updated/created.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Throws exception expected.
   */
  public function post(array $promotions = []) {
    $promotions = $promotions['promotions'];
    $acq_promotion_attach_batch_size = $this->configFactory
      ->get('acq_promotion.settings')
      ->get('promotion_attach_batch_size');

    foreach ($promotions as $promotion) {
      $attached_promotion_skus = [];
      $fetched_promotion_skus = [];
      $fetched_promotion_sku_attach_data = [];

      // Extract list of sku text attached with the promotion passed.
      $products = $promotion['products'];
      foreach ($products as $product) {
        $fetched_promotion_skus[] = $product['product_sku'];
        $fetched_promotion_sku_attach_data[$product['product_sku']] = [
          'sku' => $product['product_sku'],
          'final_price' => $product['final_price'],
        ];
      }

      // Check if this promotion exists in Drupal.
      $promotion_node = $this->promotionManager->getPromotionByRuleId($promotion['rule_id']);

      // If promotion exists, we update the related skus & final price.
      if ($promotion_node) {
        $attached_skus = $this->promotionManager->getSkusForPromotion($promotion_node);

        // Extract sku text from sku objects.
        if (!empty($attached_skus)) {
          foreach ($attached_skus as $attached_sku) {
            $attached_promotion_skus[] = $attached_sku->getSku();
          }
        }

        // Get list of skus for which promotions should be detached.
        $detach_promotion_skus = array_diff($attached_promotion_skus, $fetched_promotion_skus);

        // Create a queue for removing promotions from skus.
        if (!empty($detach_promotion_skus)) {
          $promotion_detach_queue = $this->queue->get('acq_promotion_detach_queue');
          $data['promotion'] = $promotion_node->id();
          $data['skus'] = $detach_promotion_skus;
          $promotion_detach_queue->createItem($data);
        }
      }
      else {
        // Create promotions node using Metadata from Promotions Object.
        $promotion_node = $this->promotionManager->createPromotionFromConductorResponse($promotion);
      }

      // Attach promotions to skus.
      if ($promotion_node && (!empty($fetched_promotion_skus))) {
        $promotion_attach_queue = $this->queue->get('acq_promotion_attach_queue');
        $data['promotion'] = $promotion_node->id();
        $chunks = array_chunk($fetched_promotion_sku_attach_data, $acq_promotion_attach_batch_size);
        foreach ($chunks as $chunk) {
          $data['skus'] = $chunk;
          $promotion_attach_queue->createItem($data);
        }
      }
    }

    return new ResourceResponse($promotions);
  }

}
