<?php

namespace Drupal\alshaya_product\Plugin\search_api\processor;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\StockManager;
use Drupal\Core\Database\Connection;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\SearchApiException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_acm_product\SkuImagesManager;

/**
 * Adds custom field for stock quantity.
 *
 * @SearchApiProcessor(
 *   id = "stock_quantity",
 *   label = @Translation("Stock Quantity"),
 *   description = @Translation("Add customized stock quantity field to the index."),
 *   stages = {
 *     "add_properties" = 20,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class StockQuantityField extends ProcessorPluginBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|null
   */
  protected $database;

  /**
   * Stock Manager.
   *
   * @var \Drupal\acq_sku\StockManager
   */
  protected $acqSkuStockManager;

  /**
   * SKU Images Manager service object.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $processor */
    $processor = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $processor->setDatabase($container->get('database'));
    $processor->setAcqSkuStockManager($container->get('acq_sku.stock_manager'));
    $processor->setSkuImagesManager($container->get('alshaya_acm_product.sku_images_manager'));
    return $processor;
  }

  /**
   * Sets the database connection.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The new database connection.
   *
   * @return $this
   */
  public function setDatabase(Connection $database) {
    $this->database = $database;
    return $this;
  }

  /**
   * Sets the Stock Manager.
   *
   * @param \Drupal\acq_sku\StockManager $stockManager
   *   The new stock manager.
   *
   * @return $this
   */
  public function setAcqSkuStockManager(StockManager $stockManager) {
    $this->acqSkuStockManager = $stockManager;
    return $this;
  }

  /**
   * Sets SKU Images Manager service.
   *
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_image_manager
   *   The new stock manager.
   *
   * @return $this
   */
  public function setSkuImagesManager(SkuImagesManager $sku_image_manager) {
    $this->skuImagesManager = $sku_image_manager;
    return $this;
  }

  /**
   * Retrieves the database connection.
   *
   * @return \Drupal\Core\Database\Connection
   *   The database connection.
   */
  public function getDatabase() {
    return $this->database ?: \Drupal::database();
  }

  /**
   * Retrieves the stock manager.
   *
   * @return \Drupal\acq_sku\StockManager
   *   The stock manager.
   */
  public function getAcqSkuStockManager() {
    return $this->acqSkuStockManager ?: \Drupal::service('acq_sku.stock_manager');
  }

  /**
   * Retrieves the SKU Images Manager.
   *
   * @return \Drupal\alshaya_acm_product\SkuImagesManager
   *   The sku images manager.
   */
  public function getSkuImagesManager() {
    return $this->skuImagesManager ?: \Drupal::service('alshaya_acm_product.sku_images_manager');
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      return $properties;
    }

    if ($datasource->getEntityTypeId() == 'node' && in_array('acq_product', array_keys($datasource->getBundles()))) {
      $definition = [
        'label' => $this->t('Stock Quantity'),
        'description' => $this->t("Alshaya custom stock quantity."),
        'type' => "integer",
        'processor_id' => $this->getPluginId(),
      ];
      $properties["stock_quantity"] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    try {
      $entity = $item->getOriginalObject()->getValue();
    }
    catch (SearchApiException $e) {
      return;
    }

    $fields = $item->getFields(FALSE);
    $fields = $this->getFieldsHelper()
      ->filterForPropertyPath($fields, $item->getDatasourceId(), 'stock_quantity');

    $sku_entity = $entity->get('field_skus')->first()->get('entity')->getValue();
    if ($color = $entity->get('field_product_color')->getString()) {
      $sku_gallery_entity = $this->getSkuImagesManager()->getSkuForGalleryWithColor($sku_entity, $color);
      if (!($sku_gallery_entity instanceof SKU)) {
        $sku_gallery_entity = $sku_entity;
      }
    }

    $quantity = $this->calculateStock($sku_gallery_entity ?? $sku_entity);
    foreach ($fields as $field) {
      $field->addValue($quantity);
    }
  }

  /**
   * Return stock for given sku entity.
   */
  protected function calculateStock(SKU $sku) {
    $sku_string = $sku->getSku();

    $static = &drupal_static(__METHOD__, []);
    if (isset($static[$sku_string])) {
      return $static[$sku_string];
    }

    // Return quantity of given SKU.
    switch ($sku->bundle()) {
      case 'configurable':
        $configured_skus = $sku->get('field_configured_skus')->getValue();
        $child_skus = array_map(function ($item) {
          return $item['value'];
        }, $configured_skus);

        $query = $this->getDatabase()->select('acq_sku_stock', 'stock');
        $query->addExpression('SUM(stock.quantity)', 'final_quantity');
        $query->condition('stock.sku', $child_skus, 'IN');
        $query->condition('stock.status', 1);
        $static[$sku_string] = $query->execute()->fetchField();
        break;

      case 'simple':
        $static[$sku_string] = $this->getAcqSkuStockManager()->getStockQuantity($sku->getSku());
        break;
    }
    return $static[$sku_string];
  }

}
