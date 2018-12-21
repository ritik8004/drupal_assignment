<?php

namespace Drupal\acq_sku_stock\Service;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku_stock\StockUpdatedEvent;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class StockManager {

  /**
   * DB Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * API Wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  private $apiWrapper;

  /**
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  private $i18nHelper;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Lock.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  private $lock;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $dispatcher;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $logger;

  /**
   * StockManager constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   DB Connection.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   API Wrapper.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18n Helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   Lock.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   */
  public function __construct(Connection $connection,
                              APIWrapper $api_wrapper,
                              I18nHelper $i18n_helper,
                              ConfigFactoryInterface $config_factory,
                              LockBackendInterface $lock,
                              EventDispatcherInterface $dispatcher,
                              LoggerChannelInterface $logger) {
    $this->connection = $connection;
    $this->apiWrapper = $api_wrapper;
    $this->i18nHelper = $i18n_helper;
    $this->configFactory = $config_factory;
    $this->lock = $lock;
    $this->dispatcher = $dispatcher;
    $this->logger = $logger;
  }

  /**
   * Check if product is in stock.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   *
   * @return bool
   *   TRUE if product is in stock.
   */
  public function isProductInStock(SKU $sku) {
    $status = &drupal_static('acq_sku_stock_status', []);

    if (isset($status[$sku->getSku()])) {
      return $status[$sku->getSku()];
    }

    $stock = $this->getStock($sku->getSku());

    if (empty($stock['status'])) {
      $status[$sku->getSku()] = FALSE;
      return FALSE;
    }

    // Check status + quantity of children if configurable.
    switch ($sku->bundle()) {
      case 'configurable':
        // To check if product is in stock or not, we just need first child in
        // stock.
        foreach ($sku->get('field_configured_skus')->getValue() as $child) {
          if (empty($child['value'])) {
            continue;
          }

          $child_sku = SKU::loadFromSku($child['value']);
          if ($child_sku instanceof SKU) {
            if ($this->getStockQuantity($child_sku->getSku()) > 0) {
              return TRUE;
            }
          }
        }
        break;
      case 'simple':
      default:
        return (bool) $this->getStockQuantity($sku->getSku());
        break;
    }

    return FALSE;
  }

  /**
   * Get stock quantity.
   *
   * @param string $sku
   *   SKU string.
   *
   * @return int
   *   Quantity, 0 if status flag is set to false.
   */
  public function getStockQuantity(string $sku) {
    $stock = $this->getStock($sku);

    if (empty($stock['status'])) {
      return 0;
    }

    // @TODO: For now there is no scenario in which we have quantity in float.
    // We have kept the database field to match what is there in MDC and code
    // can be updated later to match that. Casting it to int for now.
    return (int) $stock['quantity'];
  }

  /**
   * Get current stock data for SKU from DB.
   *
   * @param string $sku
   *   SKU string.
   *
   * @return array
   *   Stock data with keys [sku, status, quantity].
   */
  public function getStock(string $sku) {
    $stock = &drupal_static('acq_sku_stock_quantity', []);

    if (isset($stock[$sku])) {
      return $stock[$sku];
    }

    $query = $this->connection->select('acq_sku_stock');
    $query->condition('sku', $sku);
    $result = $query->execute()->fetchAll();

    // We may not have any entry.
    if (empty($result)) {
      return [];
    }

    // Get the first result.
    // @TODO: Add checks for multiple entries.
    $stock[$sku] = (array) reset($result);

    return $stock[$sku];
  }

  /**
   * Get stock from API.
   *
   * @TODO: Implement this.
   *
   * @param string $sku
   *   SKU string.
   */
  public function getStockFallback(string $sku) {
    // @TODO: Invoke Stock API to get stock data.
  }

