<?php

namespace Drupal\acq_sku\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Event\ProcessBlackListedProductsEvent;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Acquia Sku Process Blacklisted Products Subscriber.
 *
 * @package Drupal\acq_sku\EventSubscriber
 */
class AcqSkuProcessBlacklistedProductsSubscriber implements EventSubscriberInterface {

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
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   Logger factory.
   */
  public function __construct(Connection $connection,
                              LoggerChannelFactory $logger_factory) {
    $this->connection = $connection;
    $this->logger = $logger_factory->get('acq_sku');
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
    $query->condition('media__value', '%blacklist_expiry%', 'LIKE');
    $products = $query->execute()->fetchAll();

    // Get Media for each SKU.
    foreach ($products as $product) {
      $sku = SKU::loadFromSku($product->sku, '', FALSE, FALSE);
      $this->logger->notice('Drupal attempted to download blacklisted images for the sku:@sku', [
        '@sku' => $product->sku,
      ]);
      if ($sku instanceof SKUInterface) {
        $sku->getMedia();
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
