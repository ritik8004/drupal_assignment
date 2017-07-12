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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    AcqPromotionsManager $promotionManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
    $this->promotionManager = $promotionManager;
    $this->logger = $logger;
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
      $container->get('acq_promotion.promotions_manager')
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
    $output = $this->promotionManager->processPromotions($promotions);

    return new ResourceResponse($output);
  }

}