  /**
   * Update stock data for particular sku.
   *
   * @param string $sku
   *   SKU.
   * @param int|float $quantity
   *   Quantity.
   * @param int $status
   *   Stock status.
   *
   * @throws \Exception
   */
  public function updateStock($sku, $quantity, $status) {
    // Update the stock now.
    $this->acquireLock($sku);

    // First try to check if stock changed.
    $current = $this->getStock($sku);

    // Update only if value changed.
    if (empty($current) || $current['status'] != $status || $current['quantity'] != $quantity) {
      $new = [
        'quantity' => $quantity,
        'status' => $status,
      ];

      $this->connection->merge('acq_sku_stock')
        ->key(['sku' => $sku])
        ->fields($new)
        ->execute();

      $sku_entity = SKU::loadFromSku($sku);
      if ($sku_entity instanceof SKUInterface) {
        $status_changed = $current
          ? $this->isStockStatusChanged($current, $new)
          : TRUE;
        $low_quantity = $this->isQuantityLow($new);
        $event = new StockUpdatedEvent($sku_entity, $status_changed, $low_quantity);
        $this->dispatcher->dispatch(StockUpdatedEvent::EVENT_NAME, $event);
      }
    }

    $this->releaseLock($sku);
  }

  /**
   * Process stock message received in API.
   *
   * @param array $stock
   *   Stock data for particular SKU.
   *
   * @throws \Exception
   */
  public function processStockMessage(array $stock) {
    // Sanity check.
    if (!isset($stock['sku']) || !strlen($stock['sku'])) {
      $this->logger->error('Invalid or empty product SKU. Stock message: @message', [
        '@message' => json_encode($stock),
      ]);

      return;
    }

    $langcode = NULL;

    // Check for stock is valid for current site.
    if (isset($stock['store_id'])) {
      $langcode = $this->i18nHelper->getLangcodeFromStoreId($stock['store_id']);

      if (empty($langcode)) {
        // It could be for a different store/website, don't do anything.
        $this->logger->info('Ignored stock message for different store. Message: @message', [
          '@message' => json_encode($stock),
        ]);

        return;
      }
    }

    // Work Around for the ACM V1 as quantity key is changed in ACM V2.
    $quantity = array_key_exists('qty', $stock) ? $stock['qty'] : $stock['quantity'];
    $stock_status = isset($stock['is_in_stock']) ? (int) $stock['is_in_stock'] : 1;

    $this->updateStock($stock['sku'], $quantity, $stock_status);

    $this->logger->info('Processed stock message for sku @sku. Message: @message', [
      '@sku' => $stock['sku'],
      '@message' => json_encode($stock),
    ]);
  }

  /**
   * Helper function to acquire lock.
   *
   * @param string $sku
   *   SKU string.
   */
  private function acquireLock(string $sku) {
    $lock_key = self::class . ':' . $sku;
    do {
      $lock_acquired = $this->lock->acquire($lock_key);

      // Sleep for half a second before trying again.
      if (!$lock_acquired) {
        usleep(500000);
      }
    } while (!$lock_acquired);
  }

  /**
   * Helper function to release lock.
   *
   * @param string $sku
   *   SKU string.
   */
  private function releaseLock(string $sku) {
    $lock_key = self::class . ':' . $sku;
    $this->lock->release($lock_key);
  }

  /**
   * Get field code for which value is requested.
   *
   * @param array $old
   *   Old stock.
   * @param array $new
   *   New stock.
   *
   * @return string
   *   Field Code.
   */
  private function isStockStatusChanged(array $old, array $new) {
    if ($old['status'] != $new['status']) {
      return TRUE;
    }

    $had_quantity = (bool) $old['quantity'];
    $has_quantity = (bool) $new['quantity'];

    // Either it was zero before or zero now and status says in stock,
    // we consider it is changed.
    if ($new['status'] && $had_quantity != $has_quantity) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Check if quantity is low.
   *
   * @param array $new
   *   New stock.
   *
   * @return bool
   *   TRUE if quantity is low.
   */
  private function isQuantityLow(array $new) {
    // No need to say low stock for OOS items.
    if (!($new['status'])) {
      return FALSE;
    }

    $low_stock = (int) $this->configFactory
      ->get('acq_sku_stock.settings')
      ->get('low_stock');

    return ($new['quantity'] && $new['quantity'] < $low_stock);
  }

}
