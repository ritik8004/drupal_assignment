<?php

namespace Drupal\alshaya_hm_images\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Event\ProcessBlackListedProductsEvent;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\alshaya_hm_images\SkuAssetManager;

/**
 * Class Alshaya HM Process Blacklisted Products Subscriber.
 *
 * @package Drupal\alshaya_hm_images\EventSubscriber
 */
class AlshayaHMProcessBlacklistedProductsSubscriber implements EventSubscriberInterface {

  /**
   * SKU Assets Manager.
   *
   * @var \Drupal\alshaya_hm_images\SkuAssetManager
   */
  private $skuAssetsManager;

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * AlshayaHMProcessBlacklistedProductsSubscriber constructor.
   *
   * @param \Drupal\alshaya_hm_images\SkuAssetManager $sku_assets_manager
   *   SKU Assets Manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   Logger factory.
   */
  public function __construct(SkuAssetManager $sku_assets_manager,
                              Connection $connection,
                              LoggerChannelFactory $logger_factory) {
    $this->skuAssetsManager = $sku_assets_manager;
    $this->connection = $connection;
    $this->logger = $logger_factory->get('alshaya_hm_images');
  }

  /**
   * Process Blacklisted Products event handler.
   *
   * @param \Drupal\acq_sku\Event\ProcessBlackListedProductsEvent $event
   *   Acq sku validate event.
   */
  public function processBlackListedProducts(ProcessBlackListedProductsEvent $event) {
    // Fetching Blacklisted Products.
    $query = $this->connection->select('acq_sku_field_data', 'acfd');
    $query->fields('acfd');
    $query->condition('attr_assets__value', '%blacklist_expiry%', 'LIKE');
    $products = $query->execute()->fetchAll();

    // Get Assets for each SKU.
    foreach ($products as $product) {
      $sku = SKU::loadFromSku($product->sku, '', FALSE, FALSE);
      $this->logger->notice('Drupal attempted to download blacklisted images for the sku:@sku', [
        '@sku' => $product->sku,
      ]);
      if ($sku instanceof SKUInterface) {
        $this->skuAssetsManager->getAssetsForSku($sku, 'pdp');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ProcessBlackListedProductsEvent::EVENT_NAME =>
        ['processBlackListedProducts', 100],
    ];
  }

}
