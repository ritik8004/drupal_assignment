<?php

namespace Drupal\acq_commerce\Plugin\QueueWorker;

use Drupal\acq_sku\CategoryManagerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              CategoryManagerInterface $conductor_category_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->conductorCategoryManager = $conductor_category_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('acq_sku.category_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $this->getLogger('category_sync')->notice('Category sync started.');
    $this->conductorCategoryManager->synchronizeTree('acq_product_category');
    $this->getLogger('category_sync')->notice('Category sync completed.');
  }

}
