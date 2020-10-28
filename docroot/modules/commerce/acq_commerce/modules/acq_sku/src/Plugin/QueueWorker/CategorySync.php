<?php

namespace Drupal\acq_sku\Plugin\QueueWorker;

use Drupal\acq_sku\CategoryManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Category sync queue worker.
 *
 * @QueueWorker(
 *   id = "category_sync",
 *   title = @Translation("Category Sync"),
 * )
 */
class CategorySync extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;

  /**
   * Queue Name.
   */
  const QUEUE_NAME = 'category_sync';

  /**
   * Conductor category manager.
   *
   * @var \Drupal\acq_sku\CategoryManagerInterface
   */
  protected $conductorCategoryManager;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * CategorySync constructor.
   *
   * @param array $configuration
   *   Plugin config.
   * @param string $plugin_id
   *   Plugin unique id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\acq_sku\CategoryManagerInterface $conductor_category_manager
   *   A CategoryManager instance.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              CategoryManagerInterface $conductor_category_manager,
                              EventDispatcherInterface $dispatcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->conductorCategoryManager = $conductor_category_manager;
    $this->dispatcher = $dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('acq_sku.category_manager'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $this->getLogger('category_sync')->notice('Category sync started.');
    $this->conductorCategoryManager->synchronizeTree('acq_product_category');
    Cache::invalidateTags(['taxonomy_term:acq_product_category']);
    $this->getLogger('category_sync')->notice('Category sync completed.');
  }

}
